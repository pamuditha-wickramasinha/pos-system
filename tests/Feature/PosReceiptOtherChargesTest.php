<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Sale;
use App\Models\StockEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosReceiptOtherChargesTest extends TestCase
{
    use RefreshDatabase;

    private function makeSaleWithOtherCharges(float $otherCharges): Sale
    {
        $user = User::create(['username' => 'cashier', 'password' => 'secret123', 'status' => true]);
        $this->actingAs($user);

        $item = Item::create([
            'item_code' => 'IT0001', 'item_name' => 'Test Item',
            'price' => 100, 'purchase_price' => 60, 'sales_price' => 100, 'final_price' => 100,
            'tax_type' => 'Exclusive', 'status' => true,
        ]);
        StockEntry::create(['entry_date' => now(), 'item_id' => $item->id, 'qty' => 10, 'status' => true]);
        $customer = Customer::create(['customer_name' => 'Walk-in', 'status' => true]);

        $this->post('/pos/pos_save_update', [
            'command' => 'insert',
            'customer_id' => $customer->id,
            'tot_amt' => 100 + $otherCharges,
            'tot_grand' => 100 + $otherCharges,
            'hidden_rowcount' => 1,
            'tr_item_id_0' => $item->id,
            "item_qty_{$item->id}" => 1,
            'sales_price_0' => 100,
            'tr_tax_type_0' => 'Exclusive',
            'tr_tax_value_0' => 0,
            'item_discount_0' => 0,
            'td_data_0_4' => 100,
            'purchase_price_0' => 60,
            'other_charges' => $otherCharges,
            'pay_all' => 'true',
        ])->assertSuccessful();

        return Sale::latest('id')->first();
    }

    public function test_pos_receipt_shows_other_charges(): void
    {
        $sale = $this->makeSaleWithOtherCharges(50);

        $this->assertEquals(50, (float) $sale->other_charges_amt);

        $receipt = $this->get('/pos/print_invoice_pos/'.$sale->id);
        $receipt->assertOk();
        $receipt->assertSee('වෙනත් ගාස්තු(රු):', false);
        $receipt->assertSee('50.00');
    }

    public function test_thermal_receipt_shows_other_charges(): void
    {
        $sale = $this->makeSaleWithOtherCharges(50);
        $sale->loadMissing('items.item');

        // the printer bridge renders this template to a PNG - render it directly
        $html = \Illuminate\Support\Facades\View::make('sales.receipt-thermal', [
            'sale' => $sale,
            'company' => \App\Models\Company::where('id', 1)->where('status', true)->first(),
            'footerText' => '',
            'widthPx' => 576,
            'base' => 17,
            'pad' => 12,
        ])->render();

        $this->assertStringContainsString('වෙනත් ගාස්තු(රු):', $html);
        $this->assertStringContainsString('50.00', $html);
    }

    public function test_pos_receipt_hides_the_line_when_there_is_no_other_charge(): void
    {
        $sale = $this->makeSaleWithOtherCharges(0);

        $receipt = $this->get('/pos/print_invoice_pos/'.$sale->id);
        $receipt->assertOk();
        $receipt->assertDontSee('වෙනත් ගාස්තු', false);
    }
}
