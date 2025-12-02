<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartService
{
    public function getCart(): Cart
    {
        $user = Auth::user();
        $sessionId = $this->getSessionId(); //Session::getId();

        if ($user) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            if ($cart->session_id !== $sessionId) {
                $cart->update(['session_id' => $sessionId]);
            }

            $this->mergeGuestCart($cart, $sessionId);
            return $cart;
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    public function add(int $productId, int $quantity = 1): void
    {
        $cart = $this->getCart();

        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        $item->update(['quantity' => $quantity]);
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    /**
     *  Если запоминать гостевую корзину, под Session::getId(), это приводит к хитрым граблям.
     *  При логине/регистрации сессия регенерируется, появляется новый Session::getId(),
     *  и мы не можем найти старую корзину. Для решения - генерируем свой униккальный токен:
     *  cart_token ложим его в сессию и храним гостевую корзину под этим id (не под Session::getId())
     *  При регенерации ID сессии (регистрации/логине), так как Laravel переносит данные
     *  из старой сессии в новую, нам доступен наш 'cart_token' для слияния с гостевой корзиной.
     **/
    private function  getSessionId(): string
    {
        if (!Session::has('cart_token')) {
            Session::put('cart_token', Str::random(40));
        }

        return Session::get('cart_token');
    }


    // слияние гостевой корзины с пользовательской после логина/регистрации
    private function mergeGuestCart(Cart $userCart, string $sessionId): void
    {
        $guestCart = Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->where('id', '!=', $userCart->id)
            ->first();

        if (!$guestCart) {
            return;
        }

        foreach ($guestCart->items as $guestItem) {
            $existingItem = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existingItem) {
                $existingItem->increment('quantity', $guestItem->quantity);
            } else {
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();
    }
}