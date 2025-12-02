<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(): View
    {
        $cart = $this->cartService->getCart()->load('items.product');
        return view('cart.index', ['cart' => $cart]);
    }

    public function add(AddToCartRequest $request, Product $product): RedirectResponse
    {

        $quantity = $request->get('quantity', 1);

        $this->cartService->add($product->id, $quantity);

        return redirect()->back()->with('success', 'Product added to cart!');;
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): RedirectResponse
    {
        $this->cartService->updateQuantity(
            $item,
            $request->validated('quantity')
        );

        return redirect()->back();
    }

    public function remove(CartItem $item): RedirectResponse
    {
        // Повторная проверка твоя ли это корзина, если Remove не имеет своего Request класса
        abort_if(
            $item->cart->user_id !== auth()->id() &&
            $item->cart->session_id !== session()->get('cart_token', 'guest'),
            403
        );

        $this->cartService->remove($item);

        return redirect()->back();
    }
}
