<?php // Filepath: /views/profile.php
if (!isset($user) || !isset($user['username'])) displayError("Unauthorized access", 403);

$currentUser = Config::getUser($user['username']);
$userId = $currentUser['id'];
$userGroup = $currentUser['user_group'];
$userName = htmlspecialchars($currentUser['name'] ?? $user['username']);

// --- Videos Statistics ---
$content = Config::getGroupContent($userGroup, 'videos');
$total_videos = 0;
foreach ($content as $cat) $total_videos += count($cat['videos'] ?? []);

$stmt = Config::db()->prepare("SELECT COUNT(*) FROM watched_videos WHERE user_id = ?");
$stmt->execute([$userId]);
$watched_count = $stmt->fetchColumn();

// Fetch all watched video IDs to optimize progress calculation
$stmt = Config::db()->prepare("SELECT video_id FROM watched_videos WHERE user_id = ?");
$stmt->execute([$userId]);
$watchedVideosMap = $stmt->fetchAll(PDO::FETCH_COLUMN); 

$completion_percentage = $total_videos > 0 ? round(($watched_count / $total_videos) * 100) : 0;
$remaining_videos = $total_videos - $watched_count;
$estimated_hours = $remaining_videos > 0 ? ceil($remaining_videos * 15 / 60) : 0;

// User Level Logic
$level = 'Beginner';
$level_color = 'text-gray-600';
if ($completion_percentage >= 75) { $level = 'Expert'; $level_color = 'text-purple-600'; }
elseif ($completion_percentage >= 50) { $level = 'Advanced'; $level_color = 'text-blue-600'; }
elseif ($completion_percentage >= 25) { $level = 'Intermediate'; $level_color = 'text-green-600'; }

// --- Exercises Statistics ---
$exercises = Config::getGroupContent($userGroup, 'exercises');
$total_exercises = count($exercises);

$stmt = Config::db()->prepare("SELECT COUNT(*) FROM exercise_submissions WHERE user_id = ? AND status = 'approved'");
$stmt->execute([$userId]);
$approved_exercises = $stmt->fetchColumn();

// --- Exams Statistics ---
$stmt = Config::db()->prepare("SELECT COUNT(*) FROM exam_submissions WHERE user_id = ? AND status = 'graded' AND score >= 70");
$stmt->execute([$userId]);
$passed_exams = $stmt->fetchColumn();

$stmt = Config::db()->prepare("SELECT COUNT(*) FROM exams WHERE group_name = ? AND is_active = 1");
$stmt->execute([$userGroup]);
$active_exams = $stmt->fetchColumn();
?>

<div class="space-y-6 max-w-5xl mx-auto">

    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-2xl p-8 text-white">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            <div class="relative">
                <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center shadow-2xl">
                    <svg class="w-20 h-20 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 px-3 py-1 bg-yellow-400 text-yellow-900 rounded-full text-xs font-black shadow-lg uppercase">
                    <?= htmlspecialchars($userGroup) ?> Group
                </div>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h2 class="text-4xl font-black mb-2"><?= $userName ?></h2>
                <p class="text-xl font-medium text-indigo-100 mb-4">Bahonar Coding Association Member</p>
                <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                    <span class="px-4 py-2 bg-white bg-opacity-20 rounded-full font-bold backdrop-blur-sm">
                        Level: <span class="<?= $level_color ?>"><?= $level ?></span>
                    </span>
                    <span class="px-4 py-2 bg-white bg-opacity-20 rounded-full font-bold backdrop-blur-sm">
                        <?= $watched_count ?> Videos
                    </span>
                    <span class="px-4 py-2 bg-white bg-opacity-20 rounded-full font-bold backdrop-blur-sm">
                        <?= $approved_exercises ?> Solved Challenges
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass-card rounded-xl p-6 text-center hover:scale-105 transition">
            <div class="w-16 h-16 bg-blue-500 rounded-full mx-auto mb-3 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-3xl font-black"><?= $total_videos ?></h3>
            <p class="text-sm text-gray-600 uppercase font-bold tracking-tighter">Total Videos</p>
        </div>

        <div class="glass-card rounded-xl p-6 text-center hover:scale-105 transition">
            <div class="w-16 h-16 bg-green-500 rounded-full mx-auto mb-3 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h3 class="text-3xl font-black"><?= $watched_count ?></h3>
            <p class="text-sm text-gray-600 uppercase font-bold tracking-tighter">Completed</p>
        </div>

        <div class="glass-card rounded-xl p-6 text-center hover:scale-105 transition">
            <div class="w-16 h-16 bg-orange-500 rounded-full mx-auto mb-3 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <h3 class="text-3xl font-black"><?= $total_exercises ?></h3>
            <p class="text-sm text-gray-600 uppercase font-bold tracking-tighter">Challenges</p>
        </div>

        <div class="glass-card rounded-xl p-6 text-center hover:scale-105 transition">
            <div class="w-16 h-16 bg-purple-500 rounded-full mx-auto mb-3 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-3xl font-black"><?= $active_exams ?></h3>
            <p class="text-sm text-gray-600 uppercase font-bold tracking-tighter">Active Exams</p>
        </div>
    </div>

    <div class="glass-card rounded-xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Course Progress</h3>
            <span class="text-3xl font-black text-indigo-600"><?= $completion_percentage ?>%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-6 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-end px-3 transition-all duration-1000"
                 style="width: <?= $completion_percentage ?>%">
                <span class="text-white text-sm font-bold"><?= $completion_percentage ?>%</span>
            </div>
        </div>
        <?php if ($remaining_videos > 0): ?>
            <p class="mt-3 text-sm text-gray-600 font-medium italic">Approximately <?= $estimated_hours ?> hours left until completion!</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($content)): ?>
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-xl font-bold mb-6">Progress by Category</h3>
            <div class="space-y-5">
                <?php foreach ($content as $cat):
                    $cat_total = count($cat['videos'] ?? []);
                    $cat_watched = 0;
                    
                    // Optimized search within already fetched watchedVideosMap
                    foreach ($cat['videos'] as $v) {
                        if (in_array($v['id'], $watchedVideosMap)) {
                            $cat_watched++;
                        }
                    }
                    
                    $cat_perc = $cat_total > 0 ? round($cat_watched / $cat_total * 100) : 0;
                ?>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="font-semibold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($cat['categoryTitle']) ?></span>
                            <span class="text-sm font-bold"><?= $cat_watched ?> / <?= $cat_total ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all"
                                 style="width: <?= $cat_perc ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($completion_percentage < 100): ?>
        <div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-2xl p-8 text-white text-center">
            <h3 class="text-3xl font-black mb-3">Keep it up, Champ! 💪</h3>
            <p class="text-xl">Only <?= $remaining_videos ?> more videos to reach Expert level!</p>
        </div>
    <?php else: ?>
        <div class="bg-gradient-to-r from-green-400 to-emerald-600 rounded-2xl p-8 text-white text-center">
            <h3 class="text-3xl font-black mb-3">Congratulations! 🎉</h3>
            <p class="text-xl">You have successfully completed the entire course. You are now a real developer!</p>
        </div>
    <?php endif; ?>

</div>