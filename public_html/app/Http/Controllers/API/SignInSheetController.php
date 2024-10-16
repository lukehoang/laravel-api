<?php
namespace App\Http\Controllers\API;

use App\SignInSheet;
use App\Http\Controllers\Controller;
use App\Http\Resources\SignInSheetResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\SignInSheetEvent;

use Twilio\Rest\Client;

class SignInSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'storeId' => 'required',
            'customer_name' => 'required'
        ]);

        if($validator->fails()){
            return response(['error' => $validator->errors(), 'Validation Error']);
        }

        $sheet = SignInSheet::create($data);
        event(new SignInSheetEvent($sheet));

        return response([ 'sheet' => new SignInSheetResource($sheet), 'message' => 'Created successfully'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SignInSheet  $service
     * @return \Illuminate\Http\Response
     */
    public function show(SignInSheet $sheet)
    {
        return response([ 'sheet' => new SignInSheetResource($sheet), 'message' => 'Retrieved successfully'], 200);
    }

    public function getSheetsByStoreId($storeId, $date)
    {
        $sheets = SignInSheet::where('storeId', $storeId)
                                ->where('date', $date)
                                ->orderBy('created_at','DESC')->get();
        return response([ 'sheets' => $sheets, 'message' => 'Retrieved successfully'], 200);
    }

    public function getSheetByPhoneNumber($storeId, $phone)
    {
        $sheet = SignInSheet::select('customer_phone', 'customer_name')
                                ->where('storeId', $storeId)
                                ->where('customer_phone', $phone)
                                ->orderBy('created_at','DESC')
                                ->distinct()
                                ->get();
        return response([ 'sheet' => $sheet, 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SignInSheet  $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SignInSheet $sheet)
    {

        // Your Account SID and Auth Token from twilio.com/console
    //    $sid    = env( 'TWILIO_SID' );
    //    $token  = env( 'TWILIO_AUTH_TOKEN' );
    //    $twilio_number  = env( 'TWILIO_NUMBER' );
    //    $twilio_number_sid  = env( 'TWILIO_NUMBER_SID' );

    //    $client = new Client( $sid, $token );

    //    $numbers_in_arrays = $request->phone_list;

    //     $message = strtoupper($request->staff_fname).": ".$request->note;
    //     $count = 0;

    //     foreach( $numbers_in_arrays as $number )
    //     {
    //         $count++;

    //         $client->messages->create(
    //             $number,
    //             [
    //                 'from' => $twilio_number,
    //                 'body' => $message,
    //             ]
    //         );
    //     }
        $message = '';
        if($request->checked){
            $message = 'submitted successfully';
        }else{
            $message = 'updated successfully';
        }
       
        $sheet->update($request->all());
        event(new SignInSheetEvent($sheet));
        return response([ 'sheet' => new SignInSheetResource($sheet), 'message' => $message], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SignInSheet  $service
     * @return \Illuminate\Http\Response
     */
    public function destroy(SignInSheet $sheet)
    {
        $sheet->delete();
        event(new SignInSheetEvent($sheet));
        return response(['message' => 'Deleted']);
    }


    public function getListPhoneNumbers(Request $request)
    {

    // Your Account SID and Auth Token from twilio.com/console

       $client = new Client( $sid, $token );


       $incomingPhoneNumbers = $client->incomingPhoneNumbers
                               ->read([], 20);
       
        return response([ 'numbers' => $incomingPhoneNumbers ], 200);
    }
}
