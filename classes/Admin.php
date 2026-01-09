<?php
/**
 * Admin class for admin-specific database operations
 */
class Admin
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ========== DASHBOARD STATS ==========

    public function getDashboardStats()
    {
        $stats = [];

        // Quote counts by status
        $stmt = $this->conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $stats['quotes_by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Total quotes
        $stmt = $this->conn->query("SELECT COUNT(*) FROM orders");
        $stats['total_quotes'] = $stmt->fetchColumn();

        // New quotes needing price (quote_started)
        $stats['needs_quote'] = $stats['quotes_by_status']['quote_started'] ?? 0;

        // Awaiting deposit (price_sent)
        $stats['awaiting_deposit'] = $stats['quotes_by_status']['price_sent'] ?? 0;

        // In production (deposit_paid)
        $stats['in_production'] = $stats['quotes_by_status']['deposit_paid'] ?? 0;

        // Awaiting final payment (invoice_sent)
        $stats['awaiting_final'] = $stats['quotes_by_status']['invoice_sent'] ?? 0;

        // Completed orders (paid_in_full)
        $stats['completed_orders'] = $stats['quotes_by_status']['paid_in_full'] ?? 0;

        // Unread messages
        $stmt = $this->conn->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
        $stats['unread_messages'] = $stmt->fetchColumn();

        // Total messages
        $stmt = $this->conn->query("SELECT COUNT(*) FROM contact_messages");
        $stats['total_messages'] = $stmt->fetchColumn();

        // Total users
        $stmt = $this->conn->query("SELECT COUNT(*) FROM users");
        $stats['total_users'] = $stmt->fetchColumn();

        return $stats;
    }

    /**
     * Get orders that need action
     */
    public function getOrdersNeedingAction($status, $limit = 5)
    {
        $query = "SELECT o.*, u.first_name, u.last_name, u.email
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  WHERE o.status = :status
                  ORDER BY o.created_at ASC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $orders = $stmt->fetchAll();
        foreach ($orders as &$order) {
            $order['design_data'] = json_decode($order['design_data'], true);
        }

        return $orders;
    }

    // ========== ORDERS/QUOTES ==========

    public function getAllOrders($filters = [], $limit = 20, $offset = 0)
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "o.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(o.order_number LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  $whereClause
                  ORDER BY o.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $orders = $stmt->fetchAll();
        foreach ($orders as &$order) {
            $order['design_data'] = json_decode($order['design_data'], true);
        }

        return $orders;
    }

    public function countOrders($filters = [])
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "o.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(o.order_number LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id $whereClause";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    public function getOrderById($id)
    {
        $query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  WHERE o.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $order = $stmt->fetch();
        if ($order) {
            $order['design_data'] = json_decode($order['design_data'], true);
        }

        return $order;
    }

    public function updateOrder($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];

        $allowedFields = ['status', 'final_price', 'admin_notes'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field] === '' ? null : $data[$field];
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'error' => 'No fields to update'];
        }

        $query = "UPDATE orders SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute($params)) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Update failed'];
    }

    public function getRecentOrders($limit = 5)
    {
        $query = "SELECT o.*, u.first_name, u.last_name, u.email
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  ORDER BY o.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $orders = $stmt->fetchAll();
        foreach ($orders as &$order) {
            $order['design_data'] = json_decode($order['design_data'], true);
        }

        return $orders;
    }

    // ========== MESSAGES ==========

    public function getAllMessages($filters = [], $limit = 20, $offset = 0)
    {
        $where = [];
        $params = [];

        if (isset($filters['is_read']) && $filters['is_read'] !== '') {
            $where[] = "is_read = :is_read";
            $params[':is_read'] = $filters['is_read'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(name LIKE :search OR email LIKE :search OR subject LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT * FROM contact_messages
                  $whereClause
                  ORDER BY created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countMessages($filters = [])
    {
        $where = [];
        $params = [];

        if (isset($filters['is_read']) && $filters['is_read'] !== '') {
            $where[] = "is_read = :is_read";
            $params[':is_read'] = $filters['is_read'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(name LIKE :search OR email LIKE :search OR subject LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT COUNT(*) FROM contact_messages $whereClause";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    public function getMessageById($id)
    {
        $query = "SELECT * FROM contact_messages WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function markMessageRead($id, $isRead = 1)
    {
        $query = "UPDATE contact_messages SET is_read = :is_read WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':is_read', $isRead, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function deleteMessage($id)
    {
        $query = "DELETE FROM contact_messages WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getRecentMessages($limit = 5)
    {
        $query = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // ========== USERS ==========

    public function getAllUsers($filters = [], $limit = 20, $offset = 0)
    {
        $where = [];
        $params = [];

        if (isset($filters['is_admin']) && $filters['is_admin'] !== '') {
            $where[] = "is_admin = :is_admin";
            $params[':is_admin'] = $filters['is_admin'];
        }

        if (isset($filters['email_verified']) && $filters['email_verified'] !== '') {
            $where[] = "email_verified = :email_verified";
            $params[':email_verified'] = $filters['email_verified'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT u.*,
                         (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
                         (SELECT COUNT(*) FROM table_designs WHERE user_id = u.id) as design_count
                  FROM users u
                  $whereClause
                  ORDER BY u.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countUsers($filters = [])
    {
        $where = [];
        $params = [];

        if (isset($filters['is_admin']) && $filters['is_admin'] !== '') {
            $where[] = "is_admin = :is_admin";
            $params[':is_admin'] = $filters['is_admin'];
        }

        if (isset($filters['email_verified']) && $filters['email_verified'] !== '') {
            $where[] = "email_verified = :email_verified";
            $params[':email_verified'] = $filters['email_verified'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "SELECT COUNT(*) FROM users $whereClause";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    public function getUserById($id)
    {
        $query = "SELECT u.*,
                         (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
                         (SELECT COUNT(*) FROM table_designs WHERE user_id = u.id) as design_count
                  FROM users u
                  WHERE u.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getUserOrders($userId)
    {
        $query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $orders = $stmt->fetchAll();
        foreach ($orders as &$order) {
            $order['design_data'] = json_decode($order['design_data'], true);
        }

        return $orders;
    }

    public function getUserDesigns($userId)
    {
        $query = "SELECT * FROM table_designs WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $designs = $stmt->fetchAll();
        foreach ($designs as &$design) {
            $design['design_data'] = json_decode($design['design_data'], true);
        }

        return $designs;
    }

    public function toggleAdminStatus($userId)
    {
        // Don't allow self-demotion
        if ($userId == $_SESSION['user_id']) {
            return ['success' => false, 'error' => 'You cannot change your own admin status'];
        }

        // Get current status
        $stmt = $this->conn->prepare("SELECT is_admin FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        $newStatus = $user['is_admin'] ? 0 : 1;

        $stmt = $this->conn->prepare("UPDATE users SET is_admin = :is_admin WHERE id = :id");
        $stmt->bindParam(':is_admin', $newStatus, PDO::PARAM_INT);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return ['success' => true, 'is_admin' => $newStatus];
        }

        return ['success' => false, 'error' => 'Update failed'];
    }

    public function deleteUser($userId)
    {
        // Don't allow self-deletion
        if ($userId == $_SESSION['user_id']) {
            return ['success' => false, 'error' => 'You cannot delete your own account'];
        }

        // Check if user exists and is not an admin
        $stmt = $this->conn->prepare("SELECT is_admin, first_name, last_name FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if ($user['is_admin']) {
            return ['success' => false, 'error' => 'Cannot delete admin users. Remove admin status first.'];
        }

        try {
            $this->conn->beginTransaction();

            // Delete user's designs
            $stmt = $this->conn->prepare("DELETE FROM table_designs WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            // Delete user's orders
            $stmt = $this->conn->prepare("DELETE FROM orders WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            // Delete the user
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();

            return ['success' => true, 'message' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' deleted'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => 'Delete failed: ' . $e->getMessage()];
        }
    }
}
