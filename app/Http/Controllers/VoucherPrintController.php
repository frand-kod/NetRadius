<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherPrintController extends Controller
{
    public function show(Request $request): View
    {
        $ids = array_filter(explode(',', (string) $request->query('ids', '')));

        $vouchers = Voucher::query()
            ->with('plan')
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        return view('voucher.print', ['vouchers' => $vouchers]);
    }
}
