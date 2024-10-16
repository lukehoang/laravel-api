<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

use Cloudder;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;


class FileController extends Controller
{
    /*
     * Save Profile Picture
     */

    public function uploadProfilePicture(Request $request, $id, $orientation) {

        if(!$request->hasFile('photo')) {
            return response()->json(['upload_file_not_found'], 400);
        }
        $hash=md5(uniqid(rand(), true));
        $file = $request->file('photo');
        $upload = Cloudder::upload($file, $hash, array('folder'=>'users/'.$id.'/profiles/','overwrite'=>TRUE, "transformation"=>array(array("angle"=>$orientation))));

        $photoUrl = 'https://res.cloudinary.com/nailpocket-com/image/upload/v1594434001/users/'.$id.'/profiles/'.$hash.'.'.$file->extension();

        //update record in Users table
        $user = User::find($id);
        if($user){
            $user->profile_image = $photoUrl;
            $user->save();
        }

        return response(['file' => $upload], 200);

     }

     public function uploadServiceIcon(Request $request) {

        for($i = 1; $i < 51; $i++){
            $upload = Cloudder::upload('https://createeasywebsite.com/img/services/'.$i.'.svg', $i,array('folder'=>'services/nail/','overwrite'=>TRUE));
        }
        return response(['file' => $upload], 200);

     }

     public function sendEmailLukeHoang(Request $request) {

        $data = array('email'=>$request->email,'name'=>$request->name, 'comments'=>$request->message);
        Mail::send('emails.lukehoang', $data, function ($message) use ($data)
        {
            $message->from('noreply@nailpocket.com', 'Lukehoang.com');
            $message->to('lukemhoang@gmail.com')->subject('You got mail');
        });
 
        return response(['message' => 'sent'], 200);

     }
}
