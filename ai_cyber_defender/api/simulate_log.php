<?php
require_once 'config.php';

// This script simulates an automatic log ingestion from an external source/agent
$attack_types = [
    "SQL Injection Attempt", 
    "Brute Force / Failed Logon", 
    "XSS Cross-Site Scripting", 
    "DDoS Volume Spike", 
    "Unauthorized API Access"
];

$targets = [
    "192.168.1." . rand(1, 254),
    "user_login_gateway",
    "db_server_primary",
    "webapp_production_v2",
    "api_endpoint_auth"
];

$type = $attack_types[array_rand($attack_types)];
$target = $targets[array_rand($targets)];
$is_malicious = (rand(1, 10) > 4); // 60% chance of threat in simulation

$status = $is_malicious ? 'Malicious' : 'Secure';
$ai_response = [
    "prediction" => $is_malicious ? "anomaly" : "secure",
    "attack_type" => $type,
    "threat_score" => $is_malicious ? rand(75, 99) : rand(2, 10),
    "attacker_ip" => "185." . rand(10, 255) . "." . rand(10, 255) . "." . rand(10, 255),
    "raw_context" => "Real-time Agent Detection: Automated security event captured from system journal."
];

$query = "INSERT INTO ai_requests (user_id, scan_target, scan_type, ai_response, status) VALUES (:user_id, :scan_target, 'log', :ai_response, :status)";
$stmt = $pdo->prepare($query);

// Use a default user or get from request
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 1;

$stmt->bindValue(":user_id", $user_id);
$stmt->bindValue(":scan_target", $target);
$stmt->bindValue(":ai_response", json_encode($ai_response));
$stmt->bindValue(":status", $status);

if($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Automated log captured", "data" => $ai_response]);
} else {
    echo json_encode(["status" => "error"]);
}
?>
