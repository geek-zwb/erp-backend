<?php
/**
 * Created by PhpStorm.
 * User: geekzwb
 * Date: 2017/10/28
 * Time: 上午11:46
 */

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /*$customers = Customer::with(['purchases' => function ($query) {
            $query->with(['products' => function ($query1) {
                $query1->select('products.id', 'products.sku', 'products.name');
            }]);
        }])->get();*/
        $customers = Customer::all();
        return $this->success($customers);
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
            'name' => 'required|unique:customers',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $customer = new Customer();
        $customer->name = $request->get('name');
        $customer->note = $request->get('note');

        $customer->save();

        return $this->success($customer);
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
                Rule::unique('customers')->ignore($id),
            ]
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $customer = Customer::find($id);
        $customer->name = $request->get('name');
        $customer->note = $request->get('note');

        $customer->save();

        return $this->success($customer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if($customer->orders->isNotEmpty()) {
            return $this->failed('请先删除该客户的所有订单');
        }

        $customer->delete();

        $this->message('delete success');
    }
}
