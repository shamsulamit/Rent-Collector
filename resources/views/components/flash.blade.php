@if (session('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4500)" x-transition
         class="flex items-center gap-2 rounded-xl border border-brand/30 bg-brand/10 px-4 py-3 text-sm text-brand-dark dark:text-brand-light">
        <span class="h-2 w-2 shrink-0 rounded-full bg-brand"></span>
        {{ session('message') }}
    </div>
@endif
@if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition
         class="flex items-center gap-2 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-600 dark:text-rose-400">
        <span class="h-2 w-2 shrink-0 rounded-full bg-rose-500"></span>
        {{ session('error') }}
    </div>
@endif
