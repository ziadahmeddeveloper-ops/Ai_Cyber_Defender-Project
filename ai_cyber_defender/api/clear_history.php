<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $userId = isset($data->user_id) ? $data->user_id : null;

    if ($userId) {
        $stmt = $pdo->prepare("DELETE FROM ai_requests WHERE user_id = :user_id");
        $stmt->bindParam(":user_id", $userId);
        
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "History cleared successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to clear history."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "User ID required."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}
?>
