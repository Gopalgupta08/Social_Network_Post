<?php
/**
 * Database configuration class
 * Handles database connection using PDO
 */
class Database {
    private $host = 'localhost';
    private $db_name = 'social_network';
    private $username = 'root';  // Change as needed
    private $password = "Arvind@1";      // Change as needed
    private $conn;

    /**
     * Get database connection
     * @return PDO(php data object) Database connection object
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            // Create PDO connection with error handling
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            // Set PDO attributes for error handling
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

?>
