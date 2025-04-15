<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyWebhookController extends Controller
{
    public function handleSalesWebhook(Request $request)
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, env('SHOPIFY_ADMIN_PASSWORD'), true));

        if (!hash_equals($hmacHeader, $calculatedHmac)) {
            return response()->json(['message' => 'Invalid HMAC signature'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        $items = $payload['line_items'];

        foreach($items as $item){
            $product = Product::where('shopify_id', $item['product_id'])->first();

            if(empty($product)){
                return ;
            }

            $product->increment('sales_count', $item['quantity']);
        }

        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }
}
