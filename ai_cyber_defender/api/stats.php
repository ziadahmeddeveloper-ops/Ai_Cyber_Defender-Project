<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = isset($_GET['user_id']) && $_GET['user_id'] !== 'null' && $_GET['user_id'] !== '' ? $_GET['user_id'] : null;
    $where = $userId ? "WHERE user_id = :user_id" : "";

    // Total Users count
    $usersQuery = $pdo->query("SELECT COUNT(*) as count FROM users");
    $activeUsers = $usersQuery->fetch()['count'];

    // Total Scans
    $totalScansQuery = $pdo->prepare("SELECT COUNT(*) as count FROM ai_requests $where");
    if ($userId) $totalScansQuery->bindParam(":user_id", $userId);
    $totalScansQuery->execute();
    $totalScans = $totalScansQuery->fetch()['count'];

    // Malicious
    $maliciousWhere = $userId ? "WHERE status = 'Malicious' AND user_id = :user_id" : "WHERE status = 'Malicious'";
    $maliciousQuery = $pdo->prepare("SELECT COUNT(*) as count FROM ai_requests $maliciousWhere");
    if ($userId) $maliciousQuery->bindParam(":user_id", $userId);
    $maliciousQuery->execute();
    $maliciousCount = $maliciousQuery->fetch()['count'];

    // Safe
    $safeWhere = $userId ? "WHERE status = 'Secure' AND user_id = :user_id" : "WHERE status = 'Secure'";
    $safeQuery = $pdo->prepare("SELECT COUNT(*) as count FROM ai_requests $safeWhere");
    if ($userId) $safeQuery->bindParam(":user_id", $userId);
    $safeQuery->execute();
    $safeCount = $safeQuery->fetch()['count'];

    // Get latest threats (recent Malicious)
    $latestThreatsWhere = $userId ? "WHERE status = 'Malicious' AND user_id = :user_id" : "WHERE status = 'Malicious'";
    $latestThreatsQuery = $pdo->prepare("SELECT scan_target, created_at FROM ai_requests $latestThreatsWhere ORDER BY created_at DESC LIMIT 20");
    if ($userId) $latestThreatsQuery->bindParam(":user_id", $userId);
    $latestThreatsQuery->execute();
    $latestThreats = $latestThreatsQuery->fetchAll();

    http_response_code(200);
    echo json_encode([
        "total_scans" => $totalScans,
        "malicious_count" => $maliciousCount,
        "safe_count" => $safeCount,
        "active_users" => $activeUsers,
        "latest_threats" => $latestThreats
    ]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>
