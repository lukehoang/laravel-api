<?php
namespace App\Http\Controllers\API;

use App\Employee;
use App\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Newsletter;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Employee::all();
        return response([ 'employees' => EmployeeResource::collection($employees), 'message' => 'Retrieved successfully'], 200);
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

        $hasAccount = $request->hasAccount;

        if($hasAccount == 'hasAccount'){
            
            $validator = Validator::make($data, [
                'email' => 'email|required',
                // 'PIN' => 'required',
            ]);
            if($validator->fails()){
                return response(['error' => $validator->errors(), 'Validation Error']);
            }

            $user = User::where('email', $request->email)->get();

            if(!$user){
                return response()->json(['not found'], 400);
            }

            $employeeExisted = Employee::where('userId', $user[0]->id)->where('storeId', $request->storeId)->get();
            if(count($employeeExisted) > 0){
                return response()->json('employee existed', 400);
            }         

            //get last inserted id from user
            $request->merge(['userId' => $user[0]->id]);
            // $request->merge(['PIN' => bcrypt($request->PIN)]);
            $employee = Employee::create($request->only(['userId', 'storeId']));//add PIN
            $employee= $employee->makeHidden(['PIN', 'created_at', 'updated_at']);
            return response(['employee' => $employee, 'message' => 'Created successfully'], 200);
            
        }else{
            $validator = Validator::make($data, [
                'fname' => 'required|max:55',
                'lname' => 'required|max:55',
                'email' => 'email|required',
                'password' => 'required',
                // 'PIN' => 'required',
            ]);
    
            if($validator->fails()){
                return response()->json(['has account'], 400);
            }

            $existedUser = User::where('email', $request->email)->get();

            if(count($existedUser) > 0){
                return response()->json(['user existed'], 400);
            }


            $hash = md5(uniqid(rand(), true));
            $request->merge(['hash' => $hash]);
            $request->merge(['password' => bcrypt($request->password)]);
            $user = User::create($request->only(['fname', 'lname', 'hash', 'email', 'password', 'phone', 'color']));
            
            $request->merge(['userId' => $user->id]);
            
            $data = array('email'=>$request->email,'id'=>$user->id, 'hash'=>$hash);

            Mail::send('emails.confirm_mail', $data, function ($message) use ($data)
            {
                $message->from('noreply@nailpocket.com', 'NailPocket Application');
                $message->to($data['email'])->subject('Confirmation instructions for NailPocket account');
            });

           
            
            $user= $user->makeHidden(['hash', 'created_at', 'updated_at', 'email_verified_at']);
            //get last inserted id from user
            // $request->merge(['PIN' => bcrypt($request->PIN)]);
            $employee = Employee::create($request->only(['userId', 'storeId'])); //add PIN
            $employee= $employee->makeHidden(['PIN', 'created_at', 'updated_at']);

            if ( ! Newsletter::isSubscribed($request->email) ) {
                Newsletter::subscribe($request->email);
            }
            
            return response([ 'user' => $user, 'employee' => $employee, 'message' => 'Created successfully'], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        //
    }

      /**
     * Display the specified resource.
     *
     * @param  \App\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function getStaffsByUserId($userId, $storeId)
    {
        $employees = Employee::select('users.fname', 'users.lname', 'users.email', 'users.phone', 'users.profile_image AS url', 'employees.id', 'employees.storeId', 'employees.sortIndex')
                            ->join('users', 'users.id', '=', 'employees.userId')
                            ->where('employees.userId', $userId)
                            ->where('employees.storeId', $storeId)
                            ->get();

        // $employees = Employee::where('storeId', $storeId)->get();
        return response([ 'employees' => $employees, 'message' => 'Retrieved successfully'], 200);
    }

    public function getStaffsByEmployeeId($id)
    {
        $employees = Employee::select('users.id AS userId','users.fname', 'users.lname', 'users.email', 'users.phone', 'users.profile_image AS url', 'employees.id', 'employees.storeId','employees.dayOff', 'employees.sortIndex')
                            ->join('users', 'users.id', '=', 'employees.userId')
                            ->where('employees.id', $id)
                            ->get();

        // $employees = Employee::where('storeId', $storeId)->get();
        return response([ 'employees' => $employees, 'message' => 'Retrieved successfully'], 200);
    }

    public function getStaffsByStoreId($storeId)
    {
        $employees = Employee::select( 'employees.id', 'employees.userId', 'users.id AS userId','users.fname', 'users.lname', 'users.email', 'users.color', 'users.phone', 'users.profile_image AS url', 'employees.sortIndex', 'employees.dayOff' )
                            ->join('users', 'users.id', '=', 'employees.userId')
                            ->where('employees.storeId', $storeId)
                            ->where('employees.status', 'ON')
                            ->orderBy('employees.sortIndex')
                            ->get();

        return response([ 'employees' => $employees, 'message' => 'Retrieved successfully'], 200);
    }
    public function getStaffsArchiveByStoreId($storeId)
    {
        $employees = Employee::select('users.fname', 'users.lname', 'users.email', 'employees.id','users.phone', 'users.profile_image AS url', 'employees.sortIndex' )
                            ->join('users', 'users.id', '=', 'employees.userId')
                            ->where('employees.storeId', $storeId)
                            ->where('employees.status', 'OFF')
                            ->get();

        return response([ 'employees' => $employees, 'message' => 'Retrieved successfully'], 200);
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    public function updateEmployeePIN(Request $request, $id)
    {
        $employee = Employee::find($id);
        
        $oldPIN = $employee->PIN;
        if(Hash::check($request->oldPIN, $oldPIN)) {
            $newPIN  = bcrypt($request->newPIN);
            $employee->PIN = $newPIN;
            $employee->save();
            return response(['successfully'], 200);
        }else{
            return response()->json('diff', 400);
        }
    }

    public function updateEmployeeSortIndex(Request $request, $id)
    {
        $employee = Employee::find($id);
        $employee->sortIndex = $request->sortIndex;
        $employee->save();
        return response(['successfully'], 200);
      
    }

    public function updateEmployeeSchedule(Request $request, $id)
    {
        $employee = Employee::find($id);
        $employee->dayOff = $request->dayOff;
        $employee->save();
        return response(['successfully'], 200);
      
    }

    public function removeEmployee($id)
    {
       //update record in Employees table
       $employee = Employee::find($id);
       if($employee){
           $employee->status = 'OFF';
           $employee->save();
           return response(['successfully'], 200);
       }
       return response()->json(['has account'], 400);
    }

    public function restoreEmployee($id)
    {
       //update record in Employees table
       $employee = Employee::find($id);
       if($employee){
           $employee->status = 'ON';
           $employee->save();
           return response(['successfully'], 200);
       }
       return response()->json(['has account'], 400);
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        //
    }
}
