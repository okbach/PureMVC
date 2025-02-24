<?php
function response(string $status, mixed $result = null, array $errors = []): void
{
    http_response_code($status === 'success' ? 200 : 400);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'result' => $result,
        'errors' => $errors
    ]);
    exit; 
}
?>