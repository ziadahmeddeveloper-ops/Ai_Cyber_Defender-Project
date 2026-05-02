<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    $where = $userId ? "WHERE user_id = :user_id" : "";
    
    $query = "SELECT * FROM ai_requests $where ORDER BY created_at DESC LIMIT 50";
    $stmt = $pdo->prepare($query);
    if ($userId) $stmt->bindParam(":user_id", $userId);
    $stmt->execute();
    
    $results = $stmt->fetchAll();
    
    foreach($results as &$row) {
        if(!empty($row['ai_response'])) {
            $row['ai_response'] = json_decode($row['ai_response'], true);
        }
    }
    
    http_response_code(200);
    echo json_encode(["data" => $results]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>
