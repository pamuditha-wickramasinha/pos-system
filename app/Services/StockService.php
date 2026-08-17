<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StockService
{
    public function recalculate(int $itemId): bool
    {
        $stock = (float) DB::table('stock_entries')->where('item_id', $itemId)->sum('qty');

        if (Schema::hasTable('purchase_items')) {
            $stock += (float) DB::table('purchase_items')
                ->where('item_id', $itemId)
                ->where('purchase_status', 'Received')
                ->sum('purchase_qty');
        }

        if (Schema::hasTable('sales_items')) {
            $stock -= (float) DB::table('sales_items')
                ->where('item_id', $itemId)
                ->where('sales_status', 'Final')
                ->sum('sales_qty');
        }

        if (Schema::hasTable('sales_items_returns')) {
            $stock += (float) DB::table('sales_items_returns')->where('item_id', $itemId)->sum('return_qty');
        }

        if (Schema::hasTable('purchase_items_returns')) {
            $stock -= (float) DB::table('purchase_items_returns')->where('item_id', $itemId)->sum('return_qty');
        }

        return Item::whereKey($itemId)->update(['stock' => $stock]) > 0;
    }
}
