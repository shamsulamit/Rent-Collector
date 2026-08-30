<?php

namespace App\Services\WhatsApp;

use App\Models\Bill;
use App\Models\Tenant;
use App\Models\WhatsAppMessage;

class WhatsAppService
{
    /**
     * Deep link to a WhatsApp conversation.
     */
    public static function link(?string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $phone);
        if ($phone === '') {
            return '#';
        }

        return 'https://wa.me/'.$phone;
    }

    public function build(string $template, Tenant $tenant, array $variables = [], string $locale = 'en'): array
    {
        $texts = $this->templates($locale);

        $defaults = [
            '{tenant_name}' => $tenant->full_name,
            '{property_name}' => $variables['property_name'] ?? '',
            '{unit}' => $variables['unit'] ?? '',
            '{month}' => $variables['month'] ?? '',
            '{total_bill}' => isset($variables['total_bill']) ? number_format((float) $variables['total_bill'], 2) : '',
            '{paid}' => isset($variables['paid']) ? number_format((float) $variables['paid'], 2) : '',
            '{due}' => isset($variables['due']) ? number_format((float) $variables['due'], 2) : '',
        ];

        $message = strtr($texts[$template] ?? $texts['custom'], $defaults);

        return [
            'phone' => $tenant->whatsapp_number ?: $tenant->phone,
            'message' => $message,
            'variables' => $variables,
        ];
    }

    public function templates(string $locale = 'en'): array
    {
        if ($locale === 'bn') {
            return [
                'monthly_bill' => "আসসালামু আলাইকুম {tenant_name},\n\n{month} মাসের বিল নিম্নরূপ:\nইউনিট: {unit}\nবিলের পরিমাণ: ৳{total_bill}\nপরিশোধিত: ৳{paid}\nবাকি: ৳{due}\n\n{property_name}\nধন্যবাদ।",
                'payment_confirmation' => "আসসালামু আলাইকুম {tenant_name},\n\nআপনার {month} মাসের ৳{paid} পেমেন্ট সফলভাবে গ্রহণ করা হয়েছে। বাকি: ৳{due}\n\nধন্যবাদ।",
                'due_reminder' => "আসসালামু আলাইকুম {tenant_name},\n\n{month} মাসের বাকি ৳{due} দয়া করে পরিশোধ করুন।\n\nধন্যবাদ।",
                'overdue_reminder' => "আসসালামু আলাইকুম {tenant_name},\n\n{month} মাসের ৳{due} দীর্ঘদিন বাকি আছে। দয়া করে দ্রুত পরিশোধ করুন।\n\nধন্যবাদ।",
                'statement' => "আসসালামু আলাইকুম {tenant_name},\n\nআপনার স্টেটমেন্ট: ইউনিট {unit}, মোট বিল ৳{total_bill}, পরিশোধিত ৳{paid}, বাকি ৳{due}।\n\n{property_name}",
                'custom' => "{tenant_name}, {property_name}",
            ];
        }

        return [
            'monthly_bill' => "Hello {tenant_name},\n\nYour bill for {month}:\nUnit: {unit}\nTotal: ৳{total_bill}\nPaid: ৳{paid}\nDue: ৳{due}\n\n{property_name}\nThank you.",
            'payment_confirmation' => "Hello {tenant_name},\n\nWe received your payment of ৳{paid} for {month}. Remaining due: ৳{due}\n\nThank you.",
            'due_reminder' => "Hello {tenant_name},\n\nYour due balance for {month} is ৳{due}. Please make the payment.\n\nThank you.",
            'overdue_reminder' => "Hello {tenant_name},\n\nYour balance of ৳{due} for {month} is overdue. Please clear it soon.\n\nThank you.",
            'statement' => "Hello {tenant_name},\n\nYour statement: Unit {unit}, Total billed ৳{total_bill}, Paid ৳{paid}, Due ৳{due}.\n\n{property_name}",
            'custom' => "{tenant_name}, {property_name}",
        ];
    }

    public function sendBill(string $template, Bill $bill, string $locale = 'en'): WhatsAppMessage
    {
        $tenant = $bill->tenant;
        $unit = $bill->unit;
        $property = $bill->property;

        $payload = $this->build($template, $tenant, [
            'property_name' => $property?->name,
            'unit' => $unit?->name,
            'month' => $bill->billing_month,
            'total_bill' => $bill->total,
            'paid' => $bill->totalPaid(),
            'due' => $bill->balance(),
        ], $locale);

        return WhatsAppMessage::create([
            'tenant_id' => $tenant->id,
            'bill_id' => $bill->id,
            'phone' => $payload['phone'],
            'template' => $template,
            'locale' => $locale,
            'message' => $payload['message'],
            'variables' => $payload['variables'],
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
