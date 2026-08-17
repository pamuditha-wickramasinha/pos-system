<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(): View
    {
        $this->authorize('company_edit');

        $company = Company::firstOrFail();

        return view('company.profile', [
            'page_title' => 'Company Profile',
            'q_id' => $company->id,
            'company_name' => $company->company_name,
            'mobile' => $company->mobile,
            'phone' => $company->phone,
            'email' => $company->email,
            'country' => $company->country,
            'state' => $company->state,
            'city' => $company->city,
            'postcode' => $company->postcode,
            'address' => $company->address,
            'gstin' => $company->gst_no,
            'vat' => $company->vat_no,
            'pan' => $company->pan_no,
            'website' => $company->website,
            'bank_details' => $company->bank_details,
            'upi_id' => $company->upi_id,
            'company_logo' => $company->logo,
            'upi_code' => $company->upi_code,
            'signature' => $company->signature,
            'show_signature' => $company->show_signature,
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'mobile' => 'required',
            'email' => 'required',
            'address' => 'required',
            'city' => 'required',
        ]);

        if ($validator->fails()) {
            return response('Please Enter Compulsary(* marked) fields!');
        }

        $data = [
            'show_signature' => $request->boolean('show_signature'),
            'company_name' => $request->input('company_name'),
            'mobile' => $request->input('mobile'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'country' => $request->input('country'),
            'state' => $request->input('state'),
            'city' => $request->input('city'),
            'postcode' => $request->input('postcode'),
            'address' => $request->input('address'),
            'gst_no' => $request->input('gstin'),
            'vat_no' => $request->input('vat'),
            'website' => $request->input('website'),
            'pan_no' => $request->input('pan'),
            'bank_details' => $request->input('bank_details'),
            'upi_id' => $request->input('upi_id'),
        ];

        if ($request->hasFile('company_logo')) {
            $data['logo'] = $request->file('company_logo')->store('company', 'public');
        }
        if ($request->hasFile('upi_code')) {
            $data['upi_code'] = $request->file('upi_code')->store('upi', 'public');
        }
        if ($request->hasFile('signature')) {
            $data['signature'] = $request->file('signature')->store('signature', 'public');
        }

        Company::whereKey($request->input('q_id'))->update($data);

        return response('success');
    }
}
