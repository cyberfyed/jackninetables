<?php
/**
 * Settings class for site configuration management
 */
class Settings
{
    private $conn;
    private $table = 'site_settings';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Get all settings
     */
    public function getAll()
    {
        $query = "SELECT * FROM {$this->table} ORDER BY setting_group, setting_key";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll();
    }

    /**
     * Get settings grouped by setting_group
     */
    public function getAllGrouped()
    {
        $settings = $this->getAll();
        $grouped = [];

        foreach ($settings as $setting) {
            $group = $setting['setting_group'];
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $setting;
        }

        return $grouped;
    }

    /**
     * Get a single setting value
     */
    public function get($key, $default = null)
    {
        $query = "SELECT setting_value FROM {$this->table} WHERE setting_key = :key LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    }

    /**
     * Set a single setting value
     */
    public function set($key, $value)
    {
        // Check if setting exists
        $query = "SELECT id FROM {$this->table} WHERE setting_key = :key LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->execute();

        if ($stmt->fetch()) {
            // Update existing
            $query = "UPDATE {$this->table} SET setting_value = :value WHERE setting_key = :key";
        } else {
            // Insert new
            $query = "INSERT INTO {$this->table} (setting_key, setting_value) VALUES (:key, :value)";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':value', $value);

        return $stmt->execute();
    }

    /**
     * Update multiple settings at once
     */
    public function updateBulk($settings)
    {
        $success = true;

        foreach ($settings as $key => $value) {
            if (!$this->set($key, $value)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get settings by group
     */
    public function getByGroup($group)
    {
        $query = "SELECT * FROM {$this->table} WHERE setting_group = :group ORDER BY setting_key";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':group', $group);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Delete a setting
     */
    public function delete($key)
    {
        $query = "DELETE FROM {$this->table} WHERE setting_key = :key";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':key', $key);

        return $stmt->execute();
    }

    /**
     * Add a new setting
     */
    public function add($key, $value, $type = 'text', $group = 'general')
    {
        $query = "INSERT INTO {$this->table} (setting_key, setting_value, setting_type, setting_group)
                  VALUES (:key, :value, :type, :group)
                  ON DUPLICATE KEY UPDATE setting_value = :value2";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':group', $group);
        $stmt->bindParam(':value2', $value);

        return $stmt->execute();
    }
}
