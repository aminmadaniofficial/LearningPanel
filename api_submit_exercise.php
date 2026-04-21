<?php
// Filepath: /api_submit_exercise.php
session_start();
define('APP_ACCESS', true);
require_once 'config.php';
header('Content-Type: application/json');

// Authentication Check
if (!isset($_SESSION['user']['username'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to continue']);
    exit;
}

$user = Config::getUser($_SESSION['user']['username']);
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User profile not found']);
    exit;
}

$exercise_id = $_POST['exercise_id'] ?? null;
$code = trim($_POST['code'] ?? '');

// Validation
if (!$exercise_id || $code === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Exercise ID or code content is missing']);
    exit;
}

// Ensure exercise_id is treated as a clean string (e.g., 'a_prac_1')
$exercise_id = trim($exercise_id);

try {
    $pdo = Config::db();
    
    /* Using ON DUPLICATE KEY UPDATE to allow students to resubmit their code.
       This resets the status to 'pending' so mentors know it needs a re-review.
    */
    $stmt = $pdo->prepare("
        INSERT INTO exercise_submissions (user_id, exercise_id, code, status) 
        VALUES (?, ?, ?, 'pending')
        ON DUPLICATE KEY UPDATE 
            code = VALUES(code), 
            status = 'pending', 
            submitted_at = NOW()
    ");
    
    $stmt->execute([$user['id'], $exercise_id, $code]);

    echo json_encode(['success' => true, 'message' => 'Code submitted successfully!']);
    
} catch (Exception $e) {
    http_response_code(500);
    // Logging the error internally while showing a generic message to the user is safer
    echo json_encode(['success' => false, 'message' => 'Server error occurred during submission']);
}