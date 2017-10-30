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


        $products = Product::with('products')
            ->skip($skip)
            ->take($perPage)
            ->orderBy($column, $order)
            ->get();

        // 产品数量统计以及花费统计
        foreach ($products as $product) {
            $product->count = 0;
            $product->totalCost = 0;
            foreach ($product->products as $product) {
                $product->count += $product->pivot->count;
                $product->totalCost += $product->pivot->price * $product->pivot->count;
                $product->count = $product->pivot->count;
                $product->price = $product->pivot->price;
                $product->total = $product->pivot->price * $product->pivot->count;
            }
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
            'name' => 'required|unique:products',
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $product = new Product();
        $product->name = $request->get('name');
        $product->note = $request->get('note', '');
        $product->status = $request->get('status', true);

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
            'name' => [
                'required',
                Rule::unique('products')->ignore($id),
            ]
        ]);

        if ($validator->fails()) {
            return $this->failed($validator->errors());
        }

        $product = Product::find($id);
        $product->name = $request->get('name');
        $product->note = $request->filled('note') ? $request->get('note') : $product->note;
        $product->status = $request->filled('status') ? $request->get('status') : $product->status;

        $product->save();

        return $this->success($product);
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

        if ($product->products->isNotEmpty()) {
            return $this->failed('请先删除或转移仓库 ' . $product->name . ' 里的所有商品');
        }

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
