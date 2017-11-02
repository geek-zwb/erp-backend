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
        $fromDate = $request->get('fromDate', date('Y-m-1'));
        $toDate = $request->get('toDate', date('Y-m-d'));
        $purchases = Supplier::find($id)->purchases()
            ->with('products')
            ->where([
                ['created_at', '>=', $fromDate],
                ['created_at', '<=', $toDate],
            ])
            ->get();

        return $this->success($purchases);
    }
}
