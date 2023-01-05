<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Checkout;
use Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // only the checkouts belongs to the user
        $checkouts = Checkout::with('camp')->where('user_id', Auth::id())->get();
        return view('user.dashboard', [
            "checkouts" => $checkouts
        ]);
    }
}