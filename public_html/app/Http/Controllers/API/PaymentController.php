<?php

namespace App\Http\Controllers\API;

use App\Store;
use App\Payment;
use App\Package;
use App\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;
use Stripe\Invoice;
use Stripe\Subscription;
use Stripe\Product;
use Stripe\Price;
use Stripe\PaymentMethod;


class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $stores = Store::all();
        // return response([ 'stores' => StoreResource::collection($stores), 'message' => 'Retrieved successfully'], 200);
    }

    public function getPaymentsByStoreId($id)
    {
        $payments = Payment::select("*")
                            ->where('storeId', $id)
                            ->orderBy('created_at', 'desc')
                            ->get();
        return response([ 'payments' => $payments, 'message' => 'Retrieved successfully'], 200);
    }

    public function getPackageByStoreId($id)
    {
        try {
            $package = Store::select("packages.id as packageId","packages.stripe_price_id", "stores.name as storeName", "stores.expiration", "stores.sub_id") 
                            ->join('packages', 'stores.packageId', '=', 'packages.id')
                            ->where('stores.id', $id)
                            ->first();

            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $price = null;
            if($package->stripe_price_id != null){
                $price = Price::retrieve(
                    $package->stripe_price_id,
                    []
                );
            }

            if($price != null){

                $product = Product::retrieve(
                    $price['product'],
                    []
                );

                $subscription = null;

                if($package->sub_id != null){
                    $subscription = Subscription::retrieve(
                        $package->sub_id,
                        []
                    );
                }

                if($subscription){
                    return response(['id' => $id,'packageId' => $package->packageId, 'storeName' => $package->storeName, 'expiration' => $subscription->status, 'price' => $price->unit_amount, 'product' => $product->name, 'current_period_end' => $subscription->current_period_end, 'current_period_start' => $subscription->current_period_start], 200);
                }else{
                    return response(['packageId' => $package->packageId, 'storeName' => $package->storeName, 'expiration' => null, 'price' => $price->unit_amount, 'product' => $product->name, 'current_period_end' => null, 'current_period_start' => null], 200);
                }
               
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }

    // return response(['expiration' => 'active'], 200);
        
    }

    public function makePayment(Request $request)
    {

        try {
           
            $store = Store::find($request->storeId);
            if($store){

                // $user = User::select("*") 
                //             ->where('email', $request->stripeEmail)
                //             ->first();
                
                //Get Package
                $package = Package::find($store->packageId);
                $price = $package->price;

                //add payment to Stripe
                Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
                // Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
        
                if(!$store->stripe_id){
                    $customer = Customer::create(array(
                        'email' => $request->stripeEmail,
                        'source' => $request->stripeToken
                    ));
                    $customerId = $customer->id;
                    $store->stripe_id = $customer->id;
                }else{
                    $customer = Customer::update(
                        $store->stripe_id,
                        ['source' => $request->stripeToken],
                        ['description' => ucwords($store->name)]
                    );
                    $customerId = $customer->id;
                }
                
                if($customerId){
                    $charge = Charge::create(array(
                        'customer' => $customerId,
                        'amount' => $price,
                        'currency' => 'usd',
                        'description' => 'NailPocket/'.ucwords($package->name).' - '.ucwords($store->name),
                        'receipt_email' => $request->stripeEmail
                    ));
                }
    
                if($charge->id){
                    //create invoice
                    // $invoice = Invoice::create(
                    //     ['customer' => $customerId]
                    // );

                    //add record to Payment table
                    $payment = Payment::create(array(
                        'storeId' => $request->storeId,
                        'amount' => $price,
                        'receipt_url' => $charge->receipt_url
                    ));

                    //update Store expiration date
                    // $thisMonth = date('m', strtotime('+0 month'));
                    // $nextMonth = date('m', strtotime('+1 month'));
                    // if($thisMonth != $nextMonth){
                    //     $expiration = date('Y-m-01', strtotime(date('m', strtotime('+1 month')).'/01/'.date('Y').' 00:00:00'));
                    // }else{
                    //     $expiration = date('Y-m-01', strtotime(date('m', strtotime('+1 month')).'/02/'.date('Y').' 00:00:00'));
                    // }

                    $expiration = date('Y-m-d', strtotime('+1 month'));

                    $store->expiration = $expiration;
                    $store->save();

                    $data = array('email'=>$request->stripeEmail, 'fname'=>'Luke', 'customerName'=>ucwords($store->name), 'receipt_url'=>$charge->receipt_url);
    
                    Mail::send('emails.payment', $data, function ($message) use ($data)
                    {
                        $message->from('noreply@nailpocket.com', 'NailPocket');
                        $message->to('mungleephoto@gmail.com')->subject('Customer payment confirmation');
                    });
                }
               
            }
        
            return response(['successfully'], 200);

        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    
    public function getAllSub()
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            $client_stripe_ids = array();
            $subscription_results = Subscription::all();
            if($subscription_results){
                foreach ($subscription_results->data as $sub) {
                    array_push($client_stripe_ids, $sub->customer);
                }
                $client_ids = Store::select("id") 
                ->whereIn('stripe_id', $client_stripe_ids)
                ->get();

                return response([ 'client_ids' => $client_ids, 'message' => 'Retrieved successfully'], 200);
            }else{  
                return response([ 'client_ids' => null, 'message' => 'no subscription found'], 200);
            }

           

        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }


    public function getInvoicesByStoreId($id)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $store = Store::find($id);

            if($store->stripe_id){
                $invoices = Invoice::all(
                    ['customer' => $store->stripe_id]
                );
                return response([ 'invoices' => $invoices->data, 'message' => 'Retrieved successfully'], 200);
            }
            return response(['invoices' => [], 'message' => 'No invoices'], 200);

        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getPMByStoreId($id)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $store = Store::find($id);

            if($store->stripe_id){
                $paymentMethods = PaymentMethod::all(
                    [
                        'customer' => $store->stripe_id,
                        'type' => 'card'
                    ]
                );

                $customer = Customer::retrieve(
                    $store->stripe_id,
                    []
                );
                return response([ 'default_source' => $customer->default_source, 'paymentMethods' => $paymentMethods->data, 'message' => 'Retrieved successfully'], 200);
            }
            return response(['paymentMethods' => [], 'message' => 'No invoices'], 200);

        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function setDefaultPMByStoreId(Request $request, $id)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $store = Store::find($id);

            if($store->stripe_id){
                $customer = Customer::update(
                    $store->stripe_id,
                    [
                        'default_source' => $request->pm_source
                    ]
                );
                return response([ 'customer' => $customer, 'message' => 'updated successfully'], 200);
            }
            return response(['customer' => [], 'message' => 'No data'], 200);

        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getAllPlans()
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            $products = Package::select("*")
                        ->where('status', 'ON')
                        ->get();

            for($i = 0; $i < count($products); $i++) {
                $product_price = '';
                $product_name = '';
                if($products[$i]->stripe_price_id){
                    $price = Price::retrieve(
                        $products[$i]->stripe_price_id,
                        []
                    );
                    $product_price = $price->unit_amount;
                    $product = Product::retrieve(
                        $price['product'],
                        []
                    );
                    $product_name =  $product->name;
                }
                $products[$i]['product_price'] = $product_price;
                $products[$i]['product_name'] = $product_name;
            }
            $products= $products->makeHidden([ 'created_at', 'updated_at', 'stripe_price_id']);
            
            return response([ 'products' => $products, 'message' => 'Retrieved successfully'], 200);

        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
    

    public function subscribe(Request $request, $id)
    {
        try {
            $store = Store::find($id);
            if($store){

                Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

                if(!$store->stripe_id){
                    $customer = Customer::create(array(
                        'email' => $request->stripeEmail,
                        'source' => $request->stripeToken,
                        'name' => $request->full_name,
                        'address' => [
                            'line1' => $request->line1,
                            'city' => $request->city,
                            'state' => $request->state,
                            'postal_code' => $request->postal_code
                        ],
                        'description' => ucwords($store->name)
                    ));
                    $customerId = $customer->id;
                    $store->stripe_id = $customer->id;
                }else{
                    $customer = Customer::update(
                        $store->stripe_id,
                        ['source' => $request->stripeToken],
                        ['description' => ucwords($store->name)]
                    );
                    $customerId = $customer->id;
                }

                if($customerId){

                    //Get Package
                    $package = Package::find($request->packageId);
                    $subscription = Subscription::create(array(
                        'customer' => $customerId,
                        'items' => [
                            ['price' => $package->stripe_price_id]
                        ]
                    ));

                    // $status = $subscription->status;

                    $store->packageId = $request->packageId;
                    $store->sub_id = $subscription->id;
                    // $store->expiration = $status;
                    $store->save();

                    $data = array('email'=>$request->stripeEmail, 'fname'=>'Luke', 'customerName'=>ucwords($store->name));
    
                    Mail::send('emails.payment', $data, function ($message) use ($data)
                    {
                        $message->from('noreply@nailpocket.com', 'NailPocket');
                        $message->to('mungleephoto@gmail.com')->subject('Customer payment confirmation');
                    });

                    return response([ 'subscription' => $subscription, 'message' => 'Success'], 200);
                }

            }else{
                return response(['message' => 'Access denied'], 400);
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    
}
