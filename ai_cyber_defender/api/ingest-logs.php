<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->logs) && is_array($data->logs)) {
    $results = [];
    $source = isset($data->source) ? $data->source : 'unknown';

    foreach ($data->logs as $log_line) {
        if (empty(trim($log_line))) continue;

        // Call Python AI model
        $python_api_url = "http://127.0.0.1:5000/api/predict";
        $ch = curl_init($python_api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["input_text" => $log_line]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($result && $http_code == 200) {
            $ai_response = json_decode($result, true);
            $status = (isset($ai_response['prediction']) && in_array($ai_response['prediction'], ['anomaly', 'malicious'])) ? 'Malicious' : 'Secure';
            
            // Insert into ai_requests
            $userId = isset($data->user_id) ? $data->user_id : 2;
            $query = "INSERT INTO ai_requests (user_id, scan_target, scan_type, ai_response, status) VALUES (:user_id, :scan_target, 'log', :ai_response, :status)";
            $stmt = $pdo->prepare($query);
            $ai_response_json = json_encode($ai_response);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":scan_target", $log_line);
            $stmt->bindParam(":ai_response", $ai_response_json);
            $stmt->bindParam(":status", $status);
            $stmt->execute();

            $results[] = [
                "log" => $log_line,
                "status" => $status,
                "ai_response" => $ai_response
            ];
        } else {
            $results[] = [
                "log" => $log_line,
                "status" => "Error",
                "message" => "Failed to reach AI model."
            ];
        }
    }

    http_response_code(200);
    echo json_encode([
        "message" => "Logs ingested and processed.",
        "results" => $results
    ]);

} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid payload. Provide an array of logs."]);
}
?>
