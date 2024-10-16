<?php
namespace App\Http\Controllers\API;

use App\Appointment;
use App\AppointmentServiceMap;
use App\Employee;
use App\User;
use App\Service;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Events\AppointmentEvent;

class AppointmentController extends Controller
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
            'customerId' => 'required',
            'date' => 'required',
            'start' => 'required',
            'end' => 'required',
        ]);

        if($validator->fails()){
            return response(['error' => $validator->errors(), 'Validation Error']);
        }

        $appointment = Appointment::create($data);

        $customer = Customer::where('id', $request->customerId)->first();
        if($customer){
            $visited = $customer->visited;
            $visited++;
            $customer->visited = $visited;
            $customer->lastVisited = $request->date;
            $customer->save();
        }
        event(new AppointmentEvent($appointment));
        return response([ 'appointment' => new AppointmentResource($appointment), 'message' => 'Created successfully'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function show(Appointment $appointment)
    {
        //
    }

    public function getAppointmentsByStoreId($storeId)
    {
        $appointments = Appointment::select('appointments.*', 'users.fname AS userFname', 'users.lname AS userLname','users.phone AS userPhone', 'customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.status', 'ON')
                            ->join('employees', 'employees.id', '=', 'appointments.staffId')
                            ->join('users', 'users.id', '=', 'employees.userId')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();


        for ($i = 0; $i < count($appointments); $i++) {
            $services = explode(',',$appointments[$i]->serviceIds);
            $tmpServices = array();
            for ($j = 0; $j < count($services); $j++){
                $service = Service::find($services[$j]);
                array_push($tmpServices, $service);
            }
            $appointments[$i]['services'] = $tmpServices;
        }
                            
        $appointmentsNull = Appointment::select('appointments.*','customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.staffId', 0)
                            ->where('appointments.status', 'ON')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();

        for ($i = 0; $i < count($appointmentsNull); $i++) {
            $services = explode(',',$appointmentsNull[$i]->serviceIds);
            $tmpServices = array();
            for ($j = 0; $j < count($services); $j++){
                $service = Service::find($services[$j]);
                array_push($tmpServices, $service);
            }
            $appointmentsNull[$i]['services'] = $tmpServices;
        }

         $appointmentsCancel = Appointment::select('appointments.*','customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.status', 'CANCEL')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();

        for ($i = 0; $i < count($appointmentsCancel); $i++) {
            $services = explode(',',$appointmentsCancel[$i]->serviceIds);
            $tmpServices = array();
            for ($j = 0; $j < count($services); $j++){
                $service = Service::find($services[$j]);
                array_push($tmpServices, $service);
            }
            $appointmentsCancel[$i]['services'] = $tmpServices;
        }

        return response([ 'appointments' => $appointments, 'appointmentsNull' => $appointmentsNull, 'appointmentsCancel' => $appointmentsCancel, 'message' => 'Retrieved successfully'], 200);
    }

    public function getAppointmentsByStoreIdAndDate($storeId, $date)
    {
        $appointments = Appointment::select('appointments.*', 'users.fname AS userFname', 'users.lname AS userLname', 'customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.date', $date)
                            ->where('appointments.status', 'ON')
                            ->join('employees', 'employees.id', '=', 'appointments.staffId')
                            ->join('users', 'users.id', '=', 'employees.userId')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();


        for ($i = 0; $i < count($appointments); $i++) {
            $services = explode(',',$appointments[$i]->serviceIds);
            $tmpServices = array();
            for ($j = 0; $j < count($services); $j++){
                $service = Service::find($services[$j]);
                array_push($tmpServices, $service);
            }
            $appointments[$i]['services'] = $tmpServices;
        }
                            
        $appointmentsNull = Appointment::select('appointments.*','customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.date', $date)
                            ->where('appointments.staffId', 0)
                            ->where('appointments.status', 'ON')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();

        for ($i = 0; $i < count($appointmentsNull); $i++) {
            $services = explode(',',$appointmentsNull[$i]->serviceIds);
            $tmpServices = array();
            for ($j = 0; $j < count($services); $j++){
                $service = Service::find($services[$j]);
                array_push($tmpServices, $service);
            }
            $appointmentsNull[$i]['services'] = $tmpServices;
        }

        $appointmentsCancel = Appointment::select('appointments.*','customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.date', $date)
                            ->where('appointments.status', 'CANCEL')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();

        for ($i = 0; $i < count($appointmentsCancel); $i++) {
            $services = explode(',',$appointmentsCancel[$i]->serviceIds);
            $tmpServices = array();
            for ($j = 0; $j < count($services); $j++){
                $service = Service::find($services[$j]);
                array_push($tmpServices, $service);
            }
            $appointmentsCancel[$i]['services'] = $tmpServices;
        }

        return response([ 'appointments' => $appointments, 'appointmentsNull' => $appointmentsNull, 'appointmentsCancel' => $appointmentsCancel, 'message' => 'Retrieved successfully'], 200);
    }

    public function getAppointmentsById($id)
    {
        $appointments = Appointment::select('appointments.*', 'users.fname AS userFname', 'users.lname AS userLname', 'customers.id AS customerId','customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.id', $id)
                            // ->where('appointments.status', 'ON')
                            ->join('employees', 'employees.id', '=', 'appointments.staffId')
                            ->join('users', 'users.id', '=', 'employees.userId')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();


        for ($i = 0; $i < count($appointments); $i++) {
            $services = explode(',',$appointments[$i]->serviceIds);
            $tmpServices = array();
            for ($j = 0; $j < count($services); $j++){
                $service = Service::find($services[$j]);
                array_push($tmpServices, $service);
            }
            $appointments[$i]['services'] = $tmpServices;
        }
                            
        $appointmentsNull = Appointment::select('appointments.*', 'customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.id', $id)
                            ->where('appointments.staffId', 0)
                            // ->where('appointments.status', 'ON')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();

        for ($i = 0; $i < count($appointmentsNull); $i++) {
            $services = explode(',',$appointmentsNull[$i]->serviceIds);
            $tmpServices = array();
            for ($j = 0; $j < count($services); $j++){
                $service = Service::find($services[$j]);
                array_push($tmpServices, $service);
            }
            $appointmentsNull[$i]['services'] = $tmpServices;
        }

         $appointmentsCancel = Appointment::select('appointments.*', 'customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.id', $id)
                            ->where('appointments.status', 'CANCEL')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();

        for ($i = 0; $i < count($appointmentsCancel); $i++) {
            $services = explode(',',$appointmentsCancel[$i]->serviceIds);
            $tmpServices = array();
            for ($j = 0; $j < count($services); $j++){
                $service = Service::find($services[$j]);
                array_push($tmpServices, $service);
            }
            $appointmentsCancel[$i]['services'] = $tmpServices;
        }

        return response([ 'appointments' => $appointments, 'appointmentsNull' => $appointmentsNull, 'appointmentsCancel' => $appointmentsCancel, 'message' => 'Retrieved successfully'], 200);
    }


    public function getAppointmentsByStoreIdAndEmployeeId($storeId,$employeeId,$date)
    {
        $appointments = Appointment::select('appointments.*', 'users.fname AS userFname', 'users.lname AS userLname', 'customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.staffId', $employeeId)
                            ->where('appointments.date', $date)
                            ->where('appointments.status', 'ON')
                            ->join('employees', 'employees.id', '=', 'appointments.staffId')
                            ->join('users', 'users.id', '=', 'employees.userId')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->orderBy('appointments.start')
                            ->get();

        $appointmentsNull = Appointment::select('appointments.*', 'customers.id AS customerId', 'customers.fname AS customerFName', 'customers.lname AS customerLName','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.staffId', 0)
                            ->where('appointments.date', $date)
                            ->where('appointments.status', 'ON')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();

        $appointmentsCancel = Appointment::select('appointments.*', 'customers.id AS customerId', 'customers.fname AS customerFName', 'customers.lname AS customerLName','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.date', $date)
                            ->where('appointments.status', 'CANCEL')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->get();


        return response([ 'appointments' => $appointments,'appointmentsNull' => $appointmentsNull, 'appointmentsCancel' => $appointmentsCancel, 'message' => 'Retrieved successfully'], 200);
    }

    public function getAppointmentsByStoreIdAndCustomerId($storeId,$customerId)
    {
        $status = ['ON', 'CANCEL'];
        $appointments = Appointment::select('appointments.*', 'users.fname AS userFname', 'users.lname AS userLname', 'customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.customerId', $customerId)
                            ->whereIn('appointments.status', $status)
                            ->leftJoin('employees', 'employees.id', '=', 'appointments.staffId')
                            ->leftJoin('users', 'users.id', '=', 'employees.userId')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->orderBy('appointments.id', 'DESC')->get();

        for ($i = 0; $i < count($appointments); $i++) {
            $services = explode(',',$appointments[$i]->serviceIds);
            $tmpServices = array();
            for ($j = 0; $j < count($services); $j++){
                $service = Service::find($services[$j]);
                array_push($tmpServices, $service);
            }
            $appointments[$i]['services'] = $tmpServices;
        }
        // $appointmentsNull = Appointment::select('appointments.*', 'customers.id AS customerId', 'customers.fname AS customerFName', 'customers.lname AS customerLName','customers.phone AS customerPhone')
        //                     ->where('appointments.storeId', $storeId)
        //                     ->where('appointments.staffId', 0)
        //                     ->where('appointments.date', $date)
        //                     ->where('appointments.status', 'ON')
        //                     ->join('customers', 'customers.id', '=', 'appointments.customerId')
        //                     ->get();


        return response([ 'appointments' => $appointments,/* 'appointmentsNull' => $appointmentsNull, */ 'message' => 'Retrieved successfully'], 200);
    }

    public function getAppointmentsByStoreIdAndType($type, $storeId, $data, $date)
    {
        $appointments = Appointment::select('appointments.*', 'users.fname AS userFname', 'users.lname AS userLname', 'customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLname','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.date', $date)
                            ->where('appointments.status', 'ON')
                            ->join('employees', 'employees.id', '=', 'appointments.staffId')
                            ->join('users', 'users.id', '=', 'employees.userId')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->where('customers.'.$type, $data)
                            ->get();

        $appointmentsNull = Appointment::select('appointments.*', 'customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLName','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.staffId', 0)
                            ->where('appointments.date', $date)
                            ->where('appointments.status', 'ON')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->where('customers.'.$type, $data)
                            ->get();

        $appointmentsCancel = Appointment::select('appointments.*', 'customers.id AS customerId', 'customers.fname AS customerFname', 'customers.lname AS customerLName','customers.phone AS customerPhone')
                            ->where('appointments.storeId', $storeId)
                            ->where('appointments.date', $date)
                            ->where('appointments.status', 'CANCEL')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->where('customers.'.$type, $data)
                            ->get();


        return response([ 'appointments' => $appointments,'appointmentsNull' => $appointmentsNull, 'appointmentsCancel' => $appointmentsCancel, 'message' => 'Retrieved successfully'], 200);
    }

    public function getPTOByEmployeeId($employeeId)
    {
        $pto = Appointment::select('appointments.*')
                            ->where('appointments.staffId', $employeeId)
                            ->where('appointments.customerId', 0)
                            ->where('appointments.serviceIds', '0')
                            ->where('appointments.status', 'ON')
                            ->join('employees', 'employees.id', '=', 'appointments.staffId')
                            ->join('users', 'users.id', '=', 'employees.userId')
                            ->leftJoin('customers', 'customers.id', '=', 'appointments.customerId')
                            ->orderBy('appointments.date', 'ASC')->get();


        return response([ 'pto' => $pto, 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Appointment $appointment)
    {
        $appointment->update($request->all());
        event(new AppointmentEvent($appointment));
        return response([ 'appointment' => new AppointmentResource($appointment), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response(['message' => 'Deleted']);
    }

    public function removeAppointment($id)
    {
       //update record in Employees table
       $appointment = Appointment::find($id);
       if($appointment){
           $appointment->status = 'OFF';
           $appointment->save();
           event(new AppointmentEvent($appointment));
           return response(['successfully'], 200);
       }
       return response()->json(['failed'], 400);
    }

    public function cancelAppointment($id)
    {
       //update record in Employees table
       $appointment = Appointment::find($id);
       if($appointment){
           $appointment->status = 'CANCEL';
           $appointment->save();
           event(new AppointmentEvent($appointment));
           return response(['successfully'], 200);
       }
       return response()->json(['failed'], 400);
    }


    public function mergeCustomers(Request $request)
    {
        //update record in Employees table
       $destAppointments = Appointment::where('customerId', $request->dest)->get();
       if($destAppointments){
           foreach ($destAppointments as $app) {
            $appointment = Appointment::find($app->id);
            if($appointment){
                $appointment->customerId = $request->source;
                $appointment->save();
            }
           }
        }
        $destCustomer = Customer::find($request->dest);
        $destCustomer->delete();
        return response([ 'message' => ' successfully'], 200);
    }

}
