<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$customer_id = $input['customer_id'] ?? null;

if (!$customer_id) {
    echo json_encode(['success' => false, 'error' => 'Customer ID is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
    }
    else {
        echo json_encode(['success' => false, 'error' => 'Customer not found']);
    }
}
catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
