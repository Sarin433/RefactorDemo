<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display product listing.
     * Replaces legacy public/index.php
     */
    public function index(): View
    {
        $products = Product::orderBy('product_number')->get();

        return view('products.index', compact('products'));
    }
}
