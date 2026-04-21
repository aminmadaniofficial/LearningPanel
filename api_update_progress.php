<?php
// Filepath: /api_update_progress.php
session_start();
header('Content-Type: application/json');

// Database Configuration
$host = 'localhost';
$db   = 'bahonar3';
$user = 'root';
$pass = '0315324457Mm';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Authentication Check
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$username = $_SESSION['user']['username'];
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validation
if (json_last_error() !== JSON_ERROR_NONE || !isset($data['videoId']) || empty(trim($data['videoId']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

$videoId = trim($data['videoId']);

// Fetch User Info and Group
$stmt = $pdo->prepare("SELECT id, user_group FROM users WHERE username = ?");
$stmt->execute([$username]);
$userDb = $stmt->fetch();

if (!$userDb) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$userId = $userDb['id'];
$userGroup = $userDb['user_group'];

// Check if already watched
$stmt = $pdo->prepare("SELECT id FROM watched_videos WHERE user_id = ? AND video_id = ?");
$stmt->execute([$userId, $videoId]);
$alreadyWatched = $stmt->fetchColumn() !== false;

// Register watch activity if new
if (!$alreadyWatched) {
    $stmt = $pdo->prepare("INSERT INTO watched_videos (user_id, video_id) VALUES (?, ?)");
    $stmt->execute([$userId, $videoId]);
    
    // Update Session (for instant UI feedback)
    if (!isset($_SESSION['user']['watchedVideos'])) {
        $_SESSION['user']['watchedVideos'] = [];
    }
    if (!in_array($videoId, $_SESSION['user']['watchedVideos'])) {
        $_SESSION['user']['watchedVideos'][] = $videoId;
    }
}

// ==========================================================
// START: Progress Calculation Logic
// ==========================================================

// Calculate total videos available for the user's specific group
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM videos v 
    JOIN video_categories vc ON v.category_id = vc.id 
    WHERE vc.group_name = ?
");
$stmt->execute([$userGroup]);
$totalVideos = (int)$stmt->fetchColumn();

// Count total watched videos directly from database
$stmt = $pdo->prepare("SELECT COUNT(*) FROM watched_videos WHERE user_id = ?");
$stmt->execute([$userId]);
$watchedCount = (int)$stmt->fetchColumn();

// Calculate percentage
$completionPercentage = $totalVideos > 0 ? round(($watchedCount / $totalVideos) * 100) : 0;

// ==========================================================
// END: Progress Calculation Logic
// ==========================================================

// Final Response
echo json_encode([
    'success' => true,
    'message' => $alreadyWatched ? 'Already watched' : 'Progress updated successfully',
    'data' => [
        'videoId' => $videoId,
        'totalVideos' => $totalVideos,
        'watchedCount' => $watchedCount,
        'completionPercentage' => $completionPercentage,
        'alreadyWatched' => $alreadyWatched
    ]
]);
?>