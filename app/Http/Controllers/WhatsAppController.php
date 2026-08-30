<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function __construct(protected WhatsAppService $whatsapp) {}

    public function send(Request $request, Tenant $tenant): RedirectResponse
    {
        $template = $request->query('template', 'custom');
        $locale = $request->query('locale', \App\Models\Setting::get('whatsapp_locale', 'en'));

        $payload = $this->whatsapp->build($template, $tenant, $request->query(), $locale);

        WhatsAppMessage::create([
            'tenant_id' => $tenant->id,
            'phone' => $payload['phone'],
            'template' => $template,
            'locale' => $locale,
            'message' => $payload['message'],
            'variables' => $payload['variables'],
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        if (! $payload['phone']) {
            session()->flash('error', 'This tenant has no WhatsApp number on file.');

            return back();
        }

        return redirect()->to(WhatsAppMessage::deepLink($payload['phone']).'?text='.urlencode($payload['message']));
    }

    public function history(): View
    {
        $messages = WhatsAppMessage::with('tenant')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('whatsapp.history', compact('messages'));
    }
}
