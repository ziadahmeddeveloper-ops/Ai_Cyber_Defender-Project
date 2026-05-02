<?php
require_once 'config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        scan_target TEXT NOT NULL,
        scan_type VARCHAR(50) NOT NULL,
        ai_response JSON NULL,
        status VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "Table ai_requests is ready.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
