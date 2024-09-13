<?php
// models/BlogPost.php

class BlogPost {
    private $conn;
    private $table_name = "blog_posts";

    public $id;
    public $title;
    public $post_date;
    public $content;
    public $image_url;
    public $caption;

    public function __construct($db) {
        $this->conn = $db;
    }
   

    // Get a blog post by its ID
    public function getById($id) {
        $query = "SELECT * FROM blog_posts WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    

    
    // Create a new blog post
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET  title=:title, post_date=:post_date, content=:content, image_url=:image_url, caption=:caption";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":post_date", $this->post_date);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":caption", $this->caption);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read a single blog post by ID
    public function read_single() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
           
            $this->title = $row['title'];
           
            $this->post_date = $row['post_date'];
            $this->content = $row['content'];
            $this->image_url = $row['image_url'];
            $this->caption = $row['caption'];
        }
    }

    // Update an existing blog post
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET title = :title, url = :url, post_date = :post_date, content = :content, image_url = :image_url, caption = :caption WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":post_date", $this->post_date);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":caption", $this->caption);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete a blog post
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // List all blog posts
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY post_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Read blog posts by page number with pagination support
    public function read_by_page($page_number) {
        $limit = 5; // Number of posts per page
        $offset = ($page_number - 1) * $limit;

        $query = "SELECT * FROM " . $this->table_name . " ORDER BY post_date DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }
}
?>
