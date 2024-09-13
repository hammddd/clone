<?php
require_once __DIR__ . '/../controllers/BlogController.php';
require_once __DIR__ . '/../controllers/TagController.php';
require_once __DIR__ . '/../controllers/ArchivesController.php';  
require_once __DIR__ . '/../controllers/AdminController.php'; // Include AdminController

$blogController = new BlogController();
$tagController = new TagController();
$archivesController = new ArchivesController();
$adminController = new AdminController(); // Initialize AdminController

// Routing logic
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove '/clone/backend/index.php' from the URI to get only the relevant part
$basePath = '/clone/backend/index.php';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Ensure URI does not have a trailing slash
$uri = rtrim($uri, '/');

// Route handling
if ($uri == '/blog') {
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if (isset($_GET['id'])) {
            $blogController->read_single($_GET['id']);
        } elseif (isset($_GET['page'])) {
            $blogController->read_by_page($_GET['page']);
        } else {
            $blogController->read_all();
        }
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_GET['id'])) {
            $blogController->update($_GET['id']);
        } else {
            $blogController->create();
        }
    } elseif ($_SERVER["REQUEST_METHOD"] == "DELETE" && isset($_GET['id'])) {
        $blogController->delete($_GET['id']);
    } else {
        echo json_encode(array("message" => "Invalid request"));
    }
} elseif ($uri == '/tags' && $_SERVER["REQUEST_METHOD"] == "GET") {
    $tagController->getAllTags();
} elseif ($uri == '/archives' && $_SERVER["REQUEST_METHOD"] == "GET") {
    $archivesController->getArchives();
} elseif ($uri == '/admin/login' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $adminController->loginAdmin(); // Call the loginAdmin function
} else {
    echo json_encode(array("message" => "Invalid request"));
}
