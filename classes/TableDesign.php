<?php
class TableDesign {
    private $conn;
    private $table = 'table_designs';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($userId, $name, $designData, $previewImage = null) {
        $query = "INSERT INTO {$this->table} (user_id, name, design_data, preview_image)
                  VALUES (:user_id, :name, :design_data, :preview_image)";

        $designDataJson = json_encode($designData);

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':design_data', $designDataJson);
        $stmt->bindParam(':preview_image', $previewImage);

        if ($stmt->execute()) {
            return ['success' => true, 'design_id' => $this->conn->lastInsertId()];
        }

        return ['success' => false, 'error' => 'Failed to save design.'];
    }

    public function update($id, $userId, $data) {
        $fields = [];
        $params = [':id' => $id, ':user_id' => $userId];

        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }

        if (isset($data['design_data'])) {
            $fields[] = "design_data = :design_data";
            $params[':design_data'] = json_encode($data['design_data']);
        }

        if (isset($data['preview_image'])) {
            $fields[] = "preview_image = :preview_image";
            $params[':preview_image'] = $data['preview_image'];
        }

        if (isset($data['is_favorite'])) {
            $fields[] = "is_favorite = :is_favorite";
            $params[':is_favorite'] = $data['is_favorite'];
        }

        if (empty($fields)) {
            return ['success' => false, 'error' => 'No fields to update.'];
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute($params)) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Update failed.'];
    }

    public function delete($id, $userId) {
        $query = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);

        if ($stmt->execute()) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Delete failed.'];
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
        $design = $stmt->fetch();

        if ($design) {
            $design['design_data'] = json_decode($design['design_data'], true);
        }

        return $design;
    }

    public function getByUser($userId, $limit = null, $offset = 0) {
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY updated_at DESC";

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
        $designs = $stmt->fetchAll();

        foreach ($designs as &$design) {
            $design['design_data'] = json_decode($design['design_data'], true);
        }

        return $designs;
    }

    public function countByUser($userId) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch()['count'];
    }

    public function toggleFavorite($id, $userId) {
        $query = "UPDATE {$this->table} SET is_favorite = NOT is_favorite WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);

        if ($stmt->execute()) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Toggle failed.'];
    }

    public function getFavorites($userId) {
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND is_favorite = 1 ORDER BY updated_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $designs = $stmt->fetchAll();

        foreach ($designs as &$design) {
            $design['design_data'] = json_decode($design['design_data'], true);
        }

        return $designs;
    }
}
