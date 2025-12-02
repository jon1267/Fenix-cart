<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shopping Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Products List</h1>
        <div class="flex items-center gap-4">
            @auth
                <span class="flex items-center">Hi there, {{ auth()->user()->name ?? '' }}</span>
            @else
                <a href="{{ route('login') }}" class="text-blue-600">Login</a>
                <a href="{{ route('register') }}" class="text-blue-600">Register</a>
            @endauth
            <a href="{{ route('cart.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Cart</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-200 p-4 rounded mb-4">{{ session('success') }}</div>
    @endif


    <!-- display validation errors -->
    @if($errors->any())
        <div class="bg-red-200 p-4 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($products as $product)
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-semibold">{{ $product->title }}</h2>
                <p class="text-gray-600 mb-4">{{ $product->description }}</p>
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold">${{ $product->price }}</span>
                    <form action="{{ route('cart.add', $product) }}" method="POST">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                            Add to cart
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
