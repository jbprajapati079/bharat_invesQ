<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        try {
            $orders = Order::with('product')->where('user_id', Auth::id())->get();

            return response()->json([
                'status' => true,
                'data' => $orders
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(OrderRequest $request)
    {

        try {
            $userId = auth()->id();

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    return response()->json(
                        [
                            'status' => false,
                            'message' => "Product not found",
                        ],
                        404
                    );
                }

                if ($product->stock < $item['quantity']) {
                    return response()->json(
                        [
                            'status' => false,
                            'message' => "Insufficient stock for {$product->name}"
                        ],
                        400
                    );
                }

                $totalPrice = $product->price * $item['quantity'];

                $order = Order::create([
                    'user_id' => $userId,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total_price' => $totalPrice,
                ]);

                $product->stock -= $item['quantity'];
                $product->save();
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Order created successfully.',
                ],
                201
            );
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
