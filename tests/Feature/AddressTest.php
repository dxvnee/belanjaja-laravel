<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_address_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('address.index'));

        $response->assertStatus(200);
    }

    public function test_user_can_create_address(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('address.store'), [
            'name' => 'Rumah Utama',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'subdistrict' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Dago No. 12',
            'phone' => '08123456789',
        ]);

        $response->assertRedirect(route('address.index'));
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'name' => 'Rumah Utama',
            'city' => 'Bandung',
        ]);
    }

    public function test_user_can_update_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::create([
            'user_id' => $user->id,
            'name' => 'Alamat Lama',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'subdistrict' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Dago No. 12',
            'phone' => '08123456789',
        ]);

        $response = $this->actingAs($user)->put(route('address.update', $address), [
            'name' => 'Alamat Baru',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'subdistrict' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Dago Baru No. 15',
            'phone' => '08123456789',
        ]);

        $response->assertRedirect(route('address.index'));
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'name' => 'Alamat Baru',
        ]);
    }

    public function test_user_cannot_update_others_address(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $address = Address::create([
            'user_id' => $user1->id,
            'name' => 'Alamat User 1',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'subdistrict' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Dago No. 12',
            'phone' => '08123456789',
        ]);

        $response = $this->actingAs($user2)->put(route('address.update', $address), [
            'name' => 'Hacked Name',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'subdistrict' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Dago Baru No. 15',
            'phone' => '08123456789',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::create([
            'user_id' => $user->id,
            'name' => 'Alamat Hapus',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'subdistrict' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Dago No. 12',
            'phone' => '08123456789',
        ]);

        $response = $this->actingAs($user)->delete(route('address.destroy', $address));

        $response->assertRedirect(route('address.index'));
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_user_cannot_delete_others_address(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $address = Address::create([
            'user_id' => $user1->id,
            'name' => 'Alamat User 1',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'subdistrict' => 'Coblong',
            'postal_code' => '40132',
            'detail' => 'Jl. Dago No. 12',
            'phone' => '08123456789',
        ]);

        $response = $this->actingAs($user2)->delete(route('address.destroy', $address));

        $response->assertStatus(403);
    }
}
