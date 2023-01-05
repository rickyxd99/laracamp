<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Checkout;
use Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // all checkouts
        $checkouts = Checkout::with('camp')->get();
        return view('admin.dashboard', [
            "checkouts" => $checkouts
        ]);
    }
}