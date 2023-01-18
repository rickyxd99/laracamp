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
use Str;
use Midtrans;

class CheckoutController extends Controller
{
    public function __construct()
    {
        // dd( env('MIDTRANS_SERVER_KEY'));
        
        // Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Midtrans\Config::$serverKey = "SB-Mid-server-NOjIO9VG1l4Uramq_wwEM3Px";
        Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        Midtrans\Config::$isSanitized = env('MIDTRANS_IS_SANITIZED');
        Midtrans\Config::$is3ds = env('MIDTRANS_IS_3DS');
    }

    public function create(Request $request,Camp $camp)
    {
        if ($camp->isRegistered) {
            $request->session()->flash('error', "You already registered on {$camp->title} camp.");
            return redirect(route('user.dashboard'));
        }

        return view('checkout.create', [
            "camp"=>$camp
        ]);
    }

    public function store(Store $request, Camp $camp)
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
            $user->phone = $data['phone'];
            $user->address = $data['address'];
            $user->save();

            // create checkout
            $checkout       = Checkout::create($data);
            $snap_setup     = $this->getSnapRedirect($checkout);

            if ($snap_setup['status']) {
                // sending notification via email
                Mail::to(Auth::user()->email)->send(new AfterCheckout($checkout));
                DB::commit();
            } else {
                // abort DB transaction
                DB::rollBack();
                return redirect()->back()->withErrors(['msg' => $snap_setup['msg']]);
            }
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

    public function getSnapRedirect(Checkout $checkout)
    {
        $orderId = $checkout->id.'-'.Str::random(5);
        $price = $checkout->Camp->price * 1000;

        $checkout->midtrans_booking_code = $orderId;

        $transaction_details = [
            'order_id' => $orderId,
            'gross_amount' => $price
        ];

        $item_details[] = [
            'id' => $orderId,
            'price' => $price,
            'quantity' => 1,
            'name' => "Payment for {$checkout->Camp->title} Camp"
        ];

        $userData = [
            "first_name" => $checkout->User->name,
            "last_name" => "",
            "address" => $checkout->User->address,
            "city" => "",
            "postal_code" => "",
            "phone" => $checkout->User->phone,
            "country_code" => "IDN",
        ];

        $customer_details = [
            "first_name" => $checkout->User->name,
            "last_name" => "",
            "email" => $checkout->User->email,
            "phone" => $checkout->User->phone,
            "billing_address" => $userData,
            "shipping_address" => $userData,
        ];

        $midtrans_params = [
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $item_details,
        ];

        try {
            // Get Snap Payment Page URL
            $paymentUrl = \Midtrans\Snap::createTransaction($midtrans_params)->redirect_url;
            $checkout->midtrans_url = $paymentUrl;
            $checkout->save();

            return array('status' => true, 'msg' => "");
        } catch (Exception $e) {
            return array('status' => false, 'msg' => $e->getMessage());
        }
    }

    public function midtransCallback(Request $request)
    {
        $notif = $request->method() == 'POST' ? new Midtrans\Notification() : Midtrans\Transaction::status($request->order_id);

        $transaction_status = $notif->transaction_status;
        $fraud = $notif->fraud_status;
        $checkout_id = explode('-', $notif->order_id)[0];
        $checkout = Checkout::find($checkout_id);

        if ($transaction_status == 'capture') {
            if ($fraud == 'challenge') {
                
                $checkout->payment_status = 'pending';
            }
            else if ($fraud == 'accept') {
               
                $checkout->payment_status = 'paid';
            }
        }
        else if ($transaction_status == 'cancel') {
            if ($fraud == 'challenge') {
           
                $checkout->payment_status = 'failed';
            }
            else if ($fraud == 'accept') {
            
                $checkout->payment_status = 'failed';
            }
        }
        else if ($transaction_status == 'deny') {
          
            $checkout->payment_status = 'failed';
        }
        else if ($transaction_status == 'settlement') {
            
            $checkout->payment_status = 'paid';
        }
        else if ($transaction_status == 'pending') {
           
            $checkout->payment_status = 'pending';
        }
        else if ($transaction_status == 'expire') {
    
            $checkout->payment_status = 'failed';
        }

        $checkout->save();
        return view('checkout/success');
    }
}