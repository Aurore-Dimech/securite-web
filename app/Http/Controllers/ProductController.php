<?php

namespace App\Http\Controllers;

use PhpParser\Error;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all()->select(['shopify_id', 'sales_count']);
    }

    public function show(Request $request)
    {
        $user_products = $request->user->products->select(['shopify_id', 'sales_count']);
        return $user_products;
    }

    public function bestsellers(Request $request)
    {
        return Product::where('created_by', $request->user->id)->orderBy('sales_count', 'desc')->get(['shopify_id', 'sales_count']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'The product name is required.',
            'name.string' => 'The product name must be a valid string.',
            'price.required' => 'The product price is required.',
            'price.numeric' => 'The product price must be a valid number.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image size must not exceed 2 MB.',
        ]);

        if (!$request->name || !$request->price) {
            return response()->json(['error' => 'A product needs a name and a price'], 400);
        }

        $shopifyStore = env('SHOPIFY_STORE');
        $adminKey = env('SHOPIFY_ADMIN_KEY');
        $adminPassword = env('SHOPIFY_ADMIN_PASSWORD');

        $url = "https://{$adminKey}:{$adminPassword}@{$shopifyStore}/admin/api/2023-04/products.json";

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
        }

        $productData = [
            'product' => [
                'title' => $request->name,
                'variants' => [
                    [
                        'price' => $request->price,
                    ],
                ],
                'images' => $base64Image ? [
                    [
                        'attachment' => $base64Image,
                    ],
                ] : [],
            ],
        ];

        try {
            $response = Http::post($url, $productData);

            if (!$response->successful() || !isset($response->json()['product']['id'])) {
                return response()->json(['error' => 'An error occurred while storing the product in Shopify.'], 500);
            }

            Product::create([
                'shopify_id' => $response->json()['product']['id'],
                'sales_count' => 0,
                'created_by' => $request->user->id,
            ]);

            return response()->json([
                'message' => 'Product created successfully in Shopify.',
                'data' => $response->json(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create product in Shopify.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
