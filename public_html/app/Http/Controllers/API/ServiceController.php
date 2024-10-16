<?php
namespace App\Http\Controllers\API;

use App\Service;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
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
            'duration' => 'required',
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response(['error' => $validator->errors(), 'Validation Error']);
        }

        $service = Service::create($data);

        return response([ 'service' => new ServiceResource($service), 'message' => 'Created successfully'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function show(Service $service)
    {
        return response([ 'service' => new ServiceResource($service), 'message' => 'Retrieved successfully'], 200);
    }

    public function getServicesByStoreId($storeId)
    {
        $services = Service::where('storeId', $storeId)
                            ->where('type', 'INTERNAL')
                            ->orderBy('name', 'ASC')->get();
        return response([ 'services' => $services, 'message' => 'Retrieved successfully'], 200);
    }
    public function getExternalServicesByStoreId($storeId)
    {
        $services = Service::where('storeId', $storeId)
                            ->where('type', 'EXTERNAL')
                            ->orderBy('name', 'ASC')->get();
        return response([ 'services' => $services, 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Service $service)
    {
        $service->update($request->all());
        return response([ 'service' => new ServiceResource($service), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return response(['message' => 'Deleted']);
    }
}
