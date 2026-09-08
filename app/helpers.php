<?php

if (! function_exists('notify')) {
    function notify(string $message, string $type = 'success'): void
    {
        session()->flash($type === 'error' ? 'error' : 'message', $message);

        try {
            if (class_exists(\Livewire\Livewire::class) && method_exists(\Livewire\Livewire::class, 'current')) {
                \Livewire\Livewire::current()?->dispatch('notify', message: $message, type: $type);
            }
        } catch (\Throwable) {
            // Full-page requests still show the layout flash.
        }
    }
}
