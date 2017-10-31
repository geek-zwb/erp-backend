<?php
/**
 * Created by PhpStorm.
 * User: geekzwb
 * Date: 2017/10/30
 * Time: 下午9:41
 */

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends ApiController
{
    // TODO 对库存的修改， 事务等

    protected $fields = [
        'name' => '',
        'customer_id' => '',
        'order_code' => '',
        'status' => '待发货',
        'delivery_code' => '',
        'delivery_date' => '1000-01-01',
        'delivery_company' => '',
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


        $orders = Order::with('products')
            ->with('customer')
            ->skip($skip)
            ->take($perPage)
            ->orderBy($column, $orderBy)
            ->get();

        // 产品数量统计以及花费统计
        foreach ($orders as $order) {
            $order->count = 0;
            $order->totalCost = 0;
            $order->productsCount = $order->products->count(); // 几种产品~
            foreach ($order->products as $product) {
                $order->count += $product->pivot->count;
                $order->totalCost += $product->pivot->price * $product->pivot->count;
                $product->count = $product->pivot->count;
                $product->price = $product->pivot->price;
                $product->total = $product->pivot->price * $product->pivot->count;
            }
        }

        $result['data'] = $orders;
        $result['total'] = Order::count();
        $result['current_page'] = $page;
        $result['per_page'] = $perPage;
        $result['last_page'] = ceil($result['total'] / $perPage);

        return $this->success($result);
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
            'name' => 'required|filled',
            'customer_email' => 'required|filled|exists:customers,email',
            'delivery_date' => 'filled',
            'products' => 'required|array'
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $customer_id = Customer::where('email', $request->input('customer_email'))->first()->id;
        $request->request->add([
            'customer_id' => $customer_id
        ]);

        $order = new Order();
        foreach (array_keys($this->fields) as $field) {
            $order->$field = $request->get($field, $this->fields[$field]);
        }

        $order->save();

        if ($request->has('products')) {
            // 发货的产品数量以及单价等等
            $products = $request->get('products');
            foreach ($products as $product) {
                $productCollec = Product::where('name', $product['name'])->first();
                $productId = $productCollec->id;
                $order->products()->attach($productId, [
                    'count' => $product['count'],
                    'price' => $product['price'],
                ]);
            }
        }

        return $this->message('add success');
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
            'name' => 'required|filled',
            'customer_email' => 'required|filled|exists:customers,email',
            'delivery_date' => 'filled',
            'products' => 'required|array'
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $customer_id = Customer::where('email', $request->input('customer_email'))->first()->id;
        $request->request->add([
           'customer_id' => $customer_id
        ]);

        $order = Order::find($id);
        foreach (array_keys($this->fields) as $field) {
            $order->$field = $request->filled($field) ? $request->get($field) : $order->$field;
        }

        $order->save();
        $products = $request->get('products');
        $syncProducts = [];
        foreach ($products as $product) {
            $productCollec = Product::where('name', $product['name'])->first();
            $productId = $productCollec->id;
            $syncProducts[$productId] = ['count' => $product['count'], 'price' => $product['price'],];
        }
        $order->products()->sync($syncProducts);

        return $this->message('update success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        $order->products()->detach();

        $order->delete();

        return $this->message('delete success');
    }
}
