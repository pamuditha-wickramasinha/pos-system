<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteContext
{
    protected ?SiteSetting $settings = null;

    public function settings(): SiteSetting
    {
        if ($this->settings === null) {
            $this->settings = Cache::remember('site_settings', 60, function () {
                return SiteSetting::with('currency')->find(1) ?? new SiteSetting([
                    'site_name' => config('app.name'),
                    'date_format' => 'dd-mm-yyyy',
                    'time_format' => 12,
                    'currency_placement' => 'Left',
                    'round_off' => false,
                    'disable_tax' => false,
                ]);
            });
        }

        return $this->settings;
    }

    public function siteTitle(): string
    {
        return $this->settings()->site_name ?? config('app.name');
    }

    public function themeLink(): string
    {
        return asset('theme').'/';
    }

    public function currencySymbol(): string
    {
        return optional($this->settings()->currency)->currency ?? '';
    }

    public function currencyCode(): string
    {
        return optional($this->settings()->currency)->currency_code ?? '';
    }

    public function currencyPlacement(): string
    {
        return $this->settings()->currency_placement ?? 'Left';
    }

    public function dateFormat(): string
    {
        return $this->settings()->date_format ?? 'dd-mm-yyyy';
    }

    public function timeFormat(): int
    {
        return (int) ($this->settings()->time_format ?? 12);
    }

    public function isTaxDisabled(): bool
    {
        return (bool) $this->settings()->disable_tax;
    }

    public function isRoundOffEnabled(): bool
    {
        return (bool) $this->settings()->round_off;
    }

    public function invoiceFormatId(): int
    {
        return (int) ($this->settings()->sales_invoice_format_id ?? 1);
    }

    public function showChangeReturn(): bool
    {
        return (bool) $this->settings()->change_return;
    }

    public function showUpiCode(): bool
    {
        return (bool) $this->settings()->show_upi_code;
    }

    public function money(mixed $value = '', bool $withComma = false): string
    {
        $value = trim((string) $value);

        if ($value !== '' && is_numeric($value)) {
            $value = $withComma ? number_format((float) $value, 2) : number_format((float) $value, 2, '.', '');
        }

        $symbol = $this->currencySymbol();

        if ($this->currencyPlacement() === 'Left') {
            return $value !== '' ? trim($symbol.' '.$value) : $symbol;
        }

        return $value !== '' ? trim($value.' '.$symbol) : $value.$symbol;
    }

    public function forget(): void
    {
        Cache::forget('site_settings');
        $this->settings = null;
    }
}
