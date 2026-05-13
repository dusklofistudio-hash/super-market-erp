<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'branches' => Branch::count(),
                'products' => Product::count(),
                'customers' => Customer::count(),
                'suppliers' => Supplier::count(),
            ],
        ]);
    }
}
