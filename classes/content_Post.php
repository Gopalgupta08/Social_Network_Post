<?php
//require_once './database.php';
require_once __DIR__ . '/../config/database.php';


/**
 * Post class for handling all post-related operations
 * Manages posts, likes, and dislikes
 */
class Post {
    private $conn;
    private $table_name = "content_posts";
    
    // Post properties
    public $id;
    public $user_id;
    public $content;
    public $image;
    public $likes;
    public $dislikes;

    /**
     * Constructor - initialize database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create new post
     * @return bool Success status
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET user_id=:user_id, content=:content, image=:image";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->content = htmlspecialchars(strip_tags($this->content));
        
        // Bind parameters
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':image', $this->image);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Get all posts by user ID
     * @param int $user_id User ID
     * @return array Posts data
     */
    public function getPostsByUserId($user_id) {
        $query = "SELECT p.*, u.full_name, u.profile_picture 
                 FROM " . $this->table_name . " p
                 LEFT JOIN users u ON p.user_id = u.id
                 WHERE p.user_id = :user_id 
                 ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete post by ID
     * @param int $post_id Post ID
     * @param int $user_id User ID (for authorization)
     * @return bool Success status
     */
    public function delete($post_id, $user_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                 WHERE id = :post_id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }

    /**
     * Update like/dislike for a post
     * @param int $post_id Post ID
     * @param int $user_id User ID
     * @param string $type 'like' or 'dislike'
     * @return bool Success status
     */
    public function updateLikeDislike($post_id, $user_id, $type) {
        try {
            // Start transaction
            $this->conn->beginTransaction();
            
            // Check if user already liked/disliked this post
            $check_query = "SELECT like_type FROM user_likes 
                           WHERE user_id = :user_id AND post_id = :post_id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':user_id', $user_id);
            $check_stmt->bindParam(':post_id', $post_id);
            $check_stmt->execute();
            
            $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if ($existing['like_type'] === $type) {
                    // Remove like/dislike if clicking same button
                    $delete_query = "DELETE FROM user_likes 
                                   WHERE user_id = :user_id AND post_id = :post_id";
                    $delete_stmt = $this->conn->prepare($delete_query);
                    $delete_stmt->bindParam(':user_id', $user_id);
                    $delete_stmt->bindParam(':post_id', $post_id);
                    $delete_stmt->execute();
                    
                    // Update post count
                    if ($type === 'like') {
                        $update_query = "UPDATE posts SET likes = likes - 1 WHERE id = :post_id";
                    } else {
                        $update_query = "UPDATE posts SET dislikes = dislikes - 1 WHERE id = :post_id";
                    }
                } else {
                    // Update existing like/dislike
                    $update_like_query = "UPDATE user_likes 
                                        SET like_type = :type 
                                        WHERE user_id = :user_id AND post_id = :post_id";
                    $update_stmt = $this->conn->prepare($update_like_query);
                    $update_stmt->bindParam(':type', $type);
                    $update_stmt->bindParam(':user_id', $user_id);
                    $update_stmt->bindParam(':post_id', $post_id);
                    $update_stmt->execute();
                    
                    // Update post counts
                    if ($type === 'like') {
                        $update_query = "UPDATE posts 
                                       SET likes = likes + 1, dislikes = dislikes - 1 
                                       WHERE id = :post_id";
                    } else {
                        $update_query = "UPDATE posts 
                                       SET dislikes = dislikes + 1, likes = likes - 1 
                                       WHERE id = :post_id";
                    }
                }
            } else {
                // Insert new like/dislike
                $insert_query = "INSERT INTO user_likes (user_id, post_id, like_type) 
                               VALUES (:user_id, :post_id, :type)";
                $insert_stmt = $this->conn->prepare($insert_query);
                $insert_stmt->bindParam(':user_id', $user_id);
                $insert_stmt->bindParam(':post_id', $post_id);
                $insert_stmt->bindParam(':type', $type);
                $insert_stmt->execute();
                
                // Update post count
                if ($type === 'like') {
                    $update_query = "UPDATE posts SET likes = likes + 1 WHERE id = :post_id";
                } else {
                    $update_query = "UPDATE posts SET dislikes = dislikes + 1 WHERE id = :post_id";
                }
            }
            
            // Execute the update query
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':post_id', $post_id);
            $update_stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollback();
            return false;
        }
    }

    /**
     * Get current like counts for a post
     * @param int $post_id Post ID
     * @return array Like and dislike counts
     */
    public function getLikeCounts($post_id) {
        $query = "SELECT likes, dislikes FROM posts WHERE id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
