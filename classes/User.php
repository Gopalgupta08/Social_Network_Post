<?php
//require_once './database.php';
require_once '../config/database.php';


// <?php
// require_once '../config/database.php';

/**
 * User class for handling all user-related operations
 * Implements OOP principles for user management
 */
class User {
    private $conn;
    private $table_name = "users";
    
    // User properties
    public $id;
    public $full_name;
    public $email;
    public $password_hash;
    public $age;
    public $profile_picture;

    /**
     * Constructor - initialize database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create new user account
     * @return bool Success status
     */
    public function create() {
        // SQL query to insert new user
        $query = "INSERT INTO " . $this->table_name . " 
                 SET full_name=:full_name, email=:email, 
                     password_hash=:password_hash, age=:age, 
                     profile_picture=:profile_picture";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input data
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->age = htmlspecialchars(strip_tags($this->age));
        
        // Bind parameters
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":age", $this->age);
        $stmt->bindParam(":profile_picture", $this->profile_picture);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Login user with email and password
     * @param string $email User email
     * @param string $password User password
     * @return array|false User data or false
     */
    public function login($email, $password) {
        // Query to get user by email
        $query = "SELECT id, full_name, email, password_hash, age, profile_picture 
                 FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password if user exists
        if($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    /**
     * Get user by ID
     * @param int $user_id User ID
     * @return array|false User data or false
     */
    public function getUserById($user_id) {
        $query = "SELECT id, full_name, email, age, profile_picture 
                 FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update user profile
     * @return bool Success status
     */
    public function updateProfile() {
        $query = "UPDATE " . $this->table_name . " 
                 SET full_name=:full_name, age=:age, profile_picture=:profile_picture 
                 WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->age = htmlspecialchars(strip_tags($this->age));
        
        // Bind parameters
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':age', $this->age);
        $stmt->bindParam(':profile_picture', $this->profile_picture);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    /**
     * Check if email already exists
     * @param string $email Email to check
     * @return bool True if exists, false otherwise
     */
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?>
