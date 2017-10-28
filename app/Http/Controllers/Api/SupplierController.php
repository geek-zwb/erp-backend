<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SupplierController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /*$suppliers = Supplier::with(['purchases' => function ($query) {
            $query->with(['products' => function ($query1) {
                $query1->select('products.id', 'products.sku', 'products.name');
            }]);
        }])->get();*/
        $suppliers = Supplier::all();
        return $this->success($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'reqired|unique:suppliers',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $supplier = new Supplier();
        $supplier->name = $request->get('name');
        $supplier->phone = $request->get('phone');
        $supplier->address = $request->get('address');
        $supplier->note = $request->get('note');

        $supplier->save();

        return $this->success($supplier);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('suppliers')->ignore($id),
            ],
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $supplier = Supplier::find($id);
        $supplier->name = $request->get('name');
        $supplier->phone = $request->get('phone');
        $supplier->address = $request->get('address');
        $supplier->note = $request->get('note');

        $supplier->save();

        return $this->success($supplier);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);

        if($supplier->purchase) {
            return $this->failed('请先删除'.$supplier->name.'供应商的所有订货单');
        }

        $supplier->delete();

        $this->message('delete success');
    }
}
