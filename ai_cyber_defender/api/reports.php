<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $period = isset($_GET['period']) ? $_GET['period'] : 'daily';
    $userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    
    $groupBy = "";
    $whereClause = "1=1";
    
    if ($period === 'daily') {
        $groupBy = "DATE(created_at)";
        $whereClause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } else if ($period === 'weekly') {
        $groupBy = "YEARWEEK(created_at, 1)";
        $whereClause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 2 MONTH)";
    } else if ($period === 'monthly') {
        $groupBy = "DATE_FORMAT(created_at, '%Y-%m')";
        $whereClause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    }

    if ($userId) {
        $whereClause .= " AND user_id = :user_id";
    }

    $query = $pdo->prepare("
        SELECT 
            $groupBy as period_label,
            COUNT(*) as total_scans,
            SUM(CASE WHEN status = 'Malicious' THEN 1 ELSE 0 END) as total_threats,
            SUM(CASE WHEN status = 'Secure' THEN 1 ELSE 0 END) as total_safe
        FROM ai_requests
        WHERE $whereClause
        GROUP BY period_label
        ORDER BY period_label DESC
    ");
    
    if ($userId) $query->bindParam(":user_id", $userId);
    $query->execute();
    $stats = $query->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "period" => $period,
        "stats" => $stats
    ]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>
