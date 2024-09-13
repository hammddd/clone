<?php
// controllers/BlogController.php

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../db/BlogPost.php';

class BlogController {

    private $db;
    private $blogPost;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->blogPost = new BlogPost($this->db);
    }
    public function create() {
        // Enable error reporting for better debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_log', __DIR__ . '/../logs/php_error.log');
    
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Print out the received data for debugging
            error_log("Received POST data: " . print_r($_POST, true));
            error_log("Received FILE data: " . print_r($_FILES, true));
    
            // Handle file upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageTmpPath = $_FILES['image']['tmp_name'];
                $imageName = $_FILES['image']['name'];
                $uploadDir = 'C:/xampp-8-0/htdocs/clone/www.robinhobb.com/images/';
                $imagePath = $uploadDir . basename($imageName);
    
                if (move_uploaded_file($imageTmpPath, $imagePath)) {
                    $this->blogPost->image_url = $imagePath;
                } else {
                    echo json_encode(["success" => false, "message" => "Failed to upload image."]);
                    return;
                }
            } else {
                $this->blogPost->image_url = null;
            }
    
            // Validate required fields
            if (isset($_POST['title']) && isset($_POST['post_date']) && isset($_POST['content']) && isset($_POST['tags'])) {
                $this->blogPost->title = $_POST['title'];
                $this->blogPost->post_date = $_POST['post_date'];
                $this->blogPost->content = $_POST['content'];
                $this->blogPost->caption = isset($_POST['caption']) ? $_POST['caption'] : '';
    
                // Check if the tags field is empty
                if (empty(trim($_POST['tags']))) {
                    echo json_encode(["success" => false, "message" => "Tags field is empty."]);
                    return;
                }
    
                // Attempt to create the blog post
                if ($this->blogPost->create()) {
                    $blogPostId = $this->db->lastInsertId(); // Get the ID of the newly created blog post
                    if (!$blogPostId) {
                        echo json_encode(["success" => false, "message" => "Failed to retrieve the last inserted blog post ID."]);
                        return;
                    }
    
                    // Handle tags and insert into blog_post_tags table
                    $tags = explode(',', $_POST['tags']);
                    foreach ($tags as $tagName) {
                        $tagName = trim($tagName); // Trim any extra spaces
    
                        // Get the tag ID from the tags table
                        $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = :name");
                        $stmt->bindParam(':name', $tagName, PDO::PARAM_STR);
                        $stmt->execute();
                        $tag = $stmt->fetch(PDO::FETCH_ASSOC);
    
                        if ($tag) {
                            $tagId = $tag['id'];
    
                            // Insert the relationship into the blog_post_tags table
                            $stmt = $this->db->prepare("INSERT INTO blog_post_tags (blog_post_id, tag_id) VALUES (:blog_post_id, :tag_id)");
                            $stmt->bindParam(':blog_post_id', $blogPostId, PDO::PARAM_INT);
                            $stmt->bindParam(':tag_id', $tagId, PDO::PARAM_INT);
                            if (!$stmt->execute()) {
                                error_log("Failed to insert tag relationship: " . print_r($stmt->errorInfo(), true));
                                echo json_encode(["success" => false, "message" => "Failed to insert tag relationship for tag: $tagName"]);
                                return;
                            }
                        } else {
                            error_log("Tag not found in database: " . $tagName);
                            echo json_encode(["success" => false, "message" => "Tag not found: $tagName"]);
                            return;
                        }
                    }
    
                    echo json_encode(["success" => true, "message" => "Blog post created successfully!"]);
                } else {
                    error_log("Failed to create blog post: " . print_r($this->conn->errorInfo(), true));
                    echo json_encode(["success" => false, "message" => "Unable to create blog post."]);
                }
            } else {
                error_log("Invalid input data: " . print_r($_POST, true));
                echo json_encode(["success" => false, "message" => "Invalid input data."]);
            }
        }
    }
    
    
    public function read_single($id) {
        $this->blogPost->id = $id;
        $this->blogPost->read_single();

        if ($this->blogPost->title != null) {
            $blog_arr = array(
                "id" => $this->blogPost->id,
                "title" => $this->blogPost->title,
                "post_date" => $this->blogPost->post_date,
                "content" => $this->blogPost->content,
                "image_url" => $this->blogPost->image_url,
                "caption" => $this->blogPost->caption
            );
            echo json_encode($blog_arr);
        } else {
            echo json_encode(array("message" => "Blog post not found."));
        }
    }








    public function update($id) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Enable error reporting for better debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_log', __DIR__ . '/../logs/php_error.log');

        // Print out the received data for debugging
        error_log("Received POST data: " . print_r($_POST, true));
        error_log("Received FILE data: " . print_r($_FILES, true));

        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageTmpPath = $_FILES['image']['tmp_name'];
            $imageName = $_FILES['image']['name'];
            $uploadDir = 'C:/xampp-8-0/htdocs/clone/www.robinhobb.com/images/';
            $imagePath = $uploadDir . basename($imageName);

            if (move_uploaded_file($imageTmpPath, $imagePath)) {
                $this->blogPost->image_url = $imagePath;
            } else {
                echo json_encode(["success" => false, "message" => "Failed to upload image."]);
                return;
            }
        } else {
            $this->blogPost->image_url = $_POST['image_url']; // Keep the old image URL if no new image is uploaded
        }

        // Validate required fields
        if (isset($_POST['title']) && isset($_POST['post_date']) && isset($_POST['content'])) {
            $this->blogPost->id = $id;
            $this->blogPost->title = $_POST['title'];
            $this->blogPost->post_date = $_POST['post_date'];
            $this->blogPost->content = $_POST['content'];
            $this->blogPost->caption = isset($_POST['caption']) ? $_POST['caption'] : '';

            // Begin a transaction to ensure atomicity
            $this->db->beginTransaction();

            try {
                // Prepare the update SQL statement for the blog post
                $stmt = $this->db->prepare("UPDATE blog_posts SET title = :title, post_date = :post_date, content = :content, image_url = :image_url, caption = :caption WHERE id = :id");

                // Bind the parameters to the query
                $stmt->bindParam(':title', $this->blogPost->title);
                $stmt->bindParam(':post_date', $this->blogPost->post_date);
                $stmt->bindParam(':content', $this->blogPost->content);
                $stmt->bindParam(':image_url', $this->blogPost->image_url);
                $stmt->bindParam(':caption', $this->blogPost->caption);
                $stmt->bindParam(':id', $this->blogPost->id, PDO::PARAM_INT);

                // Execute the update query
                if ($stmt->execute()) {
                    // Delete existing tags for this blog post
                    $stmt = $this->db->prepare("DELETE FROM blog_post_tags WHERE blog_post_id = :blog_post_id");
                    $stmt->bindParam(':blog_post_id', $this->blogPost->id, PDO::PARAM_INT);
                    $stmt->execute();

                    // Handle new tags and insert them into blog_post_tags table
                    if (!empty($_POST['tags'])) {
                        $tags = explode(',', $_POST['tags']);
                        foreach ($tags as $tagName) {
                            $tagName = trim($tagName);

                            // Get the tag ID from the tags table
                            $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = :name");
                            $stmt->bindParam(':name', $tagName, PDO::PARAM_STR);
                            $stmt->execute();
                            $tag = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($tag) {
                                $tagId = $tag['id'];

                                // Insert the relationship into the blog_post_tags table
                                $stmt = $this->db->prepare("INSERT INTO blog_post_tags (blog_post_id, tag_id) VALUES (:blog_post_id, :tag_id)");
                                $stmt->bindParam(':blog_post_id', $this->blogPost->id, PDO::PARAM_INT);
                                $stmt->bindParam(':tag_id', $tagId, PDO::PARAM_INT);
                                if (!$stmt->execute()) {
                                    throw new Exception("Failed to insert tag relationship for tag: $tagName");
                                }
                            } else {
                                throw new Exception("Tag not found: $tagName");
                            }
                        }
                    }

                    // Commit the transaction
                    $this->db->commit();
                    echo json_encode(["message" => "Blog post updated successfully!"]);
                } else {
                    throw new Exception("Unable to update blog post.");
                }
            } catch (Exception $e) {
                // Rollback the transaction on failure
                $this->db->rollBack();
                echo json_encode(["message" => $e->getMessage()]);
            }
        } else {
            echo json_encode(["message" => "Invalid input data."]);
        }
    }
}

    

    public function read_all() {
        $stmt = $this->blogPost->read();
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($blogs);
    }

    public function read_by_page($page_number) {
        $postsPerPage = 3;  // Number of posts per page
        
        // Calculate the offset for the query
        $offset = ($page_number - 1) * $postsPerPage;
    
        // SQL query to retrieve posts sorted by post_date in DESC order, limited by pagination
        $query = "SELECT * FROM blog_posts ORDER BY post_date DESC LIMIT :limit OFFSET :offset";
        
        // Prepare and execute the query
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $postsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
        $stmt->execute();
    
        // Fetch the results and output them as JSON
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($blogs);
    }


    public function delete($id) {
        // Start a transaction
        $this->db->beginTransaction();

        try {
            // Delete from blog_post_tags first
            $query = "DELETE FROM blog_post_tags WHERE blog_post_id = :blog_post_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':blog_post_id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Delete from blog_posts
            $query = "DELETE FROM blog_posts WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Commit the transaction
            $this->db->commit();

            // Check if deletion was successful
            if ($stmt->rowCount() > 0) {
                echo json_encode(["success" => true, "message" => "Blog post and related tags deleted successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "No blog post found with the given ID."]);
            }
        } catch (Exception $e) {
            // Rollback the transaction if something failed
            $this->db->rollBack();
            echo json_encode(["success" => false, "message" => "Failed to delete blog post: " . $e->getMessage()]);
        }
    }


    





    
}
?>
