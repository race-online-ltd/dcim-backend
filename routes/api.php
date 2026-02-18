<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use App\Http\Controllers\User\UserLoginController;
use App\Http\Controllers\User\UserRegisterController;
use App\Http\Controllers\Auth\CustomAuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SendSmsController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\DataCenterCreationController;
use App\Http\Controllers\DataCenterController;
use App\Http\Controllers\DcOwnerMappingController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SensorListController;
use App\Http\Controllers\SensorTypeListController;
use App\Http\Controllers\ThresholdTypeController;
use App\Http\Controllers\ThresholdValueController;
use App\Http\Controllers\StateConfigController;
use App\Http\Controllers\DashboardMappingController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DashboardDataController;
use App\Http\Controllers\DiagramController;
use App\Http\Controllers\SvgController;
use App\Http\Controllers\AllDashboardController;
use App\Http\Controllers\AlarmDetailsController;
use App\Http\Controllers\RemoteDeviceController;
use App\Http\Controllers\DoConfigController;



//Route::get('/gemini-chat', [AIChatController::class, 'index'])->name('gemini.chat');
//Route::post('/gemini-chat/send', [AIChatController::class, 'sendMessage'])->name('gemini.chat.send');

// Routes for chat conversation (if you implement stateful chats)
//Route::get('/gemini-chat/start', [AIChatController::class, 'startChat'])->name('gemini.chat.start');
//Route::post('/gemini-chat/send-to-chat', [AIChatController::class, 'sendMessageToChat'])->name('gemini.chat.send.to.chat');

//Route::apiResource('diagrams', DiagramController::class);

// routes/api.php

//Route::get('/diagrams', [DiagramController::class, 'index']);
//Route::post('/diagrams', [DiagramController::class, 'store']);
// Route::get('/diagrams/{diagram}', [DiagramController::class, 'show']);


// Route::post('/diagrams', [DiagramController::class, 'store']);
// Route::get('/diagrams/{dataCenterId}', [DiagramController::class, 'index']);
//Route::get('/diagram/{id}', [DiagramController::class, 'show']);

// Route::get('/alldc', [AllDashboardController::class, 'getAllDC']);

// Route::post('/svg-upload', [SvgController::class, 'store']);
// Route::get('/svg-preview/{datacenter_id}', [SvgController::class, 'showByDataCenter']);

//NAFI AI DB

// Route::get('schema', [DatabaseController::class, 'getSchema']);

// Route::post('execute-query', [DatabaseController::class, 'executeQuery']);

// Route::get('/models-info', [DatabaseController::class, 'getModelInfo']);


// Route::get('/thresholds/by-data-center/{dataCenterId}', [DashboardDataController::class, 'getThresholdsByDataCenter']);
// Route::get('/sensor-types/by-data-center/{dataCenterId}', [DashboardDataController::class, 'getSensorTypeByDataCenter']);

// Route::get('/state/by-data-center/{dataCenterId}', [DashboardDataController::class, 'getStatesByDataCenter']);


Route::post('auth/login', [CustomAuthController::class, 'login']);


Route::get('userfetch', [UserLoginController::class, 'FetchUser']);

Route::get('/sensor-real-time/{dataCenterId}',[SensorListController::class, 'getSensorByDataCenter']);
Route::get('/device-status/by-data-center/{dataCenterIds}', [SensorListController::class, 'getDevicesByDataCenter']);

// Route::get('data-centers/mapping', [DataCenterController::class, 'getDataCenterMapping']);
// Route::get('users/mapping', [UserRegisterController::class, 'getUserMapping']);
// Route::get('partner/mapping', [MasterDataController::class, 'getPartnerMapping']);
// Route::post('dc-partner-mappings', [DcOwnerMappingController::class, 'storeDcPartnerMapping']);
// Route::post('dc-owner-mappings', [DcOwnerMappingController::class, 'store']);


// Master Data Routes
// Route::prefix('master-data')->group(function () {
//     Route::get('divisions', [MasterDataController::class, 'FetchDivisions']);
//     Route::get('user-types', [MasterDataController::class, 'FetchUserType']);
//     Route::get('user-roles', [MasterDataController::class, 'FetchUserRole']);
//     Route::get('user-department', [MasterDataController::class, 'FetchDepartments']);
//     Route::get('owner-type', [MasterDataController::class, 'FetchOwnerTypes']);


