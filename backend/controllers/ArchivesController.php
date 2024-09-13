<?php
require_once __DIR__ . '/../db/db.php';

class ArchivesController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getArchives() {
        $query = "
            SELECT 
                DATE_FORMAT(post_date, '%Y-%m') AS month,
                COUNT(*) AS post_count,
                GROUP_CONCAT(id ORDER BY post_date DESC) AS blog_posts
            FROM 
                blog_posts
            GROUP BY 
                month
            ORDER BY 
                month DESC;
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $archives = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($archives);
    }
}
?>
