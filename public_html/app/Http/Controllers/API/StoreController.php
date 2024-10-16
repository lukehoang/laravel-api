<?php

namespace App\Http\Controllers\API;

use App\Store;
use App\Employee;
use App\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stores = Store::all();
        return response([ 'stores' => StoreResource::collection($stores), 'message' => 'Retrieved successfully'], 200);
    }

    public function storeLogin(Request $request)
    {
        $loginData = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);; 

        $user = Store::where('username', $request->username)->first();
    
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        auth()->user($user);
        $accessToken = $user->createToken('authToken')->accessToken;
        $user= $user->makeHidden(['hash', 'created_at', 'updated_at', 'email_verified_at']);
        return response(['store' => $user, 'access_token' => $accessToken]);

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'ownerId' => 'required|max:255',
            'name' => 'required|max:255',
            'username' => 'required|max:255',
            'password' => 'required',
            'email' => 'email|required'
        ]);

        if($validator->fails()){
            return response(['error' => $validator->errors(), 'Validation Error']);
        }
        
        $data['password'] = bcrypt($data['password']);

        $store = Store::create($data);
        $accessToken = $store->createToken('authToken')->accessToken;
        //get last inserted id from user
        $request->merge(['storeId' => $store->id]);
        // $request->merge(['PIN' => bcrypt($request->Pin)]);
        $employee = Employee::create($request->only(['userId', 'storeId']));//add PIN
        
        return response([ 'store' => new StoreResource($store), 'message' => 'Created successfully'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Store  $store
     * @return \Illuminate\Http\Response
     */

    public function getAllStores()
    {
        $stores = Store::all();
        return response([ 'stores' => new StoreResource($stores), 'message' => 'Retrieved successfully'], 200);
    }

    public function getStoreByOwnerId($ownerId)
    {
        $store = Store::where('ownerId', $ownerId)->where('status', 'ON')->get();
         return response([ 'store' => new StoreResource($store), 'message' => 'Retrieved successfully'], 200);
    }
    public function getStoreByPermalink($permalink)
    {
        $store = Store::where('permalink', $permalink)->get();
        return response([ 'store' => new StoreResource($store), 'message' => 'Retrieved successfully'], 200);
    }
    public function getStoreByUserId($userId)
    {
        $store = Employee::select('*')
                            ->join('stores', 'stores.id', '=', 'employees.storeId')
                            ->where('userId', $userId)
                            ->where('stores.status', 'ON')
                            ->where('employees.status', 'ON')
                            ->get();
         return response([ 'store' => new StoreResource($store), 'message' => 'Retrieved successfully'], 200);
    }


     /**
     * Display the specified resource.
     *
     * @param  \App\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function show(Store $store)
    {
        return response([ 'store' => new StoreResource($store), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Store $store)
    {
        $store->update($request->all());

        return response([ 'store' => new StoreResource($store), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function destroy(Store $store)
    {
        $store->delete();
        return response(['message' => 'Deleted']);
    }


    public function removeStore($id)
    {
       //update record in Employees table
       $store = Store::find($id);
       if($store){
           $store->status = 'OFF';
           $store->save();
           return response(['successfully'], 200);
       }
       return response()->json(['has account'], 400);
    }

    public function storeResetPassword(Request $request)
    {
        $user = User::where('id', $request->userId)->first();

        if($user){
            $userPassword = $user->password;
            if(Hash::check($request->userPassword, $userPassword)) {
                $data = array('email'=>$user->email,'fname'=>$user->fname);

                $store = Store::where('id', $request->storeId)->first();

                $newPassword  = bcrypt($request->newPassword);
                $store->password = $newPassword;
                $store->save();
                Mail::send('emails.reset_password', $data, function ($message) use ($data)
                {
                    $message->from('noreply@nailpocket.com', 'NailPocket Application');
                    $message->to($data['email'])->subject('Store Password reset successfully');
                });
    
                return response(['successfully'], 200);
            }else{
                return response()->json('diff', 400);
            }
        }else{
            return response(['message' => 'Failed'], 404);
        }
    }

    
}