//     //    Route::apiResource('partner-lists', \App\Http\Controllers\PartnerListController::class);

//     //    Route::apiResource('partner-lists', MasterDataController::class)
//     //        ->names([
//     //            'index' => 'partner-lists.indexPartner',
//     //            'store' => 'partner-lists.storePartner',
//     //            'show' => 'partner-lists.showPartner',
//     //            'update' => 'partner-lists.updatePartner',
//     //            'destroy' => 'partner-lists.destroyPartner'
//     //        ]);

//     Route::get('partner-lists', [MasterDataController::class, 'indexPartner']);
//     Route::post('partner-lists', [MasterDataController::class, 'storePartner']);
//     Route::get('partner-lists/{partnerList}', [MasterDataController::class, 'showPartner']);
//     Route::put('partner-lists/{partnerList}', [MasterDataController::class, 'updatePartner']);
//     Route::delete('partner-lists/{partnerList}', [MasterDataController::class, 'destroyPartner']);
// });

// Route::prefix('/user')->group(function () {
//     Route::get('users', [UserRegisterController::class, 'index']);
//     Route::get('users/{id}', [UserRegisterController::class, 'show']);
//     Route::put('users/{id}', [UserRegisterController::class, 'update']);
//     Route::delete('users/{id}', [UserRegisterController::class, 'destroy']);
//     Route::post('userregister', [UserRegisterController::class, 'Register']);
//     //    Route::post('change-password', [UserLoginController::class, 'changePassword']);
// });

// // Data Center
// Route::prefix('data-center')->group(function () {
//     Route::get('/user/{userId}', [DataCenterController::class, 'getUserDataCenters']);
//     Route::get('/tab/{tabId}', [DataCenterController::class, 'getTabDataCenters']);

// });


// End Data center

// Route::prefix('devices')->group(function () {
//     Route::get('/', [DeviceController::class, 'index']);
//     Route::post('/', [DeviceController::class, 'store']);
//     Route::get('/{id}', [DeviceController::class, 'show']);
//     Route::put('/{id}', [DeviceController::class, 'update']);
//     Route::delete('/{id}', [DeviceController::class, 'destroy']);
// //    Route::get('/data-centers', [DeviceController::class, 'getDataCenters']);

//     Route::get('/by-data-center/{dataCenterId}', [DeviceController::class, 'getByDataCenter']);
// });


// Route::prefix('sensor-lists')->group(function () {
//     Route::get('/', [SensorListController::class, 'index']);
//     Route::post('/', [SensorListController::class, 'store']);
//     Route::get('/{id}', [SensorListController::class, 'show']);
//     Route::put('/{id}', [SensorListController::class, 'update']);
//     Route::delete('/{id}', [SensorListController::class, 'destroy']);

// //    Route::get('/fetch-sensortype-list', [SensorTypeListController::class, 'fetchSensorTypeList']);

//     Route::get('/by-device/{deviceId}', [SensorListController::class, 'getByDevice']);

// });

// Route::get('sensor-type-lists', [SensorTypeListController::class, 'index']);
// Route::get('trigger-type-lists', [SensorTypeListController::class, 'indexTrigger']);

// //Route::get('/settings/data-centers', [DataCenterController::class, 'index']);
// Route::get('/data-centers/{id}', [DataCenterController::class, 'show']);
// Route::put('/data-centers/{id}', [DataCenterController::class, 'update']);
// Route::delete('/data-centers/{id}', [DataCenterController::class, 'destroy']);
// Route::get('/settings/data-centers', [DataCenterController::class, 'index']);
// Route::get('/data-centers/{id}', [DataCenterController::class, 'show']);
// Route::put('/data-centers/{id}', [DataCenterController::class, 'update']);
// Route::delete('/data-centers/{id}', [DataCenterController::class, 'destroy']);

// Route::post('/data-centers', [DataCenterCreationController::class, 'store']);


// Route::get('threshold-types', [ThresholdTypeController::class, 'index']);         // List all threshold types
// Route::post('threshold-types', [ThresholdTypeController::class, 'store']);         // Create a new threshold type
// Route::get('threshold-types/{threshold_type}', [ThresholdTypeController::class, 'show']); // Show a single threshold type
// Route::put('threshold-types/{threshold_type}', [ThresholdTypeController::class, 'update']); // Update a threshold type
// Route::patch('threshold-types/{threshold_type}', [ThresholdTypeController::class, 'update']); // Partial update
// Route::delete('threshold-types/{threshold_type}', [ThresholdTypeController::class, 'destroy']);

