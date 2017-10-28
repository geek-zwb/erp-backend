<?php
/**
 * Created by PhpStorm.
 * User: geekzwb
 * Date: 2017/10/28
 * Time: 上午11:31
 */

namespace App\Http\Controllers\Api;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UnitController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $units = Unit::all();
        return $this->success($units);
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
            'name' => 'reqired|unique:units',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $unit = new Unit();
        $unit->name = $request->get('name');
        $unit->note = $request->get('note');

        $unit->save();

        return $this->success($unit);
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
                Rule::unique('units')->ignore($id),
            ]
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $unit = Unit::find($id);
        $unit->name = $request->get('name');
        $unit->note = $request->get('note');

        $unit->save();

        return $this->success($unit);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $unit = Unit::find($id);

        if($unit->products) {
            return $this->failed('请先删除所有以 '.$unit->name.' 为单位的商品， 或更改相关商品的计数单位');
        }

        $unit->delete();

        $this->message('delete success');
    }
}
