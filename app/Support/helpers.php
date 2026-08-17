<?php

use App\Support\SiteContext;

if (! function_exists('site')) {
    function site(): SiteContext
    {
        return app(SiteContext::class);
    }
}

if (! function_exists('currency')) {
    function currency(mixed $value = '', bool $withComma = false): string
    {
        return site()->money($value, $withComma);
    }
}

if (! function_exists('app_number_format')) {
    function app_number_format(mixed $value = ''): string
    {
        return ($value === '' || $value === null) ? '' : number_format((float) $value, 2);
    }
}

if (! function_exists('show_date')) {
    function show_date(mixed $date = ''): string
    {
        if (empty($date)) {
            return '';
        }

        return match (site()->dateFormat()) {
            'dd/mm/yyyy' => date('d/m/Y', strtotime(str_replace('/', '-', $date))),
            'mm/dd/yyyy' => date('m/d/Y', strtotime($date)),
            default => date('d-m-Y', strtotime($date)),
        };
    }
}

if (! function_exists('show_time')) {
    function show_time(mixed $time = ''): string
    {
        if (empty($time)) {
            return '';
        }

        return site()->timeFormat() === 24 ? date('h:i', strtotime($time)) : date('h:i a', strtotime($time));
    }
}

if (! function_exists('is_tax_disabled')) {
    function is_tax_disabled(): bool
    {
        return site()->isTaxDisabled();
    }
}

if (! function_exists('tax_disable_class')) {
    function tax_disable_class(): string
    {
        return is_tax_disabled() ? 'hide' : 'block';
    }
}

if (! function_exists('is_enabled_round_off')) {
    function is_enabled_round_off(): bool
    {
        return site()->isRoundOffEnabled();
    }
}

if (! function_exists('get_invoice_format_id')) {
    function get_invoice_format_id(): int
    {
        return site()->invoiceFormatId();
    }
}

if (! function_exists('is_sms_enabled')) {
    function is_sms_enabled(): bool
    {
        return (bool) \App\Models\Company::query()->value('sms_status');
    }
}

if (! function_exists('change_return_status')) {
    function change_return_status(): bool
    {
        return (bool) \App\Models\SiteSetting::query()->value('change_return');
    }
}

if (! function_exists('get_change_return_amount')) {
    function get_change_return_amount(int $salesId): float
    {
        return (float) \App\Models\SalePayment::where('sales_id', $salesId)->sum('change_return');
    }
}

if (! function_exists('calculate_inclusive')) {
    function calculate_inclusive(mixed $amount, mixed $tax): string
    {
        $total = ($amount / (($tax / 100) + 1) / 10);

        return number_format($total, 2, '.', '');
    }
}

if (! function_exists('calculate_exclusive')) {
    function calculate_exclusive(mixed $amount, mixed $tax): string
    {
        $total = ($amount * $tax) / 100;

        return number_format($total, 2, '.', '');
    }
}

if (! function_exists('demo_app')) {
    function demo_app(): bool
    {
        return false;
    }
}

if (! function_exists('app_version')) {
    function app_version(): string
    {
        return '2.4';
    }
}

if (! function_exists('return_item_image_thumb')) {
    function return_item_image_thumb(string $path = ''): string
    {
        return str_replace('.', '_thumb.', $path);
    }
}
