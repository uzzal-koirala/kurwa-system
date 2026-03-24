<?php
/**
 * Kurwa System - General Helper Functions
 */

if (!function_exists('get_setting')) {
    /**
     * Fetch a setting value from the system_settings table
     * 
     * @param string $key The setting key
     * @param string $default Default value if key is not found
     * @return string The setting value
     */
    function get_setting($key, $default = '') {
        global $conn;
        
        if (!isset($conn)) {
            return $default;
        }

        $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        if (!$stmt) return $default;

        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['setting_value'];
        }

        $stmt->close();
        return $default;
    }
}
?>
