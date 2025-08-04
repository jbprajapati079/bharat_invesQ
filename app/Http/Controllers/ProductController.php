<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        try {
            $product = Product::where('user_id', Auth::id())->get();
            return response()->json([
                'status' => true,
                'products' => $product
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function search(Request $request)
    {
        try {
            $search = $request->input('name');

            $products = Product::where('user_id', Auth::id())->where('name', 'LIKE', "%{$search}%")->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No product found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'products' => $products
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(ProductRequest $request)
    {
        try {

            $product = Product::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'price' => $request->price,
                'stock' => $request->stock,
            ]);

            return response()->json(
                [
                    'message' => 'Product created successfully.',
                    'data' => [
                        'user_id' => $product->user_id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'stock' => $product->stock,
                    ]
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
