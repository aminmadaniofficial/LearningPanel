<?php
// Filepath: /api_get_submission.php
session_start();
define('APP_ACCESS', true);
require_once 'config.php';
header('Content-Type: application/json');

// Authentication Check
if (!isset($_SESSION['user']['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$user = Config::getUser($_SESSION['user']['username']);
if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User profile not found']);
    exit;
}

$exercise_id = $_GET['exercise_id'] ?? null;
if (!$exercise_id || empty(trim($exercise_id))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Exercise ID is required']);
    exit;
}

// Keep as string to support IDs like 'a_prac_1'
$exercise_id = trim($exercise_id);

try {
    /* Fetch the most recent code submission for this user and exercise.
       We order by submitted_at just in case the unique constraint logic 
       changes in the future, but LIMIT 1 keeps it efficient.
    */
    $stmt = Config::db()->prepare("
        SELECT code FROM exercise_submissions 
        WHERE user_id = ? AND exercise_id = ? 
        ORDER BY submitted_at DESC LIMIT 1
    ");
    $stmt->execute([$user['id'], $exercise_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'code' => $row ? $row['code'] : '' // Return empty string if no submission exists
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    // In production, it's better to hide $e->getMessage() and log it instead
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}