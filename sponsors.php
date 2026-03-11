<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? '';

switch ($action) {
    case 'get_sponsors':
        try {
            $stmt = $pdo->query("SELECT * FROM sponsors ORDER BY display_order");
            $sponsors = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $sponsors]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_sponsor':
        $name = $input['name'] ?? '';
        $logo = $input['logo_url'] ?? '';
        $website = $input['website_url'] ?? '';

        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO sponsors (id, name, logo_url, website_url) VALUES (UUID(), ?, ?, ?)");
            $stmt->execute([$name, $logo, $website]);
            echo json_encode(['success' => true, 'message' => 'Sponsor added successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update_sponsor':
        $id = $input['id'] ?? '';
        $name = $input['name'] ?? '';
        $logo = $input['logo_url'] ?? '';
        $website = $input['website_url'] ?? '';
        $is_active = $input['is_active'] ?? 1;

        if (!$id || !$name) {
            echo json_encode(['success' => false, 'message' => 'ID and Name are required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE sponsors SET name = ?, logo_url = ?, website_url = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $logo, $website, $is_active, $id]);
            echo json_encode(['success' => true, 'message' => 'Sponsor updated successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_sponsor':
        $id = $input['id'] ?? '';
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID is required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM sponsors WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Sponsor deleted successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_partnerships':
        try {
            $stmt = $pdo->query("SELECT * FROM partnership_agreements ORDER BY agreement_date DESC");
            $partnerships = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $partnerships]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_partnership':
        $partner_name = $input['partner_name'] ?? '';
        $partnership_type = $input['partnership_type'] ?? '';
        $agreement_date = $input['agreement_date'] ?? '';
        $status = $input['status'] ?? '';
        $benefits = $input['benefits'] ?? '';

        if (!$partner_name || !$agreement_date) {
            echo json_encode(['success' => false, 'message' => 'Partner name and agreement date are required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO partnership_agreements (partner_name, partnership_type, agreement_date, status, benefits) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$partner_name, $partnership_type, $agreement_date, $status, $benefits]);
            echo json_encode(['success' => true, 'message' => 'Partnership created successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_partnership':
        $id = $input['id'] ?? '';
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID is required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM partnership_agreements WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Partnership deleted successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_funding':
        try {
            $stmt = $pdo->query("SELECT * FROM sponsor_funding ORDER BY funding_date DESC");
            $funding = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $funding]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_funding':
        $source_name = $input['source_name'] ?? '';
        $amount = $input['amount'] ?? '';
        $funding_date = $input['funding_date'] ?? '';
        $method = $input['method'] ?? '';
        $status = $input['status'] ?? '';

        if (!$source_name || !$amount || !$funding_date) {
            echo json_encode(['success' => false, 'message' => 'Source name, amount, and funding date are required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO sponsor_funding (source_name, amount, funding_date, method, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$source_name, $amount, $funding_date, $method, $status]);
            echo json_encode(['success' => true, 'message' => 'Funding record created successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_funding':
        $id = $input['id'] ?? '';
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID is required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM sponsor_funding WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Funding record deleted successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
