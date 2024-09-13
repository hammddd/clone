<?php
// models/TagModel.php

require_once __DIR__ . '/../db/db.php';

class TagModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllTags() {
        $query = "SELECT * FROM tags";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $tags;
    }
}
