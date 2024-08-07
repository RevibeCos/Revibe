<?php

namespace App\Http\Controllers\Website;

use App\Enums\PageTypes;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\OfferResource;
use App\Http\Resources\PageResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Company;
use App\Models\Offer;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {

        $data = [
            'page_title' => 'Home',
            'icon' => 'fas fa-user-tie',
            'slider' => PageResource::collection(Page::where('type', PageTypes::sliderValue())->take(6)->get()),
            'offers' => OfferResource::collection(Offer::latest()->take(2)->get()),
            'specialProducts' => ProductResource::collection(Product::where('in_home', 1)->take(6)->get()),
            'topSellerProducts' => ProductResource::collection(Product::select('products.id', 'products.name', DB::raw('SUM(cart_products.quantity) as total_quantity'))
                ->join('cart_products', 'products.id', '=', 'cart_products.product_id')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_quantity')
                ->get()),
            'newestProducts' => ProductResource::collection(Product::orderBy('created_at', 'desc')->take(10)->get()),
            'categories' => CategoryResource::collection(Category::with('children')->where('parent_id', null)->latest()->take(4)->get()),
            'supCategories' => CategoryResource::collection(Category::whereHas('parent', function ($query) {
                $query->whereNull('parent_id');
            })->get(),),
            'Company' => CompanyResource::collection(Company::all()),

        ];

        return Inertia::render('Home', $data);
    }
}
