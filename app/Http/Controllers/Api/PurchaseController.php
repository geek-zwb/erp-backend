<?php
/**
 * Created by PhpStorm.
 * User: geekzwb
 * Date: 2017/10/29
 * Time: 下午2:29
 */

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchaseController extends ApiController
{
    // TODO 对库存的修改， 事务等

    protected $fields = [
        'name' => '',
        'supplier_id' => '',
        'delivery_code' => '',
        'invoice_date' => '1000-01-01',
        'invoice_code' => '',
        'invoice_amount' => '',
        'arrears' => '0',
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


        $purchases = Purchase::with('products')
            ->skip($skip)
            ->take($perPage)
            ->orderBy($column, $order)
            ->get();

        // 产品数量统计以及花费统计
        foreach ($purchases as $purchase) {
            $purchase->count = 0;
            $purchase->totalCost = 0;
            foreach ($purchase->products as $product) {
                $purchase->count += $product->pivot->count;
                $purchase->totalCost += $product->pivot->price * $product->pivot->count;
                $product->count = $product->pivot->count;
                $product->price = $product->pivot->price;
                $product->total = $product->pivot->price * $product->pivot->count;
            }
        }

        $result['data'] = $purchases;
        $result['total'] = Purchase::count();
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
            'supplier_id' => 'required|filled|exists:suppliers,id',
            'invoice_date' => 'filled',
            'products' => 'required|array'
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $purchase = new Purchase();
        foreach (array_keys($this->fields) as $field) {
            $purchase->$field = $request->get($field, $this->fields[$field]);
        }

        $purchase->save();

        if ($request->has('products')) {
            // 订购的产品数量以及单价等等
            $products = $request->get('products');
            foreach ($products as $product) {
                $productCollec = Product::where('name', $product['name'])->first();
                $productId = $productCollec->id;
                $purchase->products()->attach($productId, [
                    'count' => $product['count'],
                    'price' => $product['price'],
                ]);
            }
        }

        return $this->success($purchase);
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
            'supplier_id' => 'required|filled|exists:suppliers,id',
            'invoice_date' => 'filled',
            'products' => 'required|array'
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $purchase = Purchase::find($id);
        foreach (array_keys($this->fields) as $field) {
            $purchase->$field = $request->filled($field) ? $request->get($field) : $purchase->$field;
        }

        $purchase->save();
        $products = $request->get('products');
        $syncProducts = [];
        foreach ($products as $product) {
            $productCollec = Product::where('name', $product['name'])->first();
            $productId = $productCollec->id;
            $syncProducts[$productId] = ['count' => $product['count'], 'price' => $product['price'],];
        }
        $purchase->products()->sync($syncProducts);

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
        $purchase = Purchase::find($id);

        $purchase->products()->detach();

        $purchase->delete();

        return $this->message('delete success');
    }
}
