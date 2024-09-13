<?php
 
 error_reporting(E_ALL);
 ini_set('display_errors', 1);
 
// Set headers for JSON responses and allow all origins
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

 
require_once 'routes/routes.php';


 
?>
