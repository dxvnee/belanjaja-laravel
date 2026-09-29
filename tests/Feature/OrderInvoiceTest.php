<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function createOrderData(): array
    {
        $seller = User::factory()->create(['name' => 'Toko Elektronik']);
        $buyer = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.com']);
        $unrelatedUser = User::factory()->create(['name' => 'Orang Lain']);

        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
        ]);

        $product = Product::create([
            'user_id'     => $seller->id,
            'category_id' => $category->id,
            'name'        => 'Mouse Gaming RGB',
            'slug'        => 'mouse-gaming-rgb',
            'description' => 'Mouse gaming presisi tinggi.',
            'price'       => 250000,
            'stock'       => 15,
            'location'    => 'Jakarta Barat',
            'is_active'   => true,
        ]);

        $order = Order::create([
            'user_id'          => $buyer->id,
            'total_price'      => 500000,
            'status'           => 'paid',
            'payment_type'     => 'qris',
            'tracking_number'  => 'JNE987654321',
            'shipping_address' => [
                'name'        => 'Budi Santoso',
                'phone'       => '08123456789',
                'detail'      => 'Jl. Sudirman No. 45',
                'city'        => 'Jakarta Selatan',
                'subdistrict' => 'Kebayoran Baru',
                'province'    => 'DKI Jakarta',
                'postal_code' => '12190',
            ],
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'quantity'       => 2,
            'price_snapshot' => 250000,
            'subtotal'       => 500000,
        ]);

        return [$order, $buyer, $seller, $unrelatedUser];
    }

    public function test_buyer_can_stream_invoice_pdf(): void
    {
        [$order, $buyer] = $this->createOrderData();

        $response = $this->actingAs($buyer)->get(route('orders.invoice', $order->id));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_buyer_can_download_invoice_pdf(): void
    {
        [$order, $buyer] = $this->createOrderData();

        $response = $this->actingAs($buyer)->get(route('orders.invoice', [
            'id' => $order->id,
            'download' => 1,
        ]));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Invoice-Belanjaja-ORDER-' . $order->id . '.pdf', $response->headers->get('content-disposition'));
    }

    public function test_seller_can_view_invoice_pdf_for_order_with_their_products(): void
    {
        [$order, $buyer, $seller] = $this->createOrderData();

        $response = $this->actingAs($seller)->get(route('orders.invoice', $order->id));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_unrelated_user_cannot_access_order_invoice(): void
    {
        [$order, $buyer, $seller, $unrelatedUser] = $this->createOrderData();

        $response = $this->actingAs($unrelatedUser)->get(route('orders.invoice', $order->id));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_invoice(): void
    {
        [$order] = $this->createOrderData();

        $response = $this->get(route('orders.invoice', $order->id));

        $response->assertRedirect(route('login'));
    }
}
