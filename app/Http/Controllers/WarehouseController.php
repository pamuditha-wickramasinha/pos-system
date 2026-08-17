<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        return view('warehouse.list', [
            'page_title' => 'Warehouse List',
            'warehouses' => Warehouse::orderBy('id')->get(),
        ]);
    }

    public function add(): View
    {
        return view('warehouse.form', ['page_title' => 'Warehouse']);
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouse.form', [
            'page_title' => 'Warehouse',
            'q_id' => $warehouse->id,
            'warehouse_name' => $warehouse->warehouse_name,
            'mobile' => $warehouse->mobile,
            'email' => $warehouse->email,
        ]);
    }

    public function saveOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_name' => 'required',
            'mobile' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        $id = $request->input('q_id');
        $isUpdate = $request->input('command') === 'update';

        $nameQuery = Warehouse::where('warehouse_name', $request->input('warehouse_name'));
        $mobileQuery = Warehouse::where('mobile', $request->input('mobile'));
        $emailQuery = Warehouse::where('email', $request->input('email'));

        if ($isUpdate) {
            $nameQuery->where('id', '!=', $id);
            $mobileQuery->where('id', '!=', $id);
            $emailQuery->where('id', '!=', $id);
        }

        if ($nameQuery->exists()) {
            return response('This Warehouse Name Already Exist.');
        }
        if ($mobileQuery->exists()) {
            return response('This Moble Number already exist.');
        }
        if ($emailQuery->exists()) {
            return response('This Email ID already exist.');
        }

        $data = [
            'warehouse_name' => $request->input('warehouse_name'),
            'mobile' => $request->input('mobile'),
            'email' => $request->input('email'),
        ];

        if ($isUpdate) {
            Warehouse::whereKey($id)->update($data);

            session()->flash('success', 'Success!! Warehouse Updated Succssfully!!');

            return response('success');
        }

        $data['status'] = true;
        Warehouse::create($data);

        session()->flash('success', 'Success!! New Warehouse Created Succssfully!!');

        return response('success');
    }

    public function statusUpdate(Request $request)
    {
        Warehouse::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        Warehouse::whereKey($request->input('id'))->delete();

        return response('success');
    }
}
