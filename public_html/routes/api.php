<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->middleware('log.route')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('sendEmailLukeHoang', 'API\FileController@sendEmailLukeHoang')->middleware('log.route');

//Files
Route::post('uploadProfilePicture/{id}/{orientation}', 'API\FileController@uploadProfilePicture')->middleware('log.route');
Route::post('uploadServiceIcon', 'API\FileController@uploadServiceIcon')->middleware('log.route');

//Logs 
Route::post('create-log', 'API\StoreLogController@createLog')->middleware('auth:api')->middleware('log.route');
Route::get('getStoreLogs/{storeId}', 'API\StoreLogController@getStoreLogs')->middleware('auth:api')->middleware('log.route');
Route::get('getStoreLogsByAppointmentId/{id}', 'API\StoreLogController@getStoreLogsByAppointmentId')->middleware('auth:api');
Route::post('markRead', 'API\StoreLogController@markRead')->middleware('auth:api')->middleware('log.route');

//Settings 
Route::get('getSetting/{account_type}/{id}', 'API\SettingController@getSetting')->middleware('auth:api')->middleware('log.route');
Route::get('getSettings', 'API\SettingController@getSettings')->middleware('auth:api')->middleware('log.route');
Route::post('updateSetting/{account_type}/{id}', 'API\SettingController@updateSetting')->middleware('auth:api')->middleware('log.route');
Route::post('updateVersion', 'API\SettingController@updateVersion')->middleware('auth:api')->middleware('log.route');

//Login | Sign Up
Route::post('register', 'API\AuthController@register')->middleware('log.route');
Route::post('login', 'API\AuthController@login')->middleware('log.route');
Route::post('verify', 'API\AuthController@verify')->middleware('log.route');
Route::post('forgotPassword', 'API\AuthController@forgotPassword')->middleware('log.route');
Route::post('resetPassword', 'API\AuthController@resetPassword')->middleware('log.route');
Route::post('resetPasswordValidation', 'API\AuthController@resetPasswordValidation')->middleware('log.route');
Route::post('verify', 'API\AuthController@verify')->middleware('log.route');

Route::post('storeLogin', 'API\StoreController@storeLogin')->middleware('log.route');

//Users
Route::apiResource('user', 'API\AuthController')->middleware('auth:api')->middleware('log.route');
Route::post('userResetPassword', 'API\AuthController@userResetPassword')->middleware('auth:api')->middleware('log.route');
Route::get('getAllUsers', 'API\AuthController@getAllUsers')->middleware('auth:api')->middleware('log.route');


//Stores
Route::apiResource('store', 'API\StoreController')->middleware('auth:api')->middleware('log.route');
Route::get('getStoreByOwnerId/{ownerId}', 'API\StoreController@getStoreByOwnerId')->middleware('auth:api')->middleware('log.route');
Route::get('getStoreByUserId/{userId}', 'API\StoreController@getStoreByUserId')->middleware('auth:api')->middleware('log.route');
Route::get('getStoreByPermalink/{permalink}', 'API\StoreController@getStoreByPermalink')->middleware('auth:api')->middleware('log.route');
Route::get('removeStore/{id}', 'API\StoreController@removeStore')->middleware('auth:api')->middleware('log.route');
Route::post('storeResetPassword', 'API\StoreController@storeResetPassword')->middleware('auth:api')->middleware('log.route');
Route::get('getAllStores', 'API\StoreController@getAllStores')->middleware('auth:api')->middleware('log.route');


