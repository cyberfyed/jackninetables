<?php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($data) {
        // Check if email exists
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        $query = "INSERT INTO {$this->table}
                  (first_name, last_name, email, password, phone, verification_token)
                  VALUES (:first_name, :last_name, :email, :password, :phone, :token)";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':token', $token);

        if ($stmt->execute()) {
            return ['success' => true, 'user_id' => $this->conn->lastInsertId(), 'token' => $token];
        }

        return ['success' => false, 'error' => 'Registration failed. Please try again.'];
    }

    public function login($email, $password) {
        $query = "SELECT id, first_name, last_name, email, password, email_verified FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        $user = $stmt->fetch();

        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['email_verified'] = (bool)$user['email_verified'];

            return ['success' => true, 'user' => $user];
        }

        return ['success' => false, 'error' => 'Invalid email or password.'];
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function getById($id) {
        $query = "SELECT id, first_name, last_name, email, phone, address, city, state, zip, created_at
                  FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        foreach (['first_name', 'last_name', 'phone', 'address', 'city', 'state', 'zip'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'error' => 'No fields to update.'];
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute($params)) {
            // Update session name if changed
            if (isset($data['first_name']) || isset($data['last_name'])) {
                $user = $this->getById($id);
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            }
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Update failed.'];
    }

    public function updatePassword($id, $currentPassword, $newPassword) {
        // Verify current password
        $query = "SELECT password FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'error' => 'Current password is incorrect.'];
        }

        // Update password
        $query = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Password update failed.'];
    }

    public function createResetToken($email) {
        // Check if email exists and get user name
        $query = "SELECT first_name FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) {
            // Don't reveal if email exists or not
            return ['success' => true, 'message' => 'If this email is registered, you will receive a reset link.'];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $query = "UPDATE {$this->table} SET reset_token = :token, reset_expires = :expires WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return ['success' => true, 'token' => $token, 'name' => $user['first_name'], 'message' => 'If this email is registered, you will receive a reset link.'];
    }

    public function resetPassword($token, $newPassword) {
        $query = "SELECT id FROM {$this->table} WHERE reset_token = :token AND reset_expires > NOW() LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'error' => 'Invalid or expired reset token.'];
        }

        $user = $stmt->fetch();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $query = "UPDATE {$this->table} SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $user['id']);

        if ($stmt->execute()) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Password reset failed.'];
    }

    public function emailExists($email) {
        $query = "SELECT id FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function verifyEmail($token) {
        $query = "SELECT id, first_name, email FROM {$this->table} WHERE verification_token = :token AND email_verified = 0 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'error' => 'Invalid or already used verification link.'];
        }

        $user = $stmt->fetch();

        // Mark email as verified and clear token
        $query = "UPDATE {$this->table} SET email_verified = 1, verification_token = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user['id']);

        if ($stmt->execute()) {
            // Update session if user is logged in
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id']) {
                $_SESSION['email_verified'] = true;
            }
            return ['success' => true, 'user' => $user];
        }

        return ['success' => false, 'error' => 'Verification failed. Please try again.'];
    }

    public function resendVerificationEmail($userId) {
        // Generate new token
        $token = bin2hex(random_bytes(32));

        $query = "UPDATE {$this->table} SET verification_token = :token WHERE id = :id AND email_verified = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $userId);

        if ($stmt->execute() && $stmt->rowCount() > 0) {
            // Get user info for email
            $query = "SELECT first_name, email FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            $user = $stmt->fetch();

            return ['success' => true, 'token' => $token, 'email' => $user['email'], 'name' => $user['first_name']];
        }

        return ['success' => false, 'error' => 'Unable to resend verification email.'];
    }
}
