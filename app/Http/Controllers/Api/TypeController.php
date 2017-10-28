<?php
/**
 * Created by PhpStorm.
 * User: geekzwb
 * Date: 2017/10/28
 * Time: 上午11:26
 */

namespace App\Http\Controllers\Api;

use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TypeController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = Type::all();
        return $this->success($types);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:types',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $type = new Type();
        $type->name = $request->get('name');
        $type->note = $request->get('note', '');

        $type->save();

        return $this->success($type);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('types')->ignore($id),
            ]
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $type = Type::find($id);
        $type->name = $request->get('name');
        $type->note = $request->filled('note') ? $request->get('note') : $type->note;

        $type->save();

        return $this->success($type);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $type = Type::find($id);

        if ($type->products->isNotEmpty()) {
            return $this->failed('请先删除该类别下面商品， 或更改相关商品的所属类别');
        }

        $type->delete();

        $this->message('delete success');
    }
}
