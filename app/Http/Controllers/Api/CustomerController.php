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
    protected $fields = [
        'name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'birthday' => '1000-01-01',
        'note' => '',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $result = [];

        // 分页
        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('perPage', 10);
        $skip = ($page - 1) * $perPage;

        // 排序
        $column = $request->query('column', 'id');
        $orderBy = $request->query('order', 'desc');


        $customers = Customer::withCount('orders')
            ->skip($skip)
            ->take($perPage)
            ->orderBy($column, $orderBy)
            ->get();

        $result['data'] = $customers;
        $result['total'] = Customer::count();
        $result['current_page'] = $page;
        $result['per_page'] = $perPage;
        $result['last_page'] = ceil($result['total'] / $perPage);

        return $this->success($result);
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
            'name' => 'required',
            'email' => 'required|email|unique:customers'
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        /*$nameArr = preg_split('/[\s,]+/', $request->input('name'));

        $request->request->add([
           'first_name' => $nameArr[0],
           'last_name' => isset($nameArr[1]) ? $nameArr[1] : '',
        ]);*/

        $customer = new Customer();
        foreach (array_keys($this->fields) as $field) {
            $customer->$field = $request->get($field, $this->fields[$field]);
        }

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
            'email' => [
                'required',
                Rule::unique('customers')->ignore($id),
            ]
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $customer = Customer::find($id);
        foreach (array_keys($this->fields) as $field) {
            $customer->$field = $request->filled($field) ? $request->get($field) : $customer->$field;
        }

        $customer->save();

        return $this->message('update_success');
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

        return $this->message('delete success');
    }

    /**
     * @param $key id email name
     * @return mixed
     */
    public function getCustomerByKey($key) {
        $customers = Customer::select('id', 'email', 'name')
            ->where('id', $key)
            ->orWhere('email', 'like', $key . '%')
            ->orWhere('name', 'like', $key . '%')
            ->get();
        return $this->success($customers);
    }
}
