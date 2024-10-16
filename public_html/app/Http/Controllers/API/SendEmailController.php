<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Illuminate\Support\Facades\Mail;
use App\Events\AppointmentEvent;
use App\Appointment;
use App\AppointmentServiceMap;
class SendEmailController extends Controller
{

    public function sendEmail(Request $request) {
        // $title = $request->input('title');
        // $content = $request->input('content');
        $data = array('email'=>'mungleephoto@gmail.com');
        
        Mail::send('emails.confirm_mail', $data, function ($message) use ($data)
        {
            $message->from('noreply@nailpocket.com', 'NailPocket Application');
            $message->to($data['email'])->subject('Confirmation instructions for NailPocket account');
        });

        return response()->json(['message' => 'Request completed']);

    }


    public function getAllMessagesByStorePhoneNumber(Request $request) {
        $key    = env( 'SMS_LIVE' );
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://rest.messagebird.com/messages?direction=mo&limit=40',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: AccessKey '.$key
        ),
        ));

        $response = curl_exec($curl);
        $json_object = json_decode($response, true);

        curl_close($curl);

        return response()->json(['response' => $json_object]);
    }

    public function sendSMS(Request $request) {
        $key    = env( 'SMS_LIVE' );
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://rest.messagebird.com/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'recipients='.$request->recipient.'&originator=12068485838&body='.$request->body,
            CURLOPT_HTTPHEADER => array(
                'Authorization: AccessKey '.$key,
              'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        $json_object = json_decode($response, true);

        curl_close($curl);

        return response()->json(['response' => $json_object]);
    }


    public function sendSMSReminder(Request $request) {
        $key    = env( 'SMS_LIVE' );
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://rest.messagebird.com/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'recipients='.$request->recipient.'&originator=12068485838&body='.$request->body,
            CURLOPT_HTTPHEADER => array(
                'Authorization: AccessKey '.$key,
              'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        $json_object = json_decode($response, true);

        curl_close($curl);

        return response()->json(['response' => $json_object]);
    }


    public function sendSMSReminderBulk(Request $request) {

        
        $key    = env( 'SMS_LIVE' );

        foreach ($request->events as $event) {
            $recipient = "1".$event['customerPhone'];
            $body = "BOUTIQUE NAIL SALON\nThis is a friendly reminder of your upcoming appointment on ".$event['dateEvent']." at ".$event['sTime'].". Reply \"Yes\" to confirm your appointment, \"No\" to cancel or call us at 215-862-2111 if you would like to reschedule or have questions.\nThank you!";

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://rest.messagebird.com/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'recipients='.$recipient.'&originator=12068485838&body='.$body,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: AccessKey '.$key,
                'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $response = curl_exec($curl);
            $json_object = json_decode($response, true);

            curl_close($curl);

            $appointment = Appointment::find($event['id']);
            $appointment->note .= " -- Sent reminder";
            $appointment->save();
        }
        

        return response()->json(['response' => $request->events]);
    }


    public function sendSMSNewConfirmation(Request $request) {
        $key    = env( 'SMS_LIVE' );
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://rest.messagebird.com/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'recipients='.$request->recipient.'&originator=12068485838&body='.$request->body,
            CURLOPT_HTTPHEADER => array(
                'Authorization: AccessKey '.$key,
              'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        $json_object = json_decode($response, true);

        curl_close($curl);

        return response()->json(['response' => $json_object]);
    }


    public function sendRescheduleConfirmation(Request $request) {
        $key    = env( 'SMS_LIVE' );
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://rest.messagebird.com/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'recipients='.$request->recipient.'&originator=12068485838&body='.$request->body,
            CURLOPT_HTTPHEADER => array(
                'Authorization: AccessKey '.$key,
              'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        $json_object = json_decode($response, true);

        curl_close($curl);

        return response()->json(['response' => $json_object]);
    }


    


}
