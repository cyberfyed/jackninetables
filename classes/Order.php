<?php
class Order {
    private $conn;
    private $table = 'orders';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($userId, $designData, $designId = null, $notes = null) {
        $orderNumber = $this->generateOrderNumber();

        $query = "INSERT INTO {$this->table} (user_id, design_id, order_number, design_data, notes)
                  VALUES (:user_id, :design_id, :order_number, :design_data, :notes)";

        $stmt = $this->conn->prepare($query);
        $designDataJson = json_encode($designData);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':design_id', $designId);
        $stmt->bindParam(':order_number', $orderNumber);
        $stmt->bindParam(':design_data', $designDataJson);
        $stmt->bindParam(':notes', $notes);

        if ($stmt->execute()) {
            return ['success' => true, 'order_id' => $this->conn->lastInsertId(), 'order_number' => $orderNumber];
        }

        return ['success' => false, 'error' => 'Failed to create order.'];
    }

    public function getById($id, $userId = null) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $params = [':id' => $id];

        if ($userId) {
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $order = $stmt->fetch();

        if ($order) {
            $order['design_data'] = json_decode($order['design_data'], true);
        }

        return $order;
    }

    public function getByOrderNumber($orderNumber, $userId = null) {
        $query = "SELECT * FROM {$this->table} WHERE order_number = :order_number";
        $params = [':order_number' => $orderNumber];

        if ($userId) {
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $order = $stmt->fetch();

        if ($order) {
            $order['design_data'] = json_decode($order['design_data'], true);
        }

        return $order;
    }

    public function getByUser($userId, $limit = null, $offset = 0) {
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC";

        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);

        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        $orders = $stmt->fetchAll();

        foreach ($orders as &$order) {
            $order['design_data'] = json_decode($order['design_data'], true);
        }

        return $orders;
    }

    public function updateStatus($id, $status, $userId = null) {
        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $params = [':id' => $id, ':status' => $status];

        if ($userId) {
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $stmt = $this->conn->prepare($query);

        if ($stmt->execute($params)) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Status update failed.'];
    }

    public function countByUser($userId) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch()['count'];
    }

    public function hasPendingQuote($userId) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND status = 'quote_started'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch()['count'] > 0;
    }

    public function getStatusCounts($userId) {
        $query = "SELECT status, COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function generateOrderNumber() {
        return 'JNT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    /**
     * Record deposit payment
     */
    public function recordDeposit($orderId, $amount, $paypalOrderId, $transactionId) {
        $query = "UPDATE {$this->table}
                  SET deposit_amount = :amount,
                      deposit_paid_at = NOW(),
                      paypal_order_id = :paypal_order_id,
                      paypal_transaction_id = :transaction_id,
                      status = 'deposit_paid'
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':paypal_order_id', $paypalOrderId);
        $stmt->bindParam(':transaction_id', $transactionId);
        $stmt->bindParam(':id', $orderId);

        return $stmt->execute();
    }

    /**
     * Check if order needs deposit payment
     */
    public function needsDeposit($order) {
        // Must have a price set
        if (empty($order['final_price'])) {
            return false;
        }

        // Must not have paid deposit already
        if (!empty($order['deposit_paid_at'])) {
            return false;
        }

        // Must be in price_sent status (admin has sent quote, awaiting customer deposit)
        if ($order['status'] !== 'price_sent') {
            return false;
        }

        return true;
    }

    /**
     * Get the price to use for deposit calculation
     */
    public function getOrderPrice($order) {
        return $order['final_price'] ?? 0;
    }

    /**
     * Record final payment
     */
    public function recordFinalPayment($orderId, $transactionId) {
        $query = "UPDATE {$this->table}
                  SET status = 'paid_in_full',
                      final_payment_transaction_id = :transaction_id,
                      final_paid_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':transaction_id', $transactionId);
        $stmt->bindParam(':id', $orderId);

        return $stmt->execute();
    }
}
