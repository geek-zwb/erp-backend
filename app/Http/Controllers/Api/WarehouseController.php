<?php

namespace App\Http\Controllers\Api;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WarehouseController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $warehouses = Warehouse::all();
        return $this->success($warehouses);
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
            'name' => 'required|unique:warehouses',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $warehouse = new Warehouse();
        $warehouse->name = $request->get('name');
        $warehouse->note = $request->get('note', '');
        $warehouse->status = $request->get('status', true);

        $warehouse->save();

        return $this->success($warehouse);
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
                Rule::unique('warehouses')->ignore($id),
            ]
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $warehouse = Warehouse::find($id);
        $warehouse->name = $request->get('name');
        $warehouse->note = $request->filled('note') ? $request->get('note') : $warehouse->note;
        $warehouse->status = $request->filled('status') ? $request->get('status') : $warehouse->status;

        $warehouse->save();

        return $this->success($warehouse);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $warehouse = Warehouse::find($id);

        if($warehouse->products->isNotEmpty()) {
            return $this->failed('请先删除或转移仓库 '.$warehouse->name.' 里的所有商品');
        }

        $warehouse->delete();

        return $this->message('delete success');
    }
}
