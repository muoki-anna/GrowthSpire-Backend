<?php
require_once 'db.php';

header('Content-Type: application/json');

/**
 * Handle successful response
 */
function sendSuccess($message, $data = null)
{
    if (is_array($data) && !isset($data['success'])) {
        $result = array_merge(['success' => true, 'message' => $message], $data);
        echo json_encode($result);
    }
    else {
        echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    }
    exit;
}

/**
 * Handle error response
 */
function sendError($message)
{
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

try {
    // 1. Portfolio Startups (Vetted)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM portfolio_startups");
    $startupsCount = $stmt->fetch()['count'];

    // 2. Active Applications (Startup type)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM applications WHERE application_type = 'startup'");
    $startupAppsCount = $stmt->fetch()['count'];

    // 3. Sponsors count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sponsors");
    $sponsorsCount = $stmt->fetch()['count'];

    // 4. Mentors count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM mentors");
    $mentorCount = $stmt->fetch()['count'];

    // 5. Recent Startup Portfolio
    $stmt = $pdo->query("SELECT id, name, founder, category, status, founded_year FROM portfolio_startups ORDER BY created_at DESC LIMIT 5");
    $recentStartups = $stmt->fetchAll();

    // 6. Recent Sponsors
    $stmt = $pdo->query("SELECT id, name, website_url, is_active FROM sponsors ORDER BY created_at DESC LIMIT 5");
    $recentSponsors = $stmt->fetchAll();

    // 7. Recent Startup Applications
    $stmt = $pdo->query("SELECT id, company_name as name, industry as sector, status, created_at FROM applications WHERE application_type = 'startup' ORDER BY created_at DESC LIMIT 5");
    $recentApplications = $stmt->fetchAll();

    sendSuccess('Dashboard stats retrieved', [
        'stats' => [
            'startups' => (int)$startupsCount,
            'applications' => (int)$startupAppsCount,
            'sponsors' => (int)$sponsorsCount,
            'mentors' => (int)$mentorCount
        ],
        'recentStartups' => $recentStartups,
        'recentSponsors' => $recentSponsors,
        'recentApplications' => $recentApplications
    ]);
}
catch (Exception $e) {
    sendError($e->getMessage());
}
?>
