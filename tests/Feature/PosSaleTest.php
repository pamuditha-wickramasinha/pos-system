<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Sale;
use App\Models\StockEntry;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSaleTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsUser(): User
    {
        $user = User::create([
            'username' => 'cashier',
            'password' => 'secret123',
            'status' => true,
        ]);

        $this->actingAs($user);

        return $user;
    }

    protected function makeItem(float $stock = 10): Item
    {
        $item = Item::create([
            'item_code' => 'IT0001',
            'item_name' => 'Test Item',
            'price' => 100,
            'purchase_price' => 60,
            'sales_price' => 100,
            'final_price' => 100,
            'tax_type' => 'Exclusive',
            'status' => true,
        ]);

        StockEntry::create([
            'entry_date' => now(),
            'item_id' => $item->id,
            'qty' => $stock,
            'status' => true,
        ]);

        return $item;
    }

    public function test_pos_sale_creates_sale_items_and_reduces_stock(): void
    {
        $this->actingAsUser();
        $item = $this->makeItem(stock: 10);
        $customer = Customer::create(['customer_name' => 'Walk-in', 'status' => true]);

        $response = $this->post('/pos/pos_save_update', [
            'command' => 'insert',
            'customer_id' => $customer->id,
            'tot_amt' => 300,
            'tot_grand' => 300,
            'hidden_rowcount' => 1,
            'tr_item_id_0' => $item->id,
            "item_qty_{$item->id}" => 3,
            'sales_price_0' => 100,
            'tr_tax_type_0' => 'Exclusive',
            'tr_tax_value_0' => 0,
            'item_discount_0' => 0,
            'td_data_0_4' => 300,
            'purchase_price_0' => 60,
            'pay_all' => 'true',
        ]);

        $response->assertSuccessful();
        $this->assertStringStartsWith('success', $response->getContent());

        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertSame(1, $sale->items()->count());
        $this->assertEquals(300, (float) $sale->grand_total);

        $item->refresh();
        $this->assertEquals(7, (float) $item->stock);
    }

    public function test_pos_sale_payment_status_is_paid_when_fully_paid(): void
    {
        $this->actingAsUser();
        $item = $this->makeItem(stock: 10);
        $customer = Customer::create(['customer_name' => 'Walk-in', 'status' => true]);

        $this->post('/pos/pos_save_update', [
            'command' => 'insert',
            'customer_id' => $customer->id,
            'tot_amt' => 200,
            'tot_grand' => 200,
            'hidden_rowcount' => 1,
            'tr_item_id_0' => $item->id,
            "item_qty_{$item->id}" => 2,
            'sales_price_0' => 100,
            'tr_tax_type_0' => 'Exclusive',
            'tr_tax_value_0' => 0,
            'item_discount_0' => 0,
            'td_data_0_4' => 200,
            'purchase_price_0' => 60,
            'pay_all' => 'true',
        ]);

        $sale = Sale::latest('id')->first();
        $this->assertSame('Paid', $sale->payment_status);
        $this->assertEquals(200, (float) $sale->paid_amount);
    }

    public function test_pos_sale_payment_status_is_partial_when_underpaid(): void
    {
        $this->actingAsUser();
        $item = $this->makeItem(stock: 10);
        $customer = Customer::create(['customer_name' => 'Walk-in', 'status' => true]);

        $this->post('/pos/pos_save_update', [
            'command' => 'insert',
            'customer_id' => $customer->id,
            'tot_amt' => 200,
            'tot_grand' => 200,
            'hidden_rowcount' => 1,
            'tr_item_id_0' => $item->id,
            "item_qty_{$item->id}" => 2,
            'sales_price_0' => 100,
            'tr_tax_type_0' => 'Exclusive',
            'tr_tax_value_0' => 0,
            'item_discount_0' => 0,
            'td_data_0_4' => 200,
            'purchase_price_0' => 60,
            'pay_all' => 'false',
            'payment_row_count' => 1,
            'amount_1' => 50,
            'payment_type_1' => 'Cash',
        ]);

        $sale = Sale::latest('id')->first();
        $this->assertSame('Partial', $sale->payment_status);
        $this->assertEquals(50, (float) $sale->paid_amount);

        $customer->refresh();
        $this->assertEquals(150, (float) $customer->sales_due);
    }
}
