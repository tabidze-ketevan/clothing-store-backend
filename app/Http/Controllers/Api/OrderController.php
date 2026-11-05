<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request): OrderResource|JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $order = DB::transaction(function () use ($validated) {
                $quantities = collect($validated['items'])
                    ->groupBy('product_variant_id')
                    ->map(fn ($items) => $items->sum('quantity'));

                $variants = ProductVariant::query()
                    ->with('product:id,price')
                    ->whereIn('id', $quantities->keys())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($quantities as $variantId => $quantity) {
                    $variant = $variants->get($variantId);

                    if ($variant === null || $variant->stock === null || $variant->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'items' => "Insufficient stock for product variant {$variantId}.",
                        ]);
                    }
                }

                $total = 0;

                foreach ($quantities as $variantId => $quantity) {
                    $variant = $variants->get($variantId);
                    $total += (float) $variant->product->price * $quantity;
                    $variant->decrement('stock', $quantity);
                }

                $order = Order::query()->create([
                    'total_amount' => round($total, 2),
                    'status' => $validated['status'] ?? 'pending',
                ]);

                foreach ($quantities as $variantId => $quantity) {
                    $variant = $variants->get($variantId);
                    $order->items()->create([
                        'product_variant_id' => $variantId,
                        'quantity' => $quantity,
                        'price' => $variant->product->price,
                    ]);
                }

                return $order->load('items');
            });
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'The order could not be created.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return (new OrderResource($order))
            ->additional(['message' => 'Order created successfully.'])
            ->response()
            ->setStatusCode(201);
    }
}
