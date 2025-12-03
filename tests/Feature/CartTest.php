<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_item_to_cart(): void
    {
        $product = Product::factory()->create(['price' => 100.00]);
        $sessionToken = 'guest-token-1';

        $this->withSession(['cart_token' => $sessionToken])
            ->post(route('cart.add', $product), ['quantity' => 2]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        // проверяем, что в табл carts действительно есть корзина с этим session_id
        $this->assertDatabaseHas('carts', [
            'session_id' => $sessionToken
        ]);
    }

    public function test_guest_can_update_quantity(): void
    {
        $product = Product::factory()->create();
        $sessionToken = 'guest-token-2';

        // 1. Создаем корзину и товар сразу в БД, минуя контроллер добавления.
        $cart = Cart::create(['session_id' => $sessionToken]);
        $cartItem = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        // 2. Обновляем количество через контроллер
        $response = $this->withSession(['cart_token' => $sessionToken])
            ->patch(route('cart.update', $cartItem), [
                'quantity' => 5
            ]);

        $response->assertRedirect();

        // 3. Проверяем БД (fresh() обновляет модель данными из базы)
        $this->assertEquals(5, $cartItem->fresh()->quantity);
    }

    public function test_guest_can_remove_item(): void
    {
        $product = Product::factory()->create();
        $sessionToken = 'guest-token-3';

        // создаем корзину с 1-им товаром в БД
        $cart = Cart::create(['session_id' => $sessionToken]);
        $cartItem = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        // удаляем
        $response = $this->withSession(['cart_token' => $sessionToken])
            ->delete(route('cart.remove', $cartItem));

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id
        ]);
    }

    public function test_guest_cart_is_merged_to_user_cart_after_login(): void
    {
        $product = Product::factory()->create(['price' => 100]);
        $sessionToken = 'guest-token-4';

        // Создаем гостевую корзину в БД
        $guestCart = Cart::create(['session_id' => $sessionToken]);
        $guestCart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $user = User::factory()->create();

        // Заходим авторизованным юзером с тем же токеном сессии
        $this->withSession(['cart_token' => $sessionToken])
            ->actingAs($user)
            ->get(route('cart.index'));

        // Assert: гостевой корзины больше нет, а товар из нее есть в корзине юзера
        $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);

        $userCart = Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($userCart);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }

    public function test_security_user_cannot_update_others_cart_item(): void
    {
        // создаем user1, user2, товар
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        // User 1 создает  корзину 1
        $cart1 = Cart::create(['user_id' => $user1->id]);
        $item1 = $cart1->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        // User 2 пытается удалить корзину 1
        $response = $this->actingAs($user2)
            ->delete(route('cart.remove', $item1));

        // User 2 отказано в удалении
        $response->assertForbidden();
        $this->assertDatabaseHas('cart_items', ['id' => $item1->id]);
    }
}