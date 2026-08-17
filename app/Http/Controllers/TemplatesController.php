<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TemplatesController extends Controller
{
    public function smsNew(): View
    {
        $this->authorize('sms_template_add');

        return view('templates.form', ['page_title' => 'SMS Template', 'template' => null]);
    }

    protected function validateTemplate(Request $request)
    {
        return Validator::make($request->all(), [
            'template_name' => 'required',
            'content' => 'required',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('sms_template_add');

        $validator = $this->validateTemplate($request);
        if ($validator->fails()) {
            return response('Please Enter Templates name & Content!');
        }

        SmsTemplate::create([
            'template_name' => $request->input('template_name'),
            'content' => $request->input('content'),
            'status' => true,
        ]);

        session()->flash('success', 'Success!! Record Added Successfully!');

        return response('success');
    }

    public function edit(SmsTemplate $template): View
    {
        $this->authorize('sms_template_edit');

        return view('templates.form', ['page_title' => 'SMS Template', 'template' => $template]);
    }

    public function update(Request $request)
    {
        $this->authorize('sms_template_edit');

        $validator = Validator::make($request->all(), [
            'template_name' => 'required',
            'content' => 'required',
            'q_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response('Please Enter Templates name & Content!');
        }

        SmsTemplate::whereKey($request->input('q_id'))->update([
            'template_name' => $request->input('template_name'),
            'content' => $request->input('content'),
        ]);

        session()->flash('success', 'Success!! Record Updated Successfully!');

        return response('success');
    }

    public function sms(): View
    {
        $this->authorize('sms_template_view');

        return view('templates.list', ['page_title' => 'SMS Templates List']);
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('sms_template_view');

        return DataTables::of(SmsTemplate::query())
            ->addIndexColumn()
            ->addColumn('status_badge', fn (SmsTemplate $t) => DatatableHtml::statusBadge($t->id, $t->status))
            ->addColumn('actions', fn (SmsTemplate $t) => DatatableHtml::actionMenu([
                ['label' => 'Update', 'icon' => 'fa-edit text-blue', 'url' => route('templates.edit', $t), 'can' => $request->user()->can('sms_template_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_template({$t->id})", 'can' => $request->user()->can('sms_template_delete')],
            ]))
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('sms_template_edit');

        SmsTemplate::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('sms_template_delete');

        SmsTemplate::whereKey($request->input('q_id'))->delete();

        return response('success');
    }
}
