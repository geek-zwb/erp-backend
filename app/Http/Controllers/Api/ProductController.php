<?php
/**
 * Created by PhpStorm.
 * User: geekzwb
 * Date: 2017/10/30
 * Time: 上午11:08
 */

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends ApiController
{
    protected $fields = [
        'name' => '',
        'sku' => '',
        'unit_id' => '',
        'type_id' => '0',
        'weight' => 0,
        'description' => '',
        'picture' => '',
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
        $order = $request->query('order', 'desc');
        $fromDate = strtotime($request->query('fromDate', date('Y-01-01')));
        $toDate = $request->query('toDate', date('Y-m-d'));
        $toDate = strtotime("$toDate +1 day");

        $products = Product::with(['orders', 'purchases', 'warehouses'])
            ->skip($skip)
            ->take($perPage)
            ->orderBy($column, $order)
            ->get();

        // 产品数量统计以及收支、库存统计
        foreach ($products as $product) {
            $product->inventory = 0;    // 总库存
            $product->inQty = 0;    // 采购进的数量
            $product->totalCost = 0;    // 采购总花费
            $product->income = 0;   // 总收入
            $product->outQty = 0;    // 已卖出的数量
            $orderCount = 0;    // 改产品售出订单数

            // 售出统计
            foreach ($product->orders as $order) {
                $created_at = strtotime($order->created_at);
                if($created_at < $fromDate || $created_at > $toDate) continue;
                $orderCount += 1;
                $product->outQty += $order->pivot->count;
                $product->income += $order->pivot->price * $order->pivot->count;
            }
            $product->orderCount = $orderCount;

            // 采购统计
            foreach ($product->purchases as $purchase) {
                $created_at = strtotime($purchase->created_at);
                if($created_at < $fromDate || $created_at > $toDate) continue;
                $product->inQty += $purchase->pivot->count;
                $product->totalCost += $purchase->pivot->price * $purchase->pivot->count;
            }

            // 库存统计
            foreach ($product->warehouses as $warehouse) {
                $product->inventory += $warehouse->pivot->inventory;
                $warehouse->inventory += $warehouse->pivot->inventory;
            }

            //unset($product->purchases);
            //unset($product->orders);
        }

        $result['data'] = $products;
        $result['total'] = Product::count();
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
            'name' => 'required',
            'sku' => 'required|unique:products',
            'unit_id' => 'required',
            'type_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $product = new Product();
        foreach (array_keys($this->fields) as $field) {
            $product->$field = $request->get($field, $this->fields[$field]);
        }

        $product->save();

        return $this->success($product);
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
            'name' => 'required',
            'sku' => [
                'required',
                Rule::unique('products')->ignore($id),
            ],
            'unit_id' => 'required',
            'type_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $product = Product::find($id);
        foreach (array_keys($this->fields) as $field) {
            $product->$field = $request->filled($field) ? $request->get($field) : $product->$field;
        }

        $product->save();

        return $this->message('update_success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if ($product->purchases->isNotEmpty()) {
            return $this->failed('请先删除相关的采购单');
        }
        if ($product->orders->isNotEmpty()) {
            return $this->failed('请先删除相关的订单（发货单） ');
        }

        $product->purchases()->detach();
        $product->orders()->detach();

        $product->delete();

        return $this->message('delete success');
    }

    /**
     * 根据关键字查询产品
     * @param $key  id or sku or name
     * @return mixed
     */
    public function getProductByKey($key)
    {
        $products = Product::select('id', 'sku', 'name')
            ->where('id', $key)
            ->orWhere('sku', 'like', $key . '%')
            ->orWhere('name', 'like', $key . '%')
            ->get();
        return $this->success($products);
    }
}
