<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Currency;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    public function index(): View
    {
        $this->authorize('site_edit');

        return view('site.settings', [
            'page_title' => 'Site Settings',
            'settings' => SiteSetting::firstOrFail(),
            'company' => Company::firstOrFail(),
            'currencies' => Currency::where('status', true)->get(),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('site_edit');

        $validator = Validator::make($request->all(), ['site_name' => 'required']);
        if ($validator->fails()) {
            return response('Please Enter Compulsary(* marked) fields!');
        }

        $settings = SiteSetting::firstOrFail();

        $data = [
            'site_name' => $request->input('site_name'),
            'currency_id' => $request->input('currency') ?: null,
            'currency_placement' => $request->input('currency_placement'),
            'timezone' => $request->input('timezone'),
            'date_format' => $request->input('date_format'),
            'time_format' => $request->input('time_format'),
            'sales_discount' => $request->input('sales_discount') ?: 0,
            'change_return' => $request->boolean('change_return'),
            'show_upi_code' => $request->boolean('show_upi_code'),
            'round_off' => $request->boolean('round_off'),
            'disable_tax' => $request->boolean('disable_tax'),
            'number_to_words' => $request->input('number_to_words'),
            'sales_invoice_format_id' => $request->input('sales_invoice_format_id'),
            'sales_invoice_footer_text' => $request->input('sales_invoice_footer_text'),
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logo', 'public');
        }

        $settings->update($data);

        Company::whereKey(1)->update([
            'category_init' => $request->input('category_init'),
            'item_init' => $request->input('item_init'),
            'supplier_init' => $request->input('supplier_init'),
            'purchase_init' => $request->input('purchase_init'),
            'purchase_return_init' => $request->input('purchase_return_init'),
            'customer_init' => $request->input('customer_init'),
            'sales_init' => $request->input('sales_init'),
            'sales_return_init' => $request->input('sales_return_init'),
            'expense_init' => $request->input('expense_init'),
            'sales_terms_and_conditions' => $request->input('sales_terms_and_conditions'),
        ]);

        Cache::forget('site_settings');

        session()->flash('success', 'Success!! Settings Updated Successfully!');

        return response('success');
    }
}
