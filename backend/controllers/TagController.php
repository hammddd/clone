<?php
// controllers/TagController.php

require_once __DIR__ . '/../db/TagModel.php';

class TagController {
    private $db;

    public function __construct() {
        // Initialize the database connection
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllTags() {
        $query = "
            SELECT 
                t.id AS tag_id,
                t.name AS tag_name,
                COUNT(bt.blog_post_id) AS count,
                GROUP_CONCAT(bt.blog_post_id ORDER BY bt.blog_post_id SEPARATOR ', ') AS blog_posts
            FROM 
                tags t
            LEFT JOIN 
                blog_post_tags bt ON t.id = bt.tag_id
            GROUP BY 
                t.id, t.name
            ORDER BY 
                t.name ASC;
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($tags);
    }
}
