<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SmsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SmsController extends Controller
{
    public function index(): View
    {
        $this->authorize('send_sms');

        return view('sms.index', ['page_title' => 'Send SMS']);
    }

    public function sendMessage(Request $request)
    {
        $this->authorize('send_sms');

        return response($this->sendSms($request->input('mobile'), $request->input('message')));
    }

    protected function sendSms(string $mobile, string $message): string
    {
        if (! is_sms_enabled()) {
            return "Sorry! Can't Send.Please Enable SMS";
        }

        $rows = SmsApi::all();
        if ($rows->isEmpty()) {
            return 'API Not Available';
        }

        $params = [];
        foreach ($rows as $row) {
            if ($row->info === 'message') {
                $params[$row->key] = $message;
            } elseif ($row->info === 'mobile') {
                $params[$row->key] = $mobile;
            } else {
                $params[$row->key] = $row->key_value;
            }
        }
        $params['unicode'] = '1';

        $url = $params['weblink'] ?? '';
        unset($params['weblink']);

        try {
            $response = Http::withoutVerifying()->timeout(80)->get($url, $params);

            return $response->successful() ? 'success' : 'failed';
        } catch (\Throwable $e) {
            report($e);

            return 'failed';
        }
    }

    public function api(): View
    {
        $this->authorize('sms_api_view');

        return view('sms.api', [
            'page_title' => 'SMS API',
            'rows' => SmsApi::orderBy('id')->get(),
            'smsStatus' => (bool) Company::query()->value('sms_status'),
        ]);
    }

    public function apiUpdate(Request $request)
    {
        $this->authorize('sms_api_edit');

        $rowCount = (int) $request->input('hidden_rowcount', 0);

        SmsApi::query()->delete();

        for ($i = 1; $i <= $rowCount; $i++) {
            if ($request->has("info_{$i}")) {
                SmsApi::create([
                    'info' => $request->input("info_{$i}"),
                    'key' => $request->input("key_{$i}"),
                    'key_value' => $request->input("key_val_{$i}"),
                ]);
            }
        }

        Company::query()->update(['sms_status' => $request->input('sms_status')]);

        session()->flash('success', 'Record Successfully Saved!!');

        return response('success');
    }
}
