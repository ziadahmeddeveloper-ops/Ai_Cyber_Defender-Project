<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->scan_target) && !empty($data->scan_type)) {
    
    // Try to call Python AI model
    $python_api_url = "http://127.0.0.1:5000/api/predict";
    $ch = curl_init($python_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["input_text" => $data->scan_target]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($result && $http_code == 200) {
        $ai_response = json_decode($result, true);
        $status = (isset($ai_response['prediction']) && ($ai_response['prediction'] === 'anomaly' || $ai_response['prediction'] === 'malicious')) ? 'Malicious' : 'Secure';
    } else {
        // Fallback to Simulation for UI Testing (If server is down)
        $is_malicious = (strpos($data->scan_target, 'drop') !== false || strpos($data->scan_target, 'alert') !== false || strlen($data->scan_target) > 100);
        $ai_response = [
            "prediction" => $is_malicious ? "anomaly" : "secure",
            "attack_type" => $is_malicious ? "Suspicious Pattern" : "Normal Activity",
            "threat_score" => $is_malicious ? rand(70, 95) : rand(5, 15),
            "raw_context" => "Simulation Mode: AI Server is currently offline. Results are based on heuristic patterns."
        ];
        $status = $is_malicious ? 'Malicious' : 'Secure';
    }
    
    $query = "INSERT INTO ai_requests (user_id, scan_target, scan_type, ai_response, status) VALUES (:user_id, :scan_target, :scan_type, :ai_response, :status)";
    $stmt = $pdo->prepare($query);
    
    $ai_response_json = json_encode($ai_response);
    $user_id = isset($data->user_id) ? $data->user_id : null;
    
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":scan_target", $data->scan_target);
    $stmt->bindParam(":scan_type", $data->scan_type);
    $stmt->bindParam(":ai_response", $ai_response_json);
    $stmt->bindParam(":status", $status);
    
    if($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "message" => "Scan completed successfully (Mode: " . ($result ? "AI" : "Simulation") . ")",
            "data" => $ai_response
        ]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Unable to save scan results."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
}
?>
