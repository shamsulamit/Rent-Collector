@php
    $colors = [
        'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'gray' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/15 dark:text-slate-300',
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/15 dark:text-slate-300',
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300',
        'sky' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
        'teal' => 'bg-teal-100 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300',
        'rose' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
    ];
    $color = $colors[$color] ?? $colors['slate'];
@endphp
<span class="badge {{ $color }}">
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    {{ $label }}
</span>
