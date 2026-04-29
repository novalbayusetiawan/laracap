<div 
    x-data="{ show: false }" 
    x-on:sync-started.window="show = true"
    x-show="show"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm"
    x-cloak
>
    <div class="flex flex-col items-center gap-4 rounded-xl bg-white p-8 shadow-2xl dark:bg-gray-800">
        <svg class="h-12 w-12 animate-spin text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-lg font-medium text-gray-900 dark:text-white">
            Syncing system updates...
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            This may take a moment.
        </p>
    </div>
</div>