//Employees
Route::apiResource('staff', 'API\EmployeeController')->middleware('auth:api')->middleware('log.route');
Route::get('getStaffsByStoreId/{storeId}', 'API\EmployeeController@getStaffsByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('getStaffsArchiveByStoreId/{storeId}', 'API\EmployeeController@getStaffsArchiveByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('getStaffsByEmployeeId/{id}', 'API\EmployeeController@getStaffsByEmployeeId')->middleware('auth:api')->middleware('log.route');
Route::get('getStaffsByUserId/{userId}/{storeId}', 'API\EmployeeController@getStaffsByUserId')->middleware('auth:api')->middleware('log.route');
Route::get('removeEmployee/{id}', 'API\EmployeeController@removeEmployee')->middleware('auth:api')->middleware('log.route');
Route::get('restoreEmployee/{id}', 'API\EmployeeController@restoreEmployee')->middleware('auth:api')->middleware('log.route');
Route::post('updateEmployeePIN/{id}', 'API\EmployeeController@updateEmployeePIN')->middleware('auth:api')->middleware('log.route');
Route::post('updateEmployeeSortIndex/{id}', 'API\EmployeeController@updateEmployeeSortIndex')->middleware('auth:api')->middleware('log.route');
Route::post('updateEmployeeSchedule/{id}', 'API\EmployeeController@updateEmployeeSchedule')->middleware('auth:api')->middleware('log.route');


//Services
Route::apiResource('service', 'API\ServiceController')->middleware('auth:api')->middleware('log.route');
Route::get('getServicesByStoreId/{storeId}', 'API\ServiceController@getServicesByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('getExternalServicesByStoreId/{storeId}', 'API\ServiceController@getExternalServicesByStoreId')->middleware('auth:api')->middleware('log.route');

//SignInSheets
Route::apiResource('sheet', 'API\SignInSheetController')->middleware('auth:api')->middleware('log.route');
Route::get('getSheetsByStoreId/{storeId}/{date}', 'API\SignInSheetController@getSheetsByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('getSheetByPhoneNumber/{storeId}/{phone}', 'API\SignInSheetController@getSheetByPhoneNumber')->middleware('auth:api')->middleware('log.route');

//Customers
Route::apiResource('customer', 'API\CustomerController')->middleware('auth:api')->middleware('log.route');
Route::get('getCustomersByStoreId/{storeId}', 'API\CustomerController@getCustomersByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('searchCustomerById/{storeId}/{id}', 'API\CustomerController@searchCustomerById')->middleware('auth:api')->middleware('log.route');
Route::get('searchCustomer/{storeId}/{searchKey}', 'API\CustomerController@searchCustomer')->middleware('auth:api')->middleware('log.route');
Route::get('searchCustomerByNameAndPhone/{storeId}/{fname}/{lname}/{phone}', 'API\CustomerController@searchCustomerByNameAndPhone')->middleware('auth:api')->middleware('log.route');


//Appointments
Route::apiResource('appointment', 'API\AppointmentController')->middleware('auth:api')->middleware('log.route');
Route::get('getAppointmentsByStoreId/{storeId}', 'API\AppointmentController@getAppointmentsByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('getAppointmentsByStoreIdAndDate/{storeId}/{date}', 'API\AppointmentController@getAppointmentsByStoreIdAndDate')->middleware('auth:api')->middleware('log.route');
Route::get('getAppointmentsById/{id}', 'API\AppointmentController@getAppointmentsById')->middleware('auth:api')->middleware('log.route');
Route::get('getAppointmentsByStoreIdAndEmployeeId/{storeId}/{employeeId}/{date}', 'API\AppointmentController@getAppointmentsByStoreIdAndEmployeeId')->middleware('auth:api')->middleware('log.route');
Route::get('getAppointmentsByStoreIdAndCustomerId/{storeId}/{customerId}', 'API\AppointmentController@getAppointmentsByStoreIdAndCustomerId')->middleware('auth:api')->middleware('log.route');
Route::get('getAppointmentsByStoreIdAndType/{type}/{storeId}/{data}/{date}', 'API\AppointmentController@getAppointmentsByStoreIdAndType')->middleware('auth:api')->middleware('log.route');
Route::get('getPTOByEmployeeId/{employeeId}', 'API\AppointmentController@getPTOByEmployeeId')->middleware('auth:api')->middleware('log.route');
Route::get('removeAppointment/{id}', 'API\AppointmentController@removeAppointment')->middleware('auth:api')->middleware('log.route');
Route::get('cancelAppointment/{id}', 'API\AppointmentController@cancelAppointment')->middleware('auth:api')->middleware('log.route');
Route::post('mergeCustomers', 'API\AppointmentController@mergeCustomers')->middleware('auth:api')->middleware('log.route');

// Route::get('removeEmployee/{id}', 'API\AppointmentController@removeEmployee')->middleware('auth:api');
// Route::get('restoreEmployee/{id}', 'API\AppointmentController@restoreEmployee')->middleware('auth:api');
// Route::post('updateEmployeePIN/{id}', 'API\AppointmentController@updateEmployeePIN')->middleware('auth:api');

//Payment
Route::post('makePayment', 'API\PaymentController@makePayment')->middleware('auth:api')->middleware('log.route');
Route::get('getPaymentsByStoreId/{id}', 'API\PaymentController@getPaymentsByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('getPackageByStoreId/{id}', 'API\PaymentController@getPackageByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('getInvoicesByStoreId/{id}', 'API\PaymentController@getInvoicesByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('getAllPlans', 'API\PaymentController@getAllPlans')->middleware('auth:api')->middleware('log.route');
Route::post('subscribe/{id}', 'API\PaymentController@subscribe')->middleware('auth:api')->middleware('log.route');
Route::post('setDefaultPMByStoreId/{id}', 'API\PaymentController@setDefaultPMByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('getPMByStoreId/{id}', 'API\PaymentController@getPMByStoreId')->middleware('auth:api')->middleware('log.route');
Route::get('getAllSub', 'API\PaymentController@getAllSub')->middleware('auth:api')->middleware('log.route');




//Send Email/SMS
Route::get('getAllMessagesByStorePhoneNumber', 'API\SendEmailController@getAllMessagesByStorePhoneNumber')->middleware('auth:api')->middleware('log.route');
Route::post('sendSMSReminder', 'API\SendEmailController@sendSMSReminder')->middleware('auth:api')->middleware('log.route');
Route::post('sendSMSNewConfirmation', 'API\SendEmailController@sendSMSNewConfirmation')->middleware('auth:api')->middleware('log.route');
Route::post('sendSMSReminderBulk', 'API\SendEmailController@sendSMSReminderBulk')->middleware('auth:api')->middleware('log.route');

Route::post('sendRescheduleConfirmation', 'API\SendEmailController@sendRescheduleConfirmation')->middleware('auth:api')->middleware('log.route');
Route::post('sendSMS', 'API\SendEmailController@sendSMS')->middleware('auth:api')->middleware('log.route');





Route::post('sendEmail', 'API\SendEmailController@sendEmail')->middleware('log.route');

Route::get('getListPhoneNumbers', 'API\SignInSheetController@getListPhoneNumbers')->middleware('log.route');