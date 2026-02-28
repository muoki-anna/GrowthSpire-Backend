<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? '';
$package_id = $input['package_id'] ?? '';

if (!$user_id || !$package_id) {
    echo json_encode(['success' => false, 'message' => 'User ID and Package ID are required']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->execute([$package_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Package deleted successfully']);
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Package not found or already deleted']);
    }
}
catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
