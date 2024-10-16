<?php

namespace App\Http\Controllers\API;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
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
            'fname' => 'required',
        ]);

        if($validator->fails()){
            return response(['error' => $validator->errors(), 'Validation Error']);
        }

        $customer = Customer::create($data);

        return response([ 'customer' => new CustomerResource($customer), 'message' => 'Created successfully'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        return response([ 'customer' => new CustomerResource($customer), 'message' => 'Retrieved successfully'], 200);
    }


    public function getCustomersByStoreId($storeId)
    {
        // $customers = Customer::where('storeId', $storeId)->orderBy('customers.fname', 'ASC')->get();
        $customers = Customer::select('customers.*', 'users.fname AS userFname', 'users.lname AS userLname', 'users.profile_image as url', 'users.color as color')
                            ->leftJoin('employees', 'employees.id', '=', 'customers.fav_staffId')
                            ->leftJoin('users', 'users.id', '=', 'employees.userId')
                            ->where('customers.storeId', $storeId)
                            ->orderBy('customers.fname', 'ASC')->get();
        return response([ 'customers' => $customers, 'message' => 'Retrieved successfully'], 200);
    }

    public function searchCustomerById($storeId,$id)
    {
        $customers = Customer::select('customers.*', 'users.fname AS userFname', 'users.lname AS userLname', 'users.profile_image as url', 'users.color as color')
                            ->leftJoin('employees', 'employees.id', '=', 'customers.fav_staffId')
                            ->leftJoin('users', 'users.id', '=', 'employees.userId')
                            ->where('customers.id', $id)
                            ->where('customers.storeId', $storeId)
                            ->orderBy('customers.fname', 'ASC')->get();

        return response([ 'customers' => $customers, 'message' => 'Retrieved successfully'], 200);
    }

    public function searchCustomer($storeId,$searchKey)
    {
        $customers = Customer::select('customers.*', 'users.fname AS userFname', 'users.lname AS userLname', 'users.profile_image as url', 'users.color as color')
        ->leftJoin('employees', 'employees.id', '=', 'customers.fav_staffId')
        ->leftJoin('users', 'users.id', '=', 'employees.userId')
        ->where('customers.storeId', $storeId)
        ->where(function($q) use ($searchKey){
            $q->where('customers.fname', 'like', '%'.$searchKey.'%')
            ->orWhere('customers.lname', 'like', '%'.$searchKey.'%')
            ->orWhere('customers.email', 'like', '%'.$searchKey.'%')
            ->orWhere('customers.phone', 'like', '%'.$searchKey.'%');
        })
        ->orderBy('customers.fname', 'ASC')->get();

        return response([ 'customers' => $customers, 'message' => 'Retrieved successfully'], 200);
    }

    public function searchCustomerByNameAndPhone($storeId,$fname,$lname,$phone)
    {
        if($fname != 'null' && $lname == 'null' && $phone == 'null'){
            $customers = Customer::select('customers.*', 'users.fname AS userFname', 'users.lname AS userLname', 'users.profile_image as url', 'users.color as color')
            ->leftJoin('employees', 'employees.id', '=', 'customers.fav_staffId')
            ->leftJoin('users', 'users.id', '=', 'employees.userId')
            ->where('customers.storeId', $storeId)
            ->where('customers.fname', 'like', '%'.$fname.'%')->get();
            return response([ 'customers' => $customers, 'message' => 'Retrieved successfully'], 200);
        }
        if($fname != 'null' && $lname != 'null' && $phone == 'null'){
            $customers = Customer::select('customers.*', 'users.fname AS userFname', 'users.lname AS userLname', 'users.profile_image as url', 'users.color as color')
            ->leftJoin('employees', 'employees.id', '=', 'customers.fav_staffId')
            ->leftJoin('users', 'users.id', '=', 'employees.userId')
            ->where('customers.storeId', $storeId)
            ->where('customers.fname', 'like', '%'.$fname.'%')
            ->where('customers.lname', 'like', '%'.$lname.'%')->get();
            return response([ 'customers' => $customers, 'message' => 'Retrieved successfully'], 200);
        }
        if($fname != 'null' && $lname != 'null' && $phone != 'null'){
            $customers = Customer::select('customers.*', 'users.fname AS userFname', 'users.lname AS userLname', 'users.profile_image as url', 'users.color as color')
            ->leftJoin('employees', 'employees.id', '=', 'customers.fav_staffId')
            ->leftJoin('users', 'users.id', '=', 'employees.userId')
            ->where('customers.storeId', $storeId)
            ->where('customers.fname', 'like', '%'.$fname.'%')
            ->where('customers.lname', 'like', '%'.$lname.'%')
            ->where('customers.phone', 'like', '%'.$phone.'%')->get();
            return response([ 'customers' => $customers, 'message' => 'Retrieved successfully'], 200);
        }


        if($fname == 'null' && $lname != 'null' && $phone == 'null'){
            $customers = Customer::select('customers.*', 'users.fname AS userFname', 'users.lname AS userLname', 'users.profile_image as url', 'users.color as color')
            ->leftJoin('employees', 'employees.id', '=', 'customers.fav_staffId')
            ->leftJoin('users', 'users.id', '=', 'employees.userId')
            ->where('customers.storeId', $storeId)
            ->where('customers.lname', 'like', '%'.$lname.'%')->get();
            return response([ 'customers' => $customers, 'message' => 'Retrieved successfully'], 200);
        }

        if($fname == 'null' && $lname == 'null' && $phone != 'null'){
            $customers = Customer::select('customers.*', 'users.fname AS userFname', 'users.lname AS userLname', 'users.profile_image as url', 'users.color as color')
            ->leftJoin('employees', 'employees.id', '=', 'customers.fav_staffId')
            ->leftJoin('users', 'users.id', '=', 'employees.userId')
            ->where('customers.storeId', $storeId)
            ->where('customers.phone', 'like', '%'.$phone.'%')->get();
            return response([ 'customers' => $customers, 'message' => 'Retrieved successfully'], 200);
        }

        if($fname != 'null' && $lname == 'null' && $phone != 'null'){
            $customers = Customer::select('customers.*', 'users.fname AS userFname', 'users.lname AS userLname', 'users.profile_image as url', 'users.color as color')
            ->leftJoin('employees', 'employees.id', '=', 'customers.fav_staffId')
            ->leftJoin('users', 'users.id', '=', 'employees.userId')
            ->where('customers.storeId', $storeId)
            ->where('customers.fname', 'like', '%'.$fname.'%')
            ->where('customers.phone', 'like', '%'.$phone.'%')->get();
            return response([ 'customers' => $customers, 'message' => 'Retrieved successfully'], 200);
        }

        return response([ 'customers' => $customers, 'message' => 'Not Found'], 404);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $customer->update($request->all());
        return response([ 'customer' => new CustomerResource($customer), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response(['message' => 'Deleted']);
    }
}
