<div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2">
    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
        <button @click="open = !open"
                class="flex h-12 w-12 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-lg hover:scale-105 active:scale-95 transition-transform dark:border-ink-700 dark:bg-ink-800 dark:text-slate-300"
                aria-label="Theme">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </button>
        <div x-show="open" x-transition class="absolute bottom-14 right-0 w-40 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl dark:border-ink-700 dark:bg-ink-800">
            <button @click="theme = 'light'; open = false" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-ink-700" :class="theme === 'light' ? 'text-brand' : ''">
                <span class="h-3 w-3 rounded-full bg-amber-400"></span> Light
            </button>
            <button @click="theme = 'dark'; open = false" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-ink-700" :class="theme === 'dark' ? 'text-brand' : ''">
                <span class="h-3 w-3 rounded-full bg-ink-900"></span> Dark
            </button>
            <button @click="theme = 'system'; open = false" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-ink-700" :class="theme === 'system' ? 'text-brand' : ''">
                <span class="h-3 w-3 rounded-full bg-slate-400"></span> System
            </button>
        </div>
    </div>
</div>
