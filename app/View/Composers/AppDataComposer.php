<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppDataComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        $view->with([
            'theme_link' => site()->themeLink(),
            'base_url' => url('/').'/',
            'SITE_TITLE' => site()->siteTitle(),
            'CURRENCY' => site()->currencySymbol(),
            'CURRENCY_PLACE' => site()->currencyPlacement(),
            'CURRENCY_CODE' => site()->currencyCode(),
            'CUR_DATE' => now()->format('Y-m-d'),
            'VIEW_DATE' => site()->dateFormat(),
            'CUR_USERNAME' => $user?->username,
            'CUR_USERID' => $user?->id,
            'page_title' => $view->getData()['page_title'] ?? site()->siteTitle(),
        ]);
    }
}
