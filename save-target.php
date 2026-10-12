<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (isset($data['mindData'])) {
        $base64Data = $data['mindData'];
        // Remove data URI scheme if present
        if (strpos($base64Data, 'base64,') !== false) {
            $base64Data = explode('base64,', $base64Data)[1];
        }
        $binary = base64_decode($base64Data);
        if ($binary !== false) {
            $targetDir = __DIR__ . '/assets/targets/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            file_put_contents($targetDir . 'targets.mind', $binary);
            echo json_encode(['ok' => true]);
            exit;
        }
    }
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid data']);
}