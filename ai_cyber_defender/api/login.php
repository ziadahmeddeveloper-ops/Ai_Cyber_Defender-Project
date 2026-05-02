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
    if(!empty($data->email) && !empty($data->password)) {
        $query = "SELECT id, username, email, password FROM users WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":email", $data->email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($data->password, $row['password'])) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Login successful.",
                    "user" => [
                        "id" => $row['id'],
                        "username" => $row['username'],
                        "email" => $row['email']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Incorrect password."]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete data. Please provide email and password."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>
