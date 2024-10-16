<?php
namespace App\Http\Controllers\API;

use App\StoreLog;
use App\User;
use App\Store;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Events\PostPublished;

class StoreLogController extends Controller
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
    public function createLog(Request $request)
    {
        $data = $request->all();
        $log = StoreLog::create($data);
        event(new PostPublished($log));
        return response(['log' => $log,'message' => 'Created successfully'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Service  $service
     * @return \Illuminate\Http\Response
     */
    // public function show(Service $service)
    // {
    // }

    public function getStoreLogs($storeId)
    {
        $logs = StoreLog::where("storeId", $storeId)
                            ->limit('50')
                            ->orderBy('created_at', 'DESC')->get();
        if($logs){
            return response(['logs' => $logs], 200);
        }else{
            return response(['message' => 'not found'], 404);
        }
    }

    public function getStoreLogsByAppointmentId($id)
    {
        $logs = StoreLog::where("appointmentId", $id)->orderBy('created_at', 'ASC')->get();
        if($logs){
            return response(['logs' => $logs], 200);
        }else{
            return response(['message' => 'not found'], 404);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Service  $service
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Service $service)
    // {
    //     // $service->update($request->all());
    //     // return response([ 'service' => new ServiceResource($service), 'message' => 'Retrieved successfully'], 200);
    // }

    public function markRead(Request $request)
    {
        $log = StoreLog::find($request->log_id);
        $userIds = $log->userIds;
        if($userIds == null){
            $log->userIds = $request->user_id;
        }else{
            $log->userIds = $log->userIds.",".$request->user_id;
        }
        $log->save();
        event(new PostPublished($log));
        return response(['successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Service  $service
     * @return \Illuminate\Http\Response
     */
    // public function destroy(Service $service)
    // {
    //     // $service->delete();
    //     // return response(['message' => 'Deleted']);
    // }
}
