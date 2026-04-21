<?php if(!defined('APP_ACCESS')) die('Direct access not allowed');?>

<div class="animate-fade-in-up max-w-3xl mx-auto">
    <div class="glass-card rounded-xl shadow-lg p-8 text-center mb-8">
        <div class="relative inline-block mb-6">
            <div class="w-24 h-24 bg-gradient-to-br from-gray-400 to-gray-500 rounded-full flex items-center justify-center shadow-xl">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center border-4 border-white dark:border-gray-800">
                <svg class="w-4 h-4 text-yellow-900" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>
        
        <h3 class="text-2xl font-black text-gray-800 dark:text-white mb-4">No Active Exams</h3>
        
        <div class="mt-8 p-6 bg-blue-50 dark:bg-blue-900 dark:bg-opacity-20 rounded-xl text-left">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white mb-2">Important Notes:</h4>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                        <li>Exams are activated periodically.</li>
                        <li>Exact schedules will be shared via notifications.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <a href="#" class="group relative block mb-8 overflow-hidden rounded-xl glass-card hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-2 border-orange-400/30">
        <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-orange-500 to-red-600"></div>
        <div class="p-6 flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-orange-100 dark:bg-orange-900/50 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="animate-pulse w-2 h-2 bg-red-500 rounded-full inline-block"></span>
                        <span class="text-xs font-bold text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/30 px-2 py-0.5 rounded-md">NEW</span>
                    </div>
                    <h4 class="font-black text-lg md:text-xl text-gray-800 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                        Answer Key & Analysis - Exam #1
                    </h4>
                </div>
            </div>
        </div>
    </a>
</div>