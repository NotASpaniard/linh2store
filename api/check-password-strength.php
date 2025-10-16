<?php
/**
 * Password Strength Check API
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/password-validator.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$password = $input['password'] ?? '';

if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Password is required']);
    exit();
}

try {
    $strength_result = PasswordValidator::checkStrength($password);
    $html = PasswordValidator::getStrengthIndicator($password);
    
    echo json_encode([
        'success' => true,
        'strength' => $strength_result['strength'],
        'score' => $strength_result['score'],
        'max_score' => $strength_result['max_score'],
        'is_valid' => $strength_result['is_valid'],
        'feedback' => $strength_result['feedback'],
        'html' => $html
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error checking password strength'
    ]);
}
?>