// Route::prefix('threshold-values')->group(function () {
//     Route::get('/', [ThresholdValueController::class, 'index']);
//     Route::post('/', [ThresholdValueController::class, 'store']);
//     Route::get('/{thresholdValue}', [ThresholdValueController::class, 'show']);
//     Route::put('/{thresholdValue}', [ThresholdValueController::class, 'update']);
//     Route::delete('/{thresholdValue}', [ThresholdValueController::class, 'destroy']);
// });


// Route::prefix('state-configs')->group(function () {
//     Route::get('/', [StateConfigController::class, 'index']);
//     Route::post('/', [StateConfigController::class, 'store']);
//     Route::get('/{stateConfig}', [StateConfigController::class, 'show']);
//     Route::put('/{stateConfig}', [StateConfigController::class, 'update']);
//     Route::delete('/{stateConfig}', [StateConfigController::class, 'destroy']);
// });

// Route::prefix('tab')->group(function () {
//     Route::get('/dashboard-tabs', [DashboardMappingController::class, 'index']);
//     Route::post('/tab-mappings', [DashboardMappingController::class, 'store']);
//     Route::get('/tab-mappings', [DashboardMappingController::class, 'getMappings']);
// });



//Route::middleware('auth:api')->group(function () {
//    Route::get('/menu', [MenuController::class, 'getUserMenu']);
//});


Route::post('/control-led', [RemoteDeviceController::class, 'controlLed']);


Route::post('/sensor-control', [RemoteDeviceController::class, 'controlSensor']);

