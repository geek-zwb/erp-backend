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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        'delivery_amount' => 0,
        'note' => '',
    ];

    // orderStatus 的说明
    // [0 => '待发货'， 1 => '已经发货'， 2 => '有退货、商品损坏、不可退供应商、丢弃', 3 => '有退货，可退供应商或可继续销售', 4 => '已重发']
    // [库存不变,库存减,库存不变,库存加,库存减]
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
                $product->returns_count = $product->pivot->returns_count;
                $product->price = $product->pivot->price;
                $product->status = $product->pivot->status; // 订单状态 待发货、已发货。。。
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
     * @param Request $request
     * @return mixed
     * @throws \Error
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

        DB::beginTransaction();

        try {
            $order->save();

            if ($request->has('products')) {
                // 发货的产品数量以及单价等等
                $products = $request->get('products');
                foreach ($products as $product) {
                    $returns_count = $product['returns_count'] ? : 0;
                    // 把待发货状态去除， 暂时不考虑
                    if((int)$product['status'] != 1) throw new \Error('should be 1');
                    $productCollec = Product::where('name', $product['name'])->first();
                    $productId = $productCollec->id;
                    $order->products()->attach($productId, [
                        'count' => $product['count'],
                        'returns_count' => $returns_count,
                        'price' => $product['price'],
                        'status' => $product['status'],
                    ]);

                    // 亚马逊 库存减少 warehouse_id == 2
                    $inventoryChanged = (int)$product['status'] == 1 ? $product['count'] : 0;
                    $warehouseHome = $productCollec->warehouses()->where('warehouse_id', 2)->get();
                    if ($warehouseHome->isEmpty()) {
                        $productCollec->warehouses()->attach(2, ['inventory' => -$inventoryChanged]);
                    } else {
                        // 更改库存
                        DB::table('product_warehouse')
                            ->where('product_id', '=', $productId)
                            ->where('warehouse_id', '=', 2)
                            ->decrement('inventory', $inventoryChanged);
                    }
                }
            }
        } catch (ValidationException $e) {
            DB::rollback();
            return $this->failed('Add failed');
        }

        DB::commit();

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

        DB::beginTransaction();

        try {
            $order->save();
            $products = $request->get('products');
            $syncProducts = [];
            foreach ($products as $product) {
                $productCollec = Product::where('name', $product['name'])->first();
                $productId = $productCollec->id;

                // 计算 count 改变
                $orderProduct = DB::table('order_product')
                    ->select('id', 'count', 'returns_count')
                    ->where('product_id', $productId)
                    ->where('order_id', $id)
                    ->first();

                $returns_count = isset($product['returns_count']) ? $product['returns_count'] : $orderProduct->returns_count;

                $syncProducts[$productId] = [
                    'count' => $product['count'],
                    'returns_count' => $returns_count,
                    'price' => $product['price'],
                    'status' => $product['status'],
                ];

                // 库存 当发生退货且损坏时，不改变库存
                if ($product['status'] !== 2) {
                    $oldCount = $orderProduct->count;
                    $updateCount = $product['count'] - $oldCount;

                    if ($updateCount !== 0) {
                        DB::table('product_warehouse')
                            ->where('product_id', '=', $productId)
                            ->where('warehouse_id', '=', 2)
                            ->decrement('inventory', $updateCount);
                    }
                }
            }
            $order->products()->sync($syncProducts);
        } catch (ValidationException $e) {
            DB::rollback();
            return $this->failed('update failed');
        }

        DB::commit();

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
