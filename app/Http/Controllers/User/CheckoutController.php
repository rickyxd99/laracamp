<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use App\Models\Camp;
use App\Models\User;
use App\Models\Checkout;    
use Auth;
use Mail;
use App\Http\Requests\User\Checkout\Store;
use App\Mail\Checkout\AfterCheckout;


class CheckoutController extends Controller
{   
    public function create(Camp $camp)
    {
        return view('checkout.create', [
            "camp"=>$camp
        ]);
    }

    public function store(Request $request, Camp $camp)
    {
        try{
            DB::beginTransaction();
            // mapping request data
            $data = $request->all();
            $data['user_id'] = Auth::id();
            $data['camp_id'] = $camp->id;
            
            // update user data
            $user = Auth::user();
            $user->email = $data['email'];
            $user->name = $data['name'];
            $user->occupation = $data['occupation'];
            $user->save();

            // create checkout
            $checkout = Checkout::create($data);
            //sending notification via email
            Mail::to(Auth::user()->email)->send(new AfterCheckout($checkout));

            DB::commit();
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
        catch(\Throwable $ex){
            DB::rollBack();
            return redirect()->back()->withErrors(['msg' => $ex->getMessage()]);
        }
        return redirect(route('checkout.success'));
    }

    public function success()
    {
        return view('checkout.success');
    }
}