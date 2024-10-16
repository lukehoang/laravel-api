<?php
namespace App\Http\Controllers\API;

use App\Setting;
use App\User;
use App\Store;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\VersionEvent;

class SettingController extends Controller
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
    // public function store(Request $request)
    // {
    // }

    /**
     * Display the specified resource.
     *
     * @param  \App\Service  $service
     * @return \Illuminate\Http\Response
     */
    // public function show(Service $service)
    // {
    // }

    public function getSetting($account_type, $id)
    {
        $setting = Setting::select("app_version", "body")->first();
        if($account_type == 'store'){
            $store = Store::select("app_version")->where('id', $id)->first();
            if($setting->app_version == $store->app_version){
                return response(['message' => true, 'app_version' => $setting->app_version, 'body' => $setting->body], 200);
            }else{
                return response(['message' => false, 'app_version' => $setting->app_version, 'body' => $setting->body], 200);
            }
        }else if($account_type == 'staff'){
            $user = User::select("app_version")->where('id', $id)->first();

            if($setting->app_version == $user->app_version){
                return response(['message' => true, 'app_version' => $setting->app_version, 'body' => $setting->body], 200);
            }else{
                return response(['message' => false, 'app_version' => $setting->app_version, 'body' => $setting->body], 200);
            }
        }
        
    }


    public function getSettings()
    {
        $setting = Setting::select("app_version", "body")->first();
        return response(['message' => true, 'app_version' => $setting->app_version, 'body' => $setting->body], 200);
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

    public function updateSetting(Request $request, $account_type, $id)
    {
       
        if($account_type == 'store'){
            $store = Store::find($id);
            $store->app_version = $request->app_version;
            $store->save();
            return response(['successfully'], 200);
        }else if($account_type == 'staff'){
            $user = User::find($id);
            $user->app_version = $request->app_version;
            $user->save();
            return response(['successfully'], 200);
        }
    }


    public function updateVersion(Request $request)
    {
        $setting = Setting::find(1);
        $setting->app_version = $request->app_version;
        $setting->body = $request->body;
        $setting->save();
        event(new VersionEvent($setting));
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
