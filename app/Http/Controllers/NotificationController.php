<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get low-stock notifications for the navbar bell icon.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Fetch products with stock <= min_stock, order by stock ASC (most critical first)
        $lowStockProducts = Product::with('category')
            ->where('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->orderBy('name')
            ->get();

        $count = $lowStockProducts->count();

        // Build dropdown HTML
        $dropdown = '';

        if ($count > 0) {
            $maxItems = 10;
            $displayProducts = $lowStockProducts->take($maxItems);

            foreach ($displayProducts as $product) {
                $stock = number_format($product->stock, 0, ',', '.');
                $minStock = number_format($product->min_stock, 0, ',', '.');
                $category = $product->category?->name ?? '-';

                $level = $product->stock <= 0 ? 'danger' : 'warning';

                $dropdown .= '<a href="' . route('products.index') . '" class="dropdown-item">';
                $dropdown .= '<div class="media">';
                $dropdown .= '<div class="media-body">';
                $dropdown .= '<p class="text-sm mb-0">';
                $dropdown .= '<span class="text-' . $level . '"><strong>' . e($product->name) . '</strong></span>';
                $dropdown .= '<br><small class="text-muted">' . e($category) . ' — Stok: ' . $stock . ' / Min: ' . $minStock . '</small>';
                $dropdown .= '</p>';
                $dropdown .= '</div>';
                $dropdown .= '<i class="fas fa-circle text-' . $level . ' ml-2 mt-2" style="font-size: 8px;"></i>';
                $dropdown .= '</div>';
                $dropdown .= '</a>';
                $dropdown .= '<div class="dropdown-divider"></div>';
            }

            if ($count > $maxItems) {
                $remaining = $count - $maxItems;
                $dropdown .= '<div class="dropdown-item text-center text-muted">';
                $dropdown .= '<small>+' . $remaining . ' produk lainnya</small>';
                $dropdown .= '</div>';
                $dropdown .= '<div class="dropdown-divider"></div>';
            }
        } else {
            $dropdown .= '<div class="dropdown-item text-center text-muted">';
            $dropdown .= '<i class="fas fa-check-circle text-success mr-2"></i>Semua stok aman';
            $dropdown .= '</div>';
            $dropdown .= '<div class="dropdown-divider"></div>';
        }

        return response()->json([
            'label' => (string) $count,
            'label_color' => $count > 0 ? 'danger' : 'secondary',
            'icon_color' => $count > 0 ? 'warning' : 'secondary',
            'dropdown' => $dropdown,
        ]);
    }
}