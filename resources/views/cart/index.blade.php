<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Your Cart</h1>
        <a href="{{ route('home') }}" class="text-blue-600 hover:underline">Back to products</a>
    </div>

    @if($cart->items->count() > 0)
        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="border-b">
                <th class="p-3">Product</th>
                <th class="p-3">Price</th>
                <th class="p-3">Quantity</th>
                <th class="p-3">Sum</th>
                <th class="p-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cart->items as $item)
                <tr class="border-b">
                    <td class="p-3">{{ $item->product->title }}</td>
                    <td class="p-3">${{ $item->price }}</td>
                    <td class="p-3">
                        <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                   class="border rounded w-16 p-1 mr-2">
                            <button type="submit" class="text-sm text-blue-500 hover:underline">Save</button>
                        </form>
                    </td>
                    <td class="p-3">${{ $item->sum }}</td>
                    <td class="p-3">
                        <form action="{{ route('cart.remove', $item) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-6 text-right">
            <h3 class="text-2xl font-bold">Total: ${{ $cart->total() }}</h3>
        </div>
    @else
        <p class="text-gray-500 text-center py-10">Your cart empty</p>
    @endif
</div>
</body>
</html>
