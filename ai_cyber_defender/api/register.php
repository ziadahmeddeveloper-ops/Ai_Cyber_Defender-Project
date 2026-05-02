<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

$data = json_decode(file_get_contents("php://input"));

try {
    if(!empty($data->email) && !empty($data->password) && !empty($data->username)) {
        // Check if email exists
        $checkQuery = "SELECT id FROM users WHERE email = :email";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->bindParam(":email", $data->email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            http_response_code(409); // Conflict
            echo json_encode(["message" => "This email is already registered. Please try logging in or use a different email."]);
            exit;
        }

        $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $pdo->prepare($query);
        
        // Hash password
        $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(":username", $data->username);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":password", $password_hash);
        
        if($stmt->execute()) {
            http_response_code(200);
            echo json_encode([
                "message" => "User registered successfully.", 
                "user" => [
                    "id" => $pdo->lastInsertId(),
                    "username" => $data->username,
                    "email" => $data->email
                ]
            ]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to register user."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete data. Please provide username, email, and password."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>
