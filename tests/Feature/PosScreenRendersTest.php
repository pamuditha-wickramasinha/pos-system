<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosScreenRendersTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_screen_renders_with_keyboard(): void
    {
        $user = User::create([
            'username' => 'cashier',
            'password' => 'secret123',
            'status' => true,
        ]);

        $response = $this->actingAs($user)->get('/pos');

        $response->assertOk();
        $response->assertSee('pos_keyboard');
        $response->assertSee('pos-items-wrap');
        $response->assertSee('id="item_search"', false);
        $response->assertSee('id="pos-form-tbody"', false);
        $response->assertSee('class="tot_grand text-bold"', false);
        $response->assertSee('id="hold_invoice"', false);
        $response->assertSee('show_payments_modal');
        $response->assertSee('id="show_cash_modal"', false);
        $response->assertSee('id="pay_all"', false);
        $response->assertSee('pos-keypad.js');
        $response->assertSee('pos-ui.css');
        $response->assertDontSee('get_category_id');
        $response->assertDontSee('search_div');
    }
}
