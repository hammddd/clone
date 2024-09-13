<?php
require_once __DIR__ . '/../db/db.php';

class AdminController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function loginAdmin() {
        // Retrieve the JSON data sent via POST
        $data = json_decode(file_get_contents("php://input"), true);

        // Extract the username and password from the request
        $username = isset($data['username']) ? $data['username'] : null;
        $password = isset($data['password']) ? $data['password'] : null;

        // Check if both username and password are provided
        if ($username && $password) {
            // Prepare and execute the SQL query to fetch the admin details
            $query = "SELECT * FROM admin WHERE username = :username LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // If an admin with the provided username exists and the password matches
            if ($admin && $password === $admin['password']) {
                echo json_encode([
                    "success" => true,
                    "message" => "Login successful"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Invalid username or password"
                ]);
            }
        } else {
            // If the username or password is missing in the request
            echo json_encode([
                "success" => false,
                "message" => "Please provide both username and password"
            ]);
        }
    }
}