Route::post('/controllable-sensors', [DoConfigController::class, 'doSensorLists']);
Route::get('/operation-modes', [DoConfigController::class, 'operationMode']);
Route::get('/operation-schedulling', [DoConfigController::class, 'schedullingList']);
Route::get('/operation-repeat', [DoConfigController::class, 'repeatList']);
Route::post('/store-control-configurations', [DoConfigController::class, 'storeOperationTrigger']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('roles', RoleController::class);
    //    Route::resource('users', UserController::class);
    //    Route::resource('products', ProductController::class);

    Route::get('/user-permissions', [UserController::class, 'getPermissions'])->middleware('can:users')->name('users');
    // Route::apiResource('divisions', DivisionController::class);

    Route::get('/settings/divisions', [DivisionController::class, 'index']);
    Route::post('/settings/divisions', [DivisionController::class, 'store']);



    Route::get('/settings/data-centers', [DataCenterController::class, 'index'])->name('datacenter-index');

    Route::prefix('/auth')->group(function () {
    //        Route::get('data-centers/mapping', [DataCenterController::class, 'getDataCenterMapping'])->middleware('can:getDataCenterMapping')->name('getDataCenterMapping');
    //        Route::get('users/mapping', [UserRegisterController::class, 'getUserMapping'])->middleware('can:getUserMapping')->name('getUserMapping');
    //        Route::get('partner/mapping', [MasterDataController::class, 'getPartnerMapping'])->middleware('can:getPartnerMapping')->name('getPartnerMapping');
    //        Route::post('dc-partner-mappings', [DcOwnerMappingController::class, 'storeDcPartnerMapping'])->middleware('can:storeDcPartnerMapping')->name('storeDcPartnerMapping');
    //        Route::post('dc-owner-mappings', [DcOwnerMappingController::class, 'store'])->middleware('can:dc_owner_mappings_store')->name('dc_owner_mappings_store');
        Route::post('/logout', [CustomAuthController::class, 'logout'])->middleware('can:logout1')->name('logout1');
    //        Route::get('/settings/data-centers', [DataCenterController::class, 'index'])->name('datacenter-index');
        Route::get('/user', function (Request $request) {
            return $request->user();
        });



    //        Route::get('/menu', [MenuController::class, 'getUserMenu'])->middleware('can:getUserMenu')->name('getUserMenu');
    });
    // Route::get('/settings/data-centers', [DataCenterController::class, 'index'])->name('datacenter-index');

        Route::get('/data-centers/{id}', [DataCenterController::class, 'show'])->middleware('can:datacenter-show')->name('datacenter-show');
        Route::put('/data-centers/{id}', [DataCenterController::class, 'update'])->middleware('can:datacenter-edit')->name('datacenter-edit');
        Route::delete('/data-centers/{id}', [DataCenterController::class, 'destroy'])->middleware('can:datacenter-delete')->name('datacenter-delete');
        Route::post('/data-centers', [DataCenterCreationController::class, 'store'])->middleware('can:datacenter-add')->name('datacenter-add');


    Route::prefix('devices')->group(function () {
        Route::get('/', [DeviceController::class, 'index'])->middleware('can:device-index')->name('device-index');
        Route::post('/', [DeviceController::class, 'store'])->middleware('can:device-create')->name('device-create');
        Route::get('/{id}', [DeviceController::class, 'show'])->middleware('can:device-show')->name('device-show');
        Route::put('/{id}', [DeviceController::class, 'update'])->middleware('can:device-edit')->name('device-edit');
        Route::delete('/{id}', [DeviceController::class, 'destroy'])->middleware('can:device-delete')->name('device-delete');
        Route::get('/by-data-center/{dataCenterId}', [DeviceController::class, 'getByDataCenter']);
            // Route::get('/data-centers', [DeviceController::class, 'getDataCenters'])->middleware('can:datacenter-show')->name('datacenter-show');

        });
        Route::prefix('sensor-lists')->group(function () {
            Route::get('/', [SensorListController::class, 'index'])->middleware('can:sensor-index')->name('sensor-index');
            Route::post('/', [SensorListController::class, 'store'])->middleware('can:sensor-create')->name('sensor-create');
            Route::get('/{id}', [SensorListController::class, 'show'])->middleware('can:sensor-show')->name('sensor-show');
            Route::put('/{id}', [SensorListController::class, 'update'])->middleware('can:sensor-edit')->name('sensor-edit');
            Route::delete('/{id}', [SensorListController::class, 'destroy'])->middleware('can:sensor-delete')->name('sensor-delete');

        //    Route::get('/fetch-sensortype-list', [SensorTypeListController::class, 'fetchSensorTypeList']);

            Route::get('/by-device/{deviceId}/{sensorTypeId}', [SensorListController::class, 'getByDevice']);

        });
        Route::prefix('state-configs')->group(function () {
            Route::get('/', [StateConfigController::class, 'index'])->middleware('can:state-index')->name('state-index');
            Route::post('/', [StateConfigController::class, 'store'])->middleware('can:state-create')->name('state-create');
            Route::get('/{stateConfig}', [StateConfigController::class, 'show'])->middleware('can:state-show')->name('state-show');
            Route::put('/{stateConfig}', [StateConfigController::class, 'update'])->middleware('can:state-edit')->name('state-edit');
            Route::delete('/{stateConfig}', [StateConfigController::class, 'destroy'])->middleware('can:state-delete')->name('state-delete');
        });


        Route::get('threshold-types', [ThresholdTypeController::class, 'index'])->middleware('can:thresholdtype-index')->name('thresholdtype-index');
        Route::post('threshold-types', [ThresholdTypeController::class, 'store'])->middleware('can:thresholdtype-create')->name('thresholdtype-create');
        Route::get('threshold-types/{threshold_type}', [ThresholdTypeController::class, 'show'])->middleware('can:thresholdtype-show')->name('thresholdtype-show');
        Route::put('threshold-types/{threshold_type}', [ThresholdTypeController::class, 'update'])->middleware('can:thresholdtype-edit')->name('thresholdtype-edit');
        Route::patch('threshold-types/{threshold_type}', [ThresholdTypeController::class, 'update'])->middleware('can:thresholdtype-update')->name('thresholdtype-update');
        Route::delete('threshold-types/{threshold_type}', [ThresholdTypeController::class, 'destroy'])->middleware('can:thresholdtype-delete')->name('thresholdtype-delete');

        Route::prefix('threshold-values')->group(function () {
            Route::get('/', [ThresholdValueController::class, 'index'])->middleware('can:thresholdvalue-index')->name('thresholdvalue-index');
            Route::post('/', [ThresholdValueController::class, 'store'])->middleware('can:thresholdvalue-create')->name('thresholdvalue-create');
            Route::get('/{thresholdValue}', [ThresholdValueController::class, 'show'])->middleware('can:thresholdvalue-show')->name('thresholdvalue-show');
            Route::put('/{thresholdValue}', [ThresholdValueController::class, 'update'])->middleware('can:thresholdvalue-edit')->name('thresholdvalue-edit');
            Route::delete('/{thresholdValue}', [ThresholdValueController::class, 'destroy'])->middleware('can:thresholdvalue-delete')->name('thresholdvalue-delete');
        });
        Route::prefix('tab')->group(function () {
            Route::get('/dashboard-tabs', [DashboardMappingController::class, 'index']);
            Route::post('/tab-mappings', [DashboardMappingController::class, 'store']);
            Route::get('/tab-mappings', [DashboardMappingController::class, 'getMappings']);
        });

    Route::get('/diagrams/{diagram}', [DiagramController::class, 'show'])->middleware('can:DiagramController-show')->name('DiagramController-show');
    Route::post('/diagrams', [DiagramController::class, 'store'])->middleware('can:DiagramController-store')->name('DiagramController-store');
    Route::get('/diagrams/{dataCenterId}', [DiagramController::class, 'index'])->middleware('can:DiagramController-index')->name('DiagramController-index');

    Route::post('/svg-upload', [SvgController::class, 'store'])->middleware('can:SvgController-store')->name('SvgController-store');
    Route::get('/svg-preview/{datacenter_id}', [SvgController::class, 'showByDataCenter'])->middleware('can:SvgController-preview')->name('SvgController-preview');



    //NAFI AI DB
    Route::get('schema', [DatabaseController::class, 'getSchema'])->middleware('can:DatabaseController-getSchema')->name('DatabaseController-getSchema');
    Route::post('execute-query', [DatabaseController::class, 'executeQuery'])->middleware('can:DatabaseController-executeQuery')->name('DatabaseController-executeQuery');
    Route::get('/models-info', [DatabaseController::class, 'getModelInfo'])->middleware('can:DatabaseController-getModelInfo')->name('DatabaseController-getModelInfo');

    Route::get('/thresholds/by-data-center/{dataCenterId}', [DashboardDataController::class, 'getThresholdsByDataCenter'])->middleware('can:DashboardDataController-getThresholdsByDataCenter')->name('DashboardDataController-getThresholdsByDataCenter');
    Route::get('/sensor-types/by-data-center/{dataCenterId}', [DashboardDataController::class, 'getSensorTypeByDataCenter'])->middleware('can:DashboardDataController-getSensorTypeByDataCenter')->name('DashboardDataController-getSensorTypeByDataCenter');
    Route::get('/state/by-data-center/{dataCenterId}', [DashboardDataController::class, 'getStatesByDataCenter'])->middleware('can:DashboardDataController-getStatesByDataCenter')->name('DashboardDataController-getStatesByDataCenter');

    Route::get('/user-wise/data-centers/mapping', [DataCenterController::class, 'getDataCenterMapping'])->middleware('can:DataCenterController-getDataCenterMapping')->name('DataCenterController-getDataCenterMapping');

    Route::get('users/mapping', [UserRegisterController::class, 'getUserMapping'])->middleware('can:UserRegisterController-getUserMapping')->name('UserRegisterController-getUserMapping');

    Route::get('partner/mapping', [MasterDataController::class, 'getPartnerMapping'])->middleware('can:MasterDataController-getPartnerMapping')->name('MasterDataController-getPartnerMapping');

    Route::post('dc-partner-mappings', [DcOwnerMappingController::class, 'storeDcPartnerMapping'])->middleware('can:DcOwnerMappingController-storeDcPartnerMapping')->name('DcOwnerMappingController-storeDcPartnerMapping');
    Route::post('dc-owner-mappings', [DcOwnerMappingController::class, 'store'])->middleware('can:DcOwnerMappingController-store')->name('DcOwnerMappingController-store');


    Route::prefix('master-data')->group(function () {
    Route::get('divisions', [MasterDataController::class, 'FetchDivisions'])->middleware('can:MasterDataController-FetchDivisions')->name('MasterDataController-FetchDivisions');
    Route::get('user-types', [MasterDataController::class, 'FetchUserType'])->middleware('can:MasterDataController-FetchUserType')->name('MasterDataController-FetchUserType');
    Route::get('user-roles', [MasterDataController::class, 'FetchUserRole'])->middleware('can:MasterDataController-FetchUserRole')->name('MasterDataController-FetchUserRole');
    Route::get('user-department', [MasterDataController::class, 'FetchDepartments'])->middleware('can:MasterDataController-FetchDepartments')->name('MasterDataController-FetchDepartments');
    Route::get('owner-type', [MasterDataController::class, 'FetchOwnerTypes'])->middleware('can:MasterDataController-FetchOwnerTypes')->name('MasterDataController-FetchOwnerTypes');


    Route::get('partner-lists', [MasterDataController::class, 'indexPartner'])->middleware('can:MasterDataController-indexPartner')->name('MasterDataController-indexPartner');
    Route::post('partner-lists', [MasterDataController::class, 'storePartner'])->middleware('can:MasterDataController-storePartner')->name('MasterDataController-storePartner');
    Route::get('partner-lists/{partnerList}', [MasterDataController::class, 'showPartner'])->middleware('can:MasterDataController-showPartner')->name('MasterDataController-showPartner');
    Route::put('partner-lists/{partnerList}', [MasterDataController::class, 'updatePartner'])->middleware('can:MasterDataController-updatePartner')->name('MasterDataController-updatePartner');
    Route::delete('partner-lists/{partnerList}', [MasterDataController::class, 'destroyPartner'])->middleware('can:MasterDataController-destroyPartner')->name('MasterDataController-destroyPartner');
    });

    Route::prefix('/user')->group(function () {
        Route::get('users', [UserRegisterController::class, 'index'])->middleware('can:UserRegisterController-index')->name('UserRegisterController-index');
        Route::get('users/{id}', [UserRegisterController::class, 'show'])->middleware('can:UserRegisterController-show')->name('UserRegisterController-show');
        Route::put('users/{id}', [UserRegisterController::class, 'update'])->middleware('can:UserRegisterController-update')->name('UserRegisterController-indexupdatePartner');
        Route::delete('users/{id}', [UserRegisterController::class, 'destroy'])->middleware('can:UserRegisterController-destroy')->name('UserRegisterController-destroy');
        Route::post('userregister', [UserRegisterController::class, 'Register'])->middleware('can:UserRegisterController-Register')->name('UserRegisterController-Register');
    //    Route::post('change-password', [UserLoginController::class, 'changePassword']);
    });

    // Data Center
    Route::prefix('data-center')->group(function () {
        Route::get('/user/{userId}', [DataCenterController::class, 'getUserDataCenters'])->middleware('can:DataCenterController-getUserDataCenters')->name('DataCenterController-getUserDataCenters');
        Route::get('/tab/{tabId}', [DataCenterController::class, 'getTabDataCenters'])->middleware('can:DataCenterController-getTabDataCenters')->name('DataCenterController-getTabDataCenters');

        Route::get('/alldc', [AllDashboardController::class, 'getAllDC'])->middleware('can:AllDashboardController-getAllDC')->name('AllDashboardController-getAllDC');

    });


    Route::get('sensor-type-lists', [SensorTypeListController::class, 'index'])->middleware('can:SensorTypeListController-index')->name('SensorTypeListController-index');
    Route::get('trigger-type-lists', [SensorTypeListController::class, 'indexTrigger'])->middleware('can:SensorTypeListController-indexTrigger')->name('SensorTypeListController-indexTrigger');

    //Route::get('/settings/data-centers', [DataCenterController::class, 'index']);
    Route::get('/data-centers/{id}', [DataCenterController::class, 'show'])->middleware('can:DataCenterController-show')->name('DataCenterController-show');
    Route::put('/data-centers/{id}', [DataCenterController::class, 'update'])->middleware('can:DataCenterController-update')->name('DataCenterController-update');
    Route::delete('/data-centers/{id}', [DataCenterController::class, 'destroy'])->middleware('can:DataCenterController-destroy')->name('DataCenterController-destroy');


});


Route::prefix('alarm')->group(function () {
    Route::post('/sensor-details', [AllDashboardController::class, 'getrDataCenterAlarmDetails']);
    Route::post('/store', [AlarmDetailsController::class, 'acknowledgementStore']);


});




require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
