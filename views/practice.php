<?php // Filepath: /views/practice.php

// Ensure config.php is included before this file (from dashboard.php)
if (!isset($user) || !isset($user['username'])) {
    displayError("Unauthorized access", 403);
}

// Fetch complete user data from database
$currentUser = Config::getUser($user['username']);
if (!$currentUser) {
    displayError("User not found", 404);
}

$userGroup = $currentUser['user_group'];
$userId = $currentUser['id'];

// Fetch exercises from JSON using Config method
$content = Config::getGroupContent($userGroup, 'exercises');

// Fetch user submission statuses
$stmt = Config::db()->prepare("
    SELECT exercise_id, status FROM exercise_submissions 
    WHERE user_id = ? 
");
$stmt->execute([$userId]);
$submissions = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $submissions[$row['exercise_id']] = $row['status'];
}

// UI Colors and Badges configuration
$difficulty_colors = [
    'Easy'   => 'bg-green-100 text-green-800 border-green-500',
    'Medium' => 'bg-yellow-100 text-yellow-800 border-yellow-500',
    'Hard'   => 'bg-red-100 text-red-800 border-red-500',
];

$status_badges = [
    'pending'  => '<span class="px-3 py-1 bg-yellow-500 text-white text-xs font-bold rounded-full animate-pulse">Pending Review</span>',
    'approved' => '<span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full">Approved ✓</span>',
    'rejected' => '<span class="px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full">Revision Needed ✗</span>',
];
?>

<div class="space-y-6 animate-fade-in-up">
    
    <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl shadow-xl p-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-black mb-2 uppercase tracking-tight">Practice Challenges</h2>
                <p class="text-purple-100">Strengthen your skills by solving real-world problems</p>
            </div>
            <div class="mt-4 md:mt-0 bg-white bg-opacity-20 rounded-xl p-4 backdrop-blur-sm">
                <div class="text-center">
                    <div class="text-4xl font-black mb-1"><?= count($content) ?></div>
                    <div class="text-sm font-medium uppercase">Available Tasks</div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($content)): ?>
        <div class="glass-card rounded-2xl shadow-xl p-12 text-center">
            <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-2xl font-bold text-gray-700 mb-2">No Exercises Found</h3>
            <p class="text-gray-500">No practice tasks have been assigned to your group yet.</p>
        </div>
    <?php else: ?>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($content as $index => $ex):
                $status = $submissions[$ex['id']] ?? null;
                $color = $difficulty_colors[$ex['difficulty']] ?? 'bg-gray-100 text-gray-800 border-gray-500';
            ?>
                <div class="glass-card rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-300 cursor-pointer group overflow-hidden"
                     data-open-practice-modal
                     data-id="<?= $ex['id'] ?>"
                     data-title="<?= htmlspecialchars($ex['title']) ?>"
                     data-description="<?= htmlspecialchars($ex['description']) ?>"
                     data-difficulty="<?= htmlspecialchars($ex['difficulty']) ?>">
                    
                    <div class="h-2 bg-gradient-to-r from-purple-500 to-pink-500"></div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center text-white font-black text-xl shadow-lg">
                                <?= $index + 1 ?>
                            </div>
                            <span class="px-3 py-1 text-xs font-bold rounded-full border-2 <?= $color ?> flex items-center">
                                <?= htmlspecialchars($ex['difficulty']) ?>
                            </span>
                        </div>

                        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-3 line-clamp-2 group-hover:text-purple-600 transition-colors">
                            <?= htmlspecialchars($ex['title']) ?>
                        </h3>

                        <?php if ($status): ?>
                            <div class="mb-4"><?= $status_badges[$status] ?></div>
                        <?php endif; ?>

                        <div class="w-full px-4 py-3 font-bold text-white bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg text-center uppercase text-sm tracking-wider">
                            <?= $status ? 'View / Edit' : 'Start Challenge' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="practice-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-80 backdrop-blur-sm">
    <div class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-purple-500 to-pink-600 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 id="modal-title" class="text-2xl font-black text-white mb-2"></h3>
                    <div id="modal-difficulty-badge"></div>
                </div>
                <button data-close-modal class="p-2 bg-white bg-opacity-20 rounded-full hover:bg-opacity-30 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-6 space-y-6 max-h-screen overflow-y-auto">
            <div>
                <h4 class="text-lg font-bold text-gray-800 dark:text-white mb-3">Challenge Description</h4>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p id="modal-description" class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap"></p>
                </div>
            </div>

            <form id="submit-code-form" class="space-y-4">
                <input type="hidden" name="exercise_id" id="modal-exercise-id">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Solution (Code):</label>
                <textarea dir="ltr" name="code" rows="14" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-mono text-sm focus:ring-2 focus:ring-purple-500 outline-none" placeholder="// Write your code here..." required></textarea>
                <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-black rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all text-lg shadow-lg hover:shadow-xl uppercase">
                    Submit Code for Review
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('practice-modal');
    const form = document.getElementById('submit-code-form');
    const textarea = document.querySelector('#submit-code-form textarea[name="code"]');

    // Handle opening modal and fetching previous code
    document.querySelectorAll('[data-open-practice-modal]').forEach(btn => {
        btn.onclick = async () => {
            document.getElementById('modal-title').textContent = btn.dataset.title;
            document.getElementById('modal-description').textContent = btn.dataset.description;
            document.getElementById('modal-exercise-id').value = btn.dataset.id;

            const badgeContainer = document.getElementById('modal-difficulty-badge');
            const diff = btn.dataset.difficulty;
            
            let badgeClass = 'px-3 py-1 text-white rounded-full text-xs font-bold uppercase';
            if (diff === 'Easy') badgeClass += ' bg-green-500';
            else if (diff === 'Medium') badgeClass += ' bg-yellow-500';
            else badgeClass += ' bg-red-500';

            badgeContainer.className = badgeClass;
            badgeContainer.textContent = diff;

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Fetch previous submission if exists
            const exerciseId = btn.dataset.id;
            try {
                const res = await fetch(`api_get_submission.php?exercise_id=${exerciseId}`);
                const data = await res.json();
                textarea.value = (data.success && data.code) ? data.code : '';
            } catch (e) {
                console.error("Error fetching submission:", e);
                textarea.value = '';
            }
        };
    });

    // Close modal logic
    const hideModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    document.querySelectorAll('[data-close-modal]').forEach(btn => btn.onclick = hideModal);
    modal.onclick = (e) => { if (e.target === modal) hideModal(); };

    // Form submission
    form.onsubmit = async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        try {
            const res = await fetch('api_submit_exercise.php', { method: 'POST', body: fd });
            const data = await res.json();
            
            if (data.success) {
                alert('Success! Your code has been submitted for review.');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('A network error occurred. Please try again.');
        }
    };
});
</script>