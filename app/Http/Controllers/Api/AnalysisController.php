<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Illuminate\Http\Request;

class AnalysisController extends ApiController
{
    /**
     * 供应商 供货统计分析 默认为当月
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function supplierAna(Request $request,$id) {
        $fromDate = $request->get('fromDate', date('Y-01-01'));
        $toDate = $request->get('toDate', date('Y-m-d'));
        // 需要同时包括 fromDate 和 toDate 这两天 ？？？
        $toDate = date('Y-m-d', strtotime("$toDate +1 day"));
        $purchases = Supplier::find($id)->purchases()
            ->with('products')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        foreach ($purchases as $purchase) {
            $purchase->purchaseCost = 0;
            foreach ($purchase->products as $product) {
                $purchase->purchaseCost += $product->pivot->count * $product->pivot->price;
            }
        }

        return $this->success($purchases);
    }
}
