<?php
/**
 * Education Platform Configuration - SAMPLE VERSION
 * Bahonar 3 Programming Community
 * * Instructions:
 * 1. Copy this file and rename it to 'config.php'
 * 2. Update the Database Credentials with your own settings.
 * * @author Mohammad Amin Madani Mohammadi
 * @version 2.1.0-DB-SAMPLE
 */

if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// --- BASE SETTINGS ---
define('APP_NAME', 'Bahonar 3 Programming Community');
define('APP_VERSION', '2.1.0');
define('APP_DESCRIPTION', 'Modern Education Platform with MySQL Backend');

// --- PATH CONSTANTS ---
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/views');

// --- DATABASE CREDENTIALS (CHANGE THESE IN config.php) ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name'); // اسم دیتابیس خود را اینجا بنویسید
define('DB_USER', 'root');                // نام کاربری دیتابیس
define('DB_PASS', '');                    // رمز عبور دیتابیس
define('DB_CHARSET', 'utf8mb4');

// --- SESSION SETTINGS ---
define('SESSION_COOKIE_SAMESITE', 'Strict');

// --- SECURITY CONFIGURATIONS ---
define('SESSION_LIFETIME', 3600 * 24); // 24 Hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 Minutes
define('DEBUG_MODE', false); // Set to true for development

// --- ERROR REPORTING LOGIC ---
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

date_default_timezone_set('Asia/Tehran');

/**
 * Config Class: Manages Database Connectivity and Core Logic
 */
class Config {
    private static $pdo = null;

    // Database Connection (Singleton Pattern)
    public static function db() {
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                if (DEBUG_MODE) {
                    die("Database Connection Error: " . $e->getMessage());
                }
                displayError("Service unavailable. Please try again later.", 503);
            }
        }
        return self::$pdo;
    }

    // Fetch User Record
    public static function getUser($username) {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    // Update User Profile
    public static function updateUser($username, $updates) {
        $sets = [];
        $values = [];
        foreach ($updates as $key => $value) {
            $sets[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $username;
        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE username = ?";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($values);
    }

    // Fetch Content (Videos, Exercises, Tests)
    public static function getGroupContent($group, $type = 'videos') {
        $pdo = self::db();
        switch ($type) {
            case 'videos':
                $stmt = $pdo->prepare("
                    SELECT vc.title as categoryTitle, v.*
                    FROM videos v
                    JOIN video_categories vc ON v.category_id = vc.id
                    WHERE vc.group_name = ?
                    ORDER BY vc.id, v.id
                ");
                $stmt->execute([$group]);
                $rows = $stmt->fetchAll();

                $result = [];
                $currentCat = null;
                foreach ($rows as $row) {
                    if ($currentCat !== $row['categoryTitle']) {
                        $currentCat = $row['categoryTitle'];
                        $result[] = ['categoryTitle' => $currentCat, 'videos' => []];
                    }
                    $result[count($result)-1]['videos'][] = [
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'duration' => $row['duration'],
                        'thumbnailUrl' => $row['thumbnail_url'],
                        'videoUrl' => $row['video_url']
                    ];
                }
                return $result;

            case 'exercises':
                $stmt = $pdo->prepare("SELECT * FROM exercises WHERE group_name = ? ORDER BY id");
                $stmt->execute([$group]);
                return $stmt->fetchAll();

            case 'tests':
                $stmt = $pdo->prepare("SELECT * FROM tests WHERE group_name = ? LIMIT 1");
                $stmt->execute([$group]);
                return $stmt->fetch();

            default:
                return null;
        }
    }

    // Calculate Learning Progress
    public static function calculateProgress($group, $watchedVideos) {
        $totalStmt = self::db()->prepare("
            SELECT COUNT(*) FROM videos v
            JOIN video_categories vc ON v.category_id = vc.id
            WHERE vc.group_name = ?
        ");
        $totalStmt->execute([$group]);
        $total = $totalStmt->fetchColumn();

        $watched = is_array($watchedVideos) ? count($watchedVideos) : 0;
        $percentage = $total > 0 ? round(($watched / $total) * 100) : 0;

        return [
            'total' => (int)$total,
            'watched' => $watched,
            'percentage' => $percentage,
            'remaining' => $total - $watched
        ];
    }

    // System Logging
    public static function log($message, $userId = null, $type = 'info') {
        $stmt = self::db()->prepare("
            INSERT INTO logs (user_id, action, description, ip, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $type, $message, self::getUserIP()]);
    }

    // Get Client IP Address
    public static function getUserIP() {
        $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key]) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
                return $_SERVER[$key];
            }
        }
        return 'UNKNOWN';
    }

    // Input Sanitization Utility
    public static function sanitize($input, $type = 'string') {
        switch ($type) {
            case 'int': return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'email': return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url': return filter_var($input, FILTER_SANITIZE_URL);
            default: return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
}

// --- GLOBAL HELPER FUNCTIONS ---
function displayError($message, $code = 500) {
    http_response_code($code);
    ?>
    <!DOCTYPE html>
    <html lang="en" dir="ltr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - <?= APP_NAME ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
        <style>body{font-family:'Inter',sans-serif}</style>
    </head>
    <body class="bg-gray-50 flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-2xl shadow-2xl p-10 max-w-md text-center border border-gray-100">
            <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-black text-gray-900 mb-2">System Error</h1>
            <p class="text-gray-500 mb-8 leading-relaxed"><?= htmlspecialchars($message) ?></p>
            <a href="index.php" class="inline-block px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">Return Home</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

function redirect($url, $message = null) {
    if ($message) $_SESSION['flash_message'] = $message;
    header("Location: $url");
    exit;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}