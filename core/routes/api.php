<?php

use App\Http\Controllers\markets\AuthMarketController;
use App\Http\Controllers\markets\AuthTenantController as MarketsAuthTenantController;
use App\Http\Controllers\up\AuthController;
use App\Http\Controllers\up\v1\UpAdditionalInformationController;
use App\Http\Controllers\up\v1\UpAreasController;
use App\Http\Controllers\up\v1\UpCitiesController;
use App\Http\Controllers\up\v1\UpCountriesController;
use App\Http\Controllers\up\v1\UpLanguagesController;
use App\Http\Controllers\up\v1\UpPlanController;
use App\Http\Controllers\up\v1\UpPricingModelsController;
use App\Http\Controllers\up\v1\UpSectorsController;
use App\Http\Controllers\up\v1\UpServiceCategoryController;
use App\Http\Controllers\up\v1\UpServicesController;
use App\Http\Controllers\up\v1\UpUserController;
use App\Http\Controllers\up\v1\UpVendorsController;
use App\Http\Controllers\vendors\AuthTenantController;
use App\Http\Controllers\vendors\AuthVendorController;
use App\Http\Controllers\vendors\v1\VeCountriesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    // Route::post('register', 'register');
    Route::post('logout', 'logout');
    //Route::post('refresh', 'refresh');
});

Route::group(['prefix' => 'v1', 'middleware' => 'auth'], function () {
    Route::get('countries/list', UpCountriesController::class . '@index');
    Route::post('countries/add', UpCountriesController::class . '@add');
    Route::post('countries/edit', UpCountriesController::class . '@edit');
    Route::delete('countries/destroy', UpCountriesController::class . '@destroy');

    Route::get('cities/list', UpCitiesController::class . '@index');
    Route::post('cities/edit', UpCitiesController::class . '@edit');
    Route::post('cities/add', UpCitiesController::class . '@add');
    Route::delete('cities/destroy', UpCitiesController::class . '@destroy');

    Route::get('areas/list', UpAreasController::class . '@index');
    Route::post('areas/add', UpAreasController::class . '@add');
    Route::post('areas/edit', UpAreasController::class . '@edit');
    Route::delete('areas/destroy', UpAreasController::class . '@destroy');

    Route::get('pricing-models/list', UpPricingModelsController::class . '@index');
    Route::post('pricing-models/add', UpPricingModelsController::class . '@add');
    Route::post('pricing-models/edit', UpPricingModelsController::class . '@edit');
    Route::delete('pricing-models/destroy', UpPricingModelsController::class . '@destroy');
    Route::get('pricing-models/dropdown', UpPricingModelsController::class . '@dropdown');

    Route::post('sectors/add', UpSectorsController::class . '@add');
    Route::post('sectors/edit', UpSectorsController::class . '@edit');
    Route::delete('sectors/destroy', UpSectorsController::class . '@destroy');
    Route::get('sectors/dropdown', UpSectorsController::class . '@dropdown');
    Route::get('sectors/list', UpSectorsController::class . '@index');

    Route::get('services/list', UpServicesController::class . '@index');
    Route::post('services/add', UpServicesController::class . '@add');
    Route::post('services/edit', UpServicesController::class . '@edit');
    Route::delete('services/destroy', UpServicesController::class . '@destroy');

    Route::get('service-categories/list', UpServiceCategoryController::class . '@index');
    Route::post('service-categories/add', UpServiceCategoryController::class . '@store');
    Route::post('service-categories/edit', UpServiceCategoryController::class . '@update');
    Route::delete('service-categories/destroy', UpServiceCategoryController::class . '@destroy');

    Route::get('vendors/list', UpVendorsController::class . '@index');
    Route::post('vendors/add', UpVendorsController::class . '@store');
    Route::post('vendors/edit', UpVendorsController::class . '@edit');
    Route::post('vendors/show', UpVendorsController::class . '@show');
    Route::post('vendors/count', UpVendorsController::class . '@get_count');
    Route::post('vendors/areas/covered', UpVendorsController::class . '@get_areas_covered');
    Route::post('vendors/offdays/list', UpVendorsController::class . '@get_vendor_offdays');
    Route::post('vendors/working_schedule', UpVendorsController::class . '@get_vendor_working_schedule');
    Route::post('vendors/statistics', UpVendorsController::class . '@get_vendor_statistics');
    Route::post('vendors/update_subscription', UpVendorsController::class . '@update_subscription');

    Route::get('users/list', [UpUserController::class, 'index']);

    Route::get('additional/list', UpAdditionalInformationController::class . '@index');
    Route::post('additional/show', UpAdditionalInformationController::class . '@getById');

    //    Route::post('media/upload', VeMediaController::class . '@upload');
    //    Route::post('media/list', VeMediaController::class . '@list');
    //    Route::post('media/upload', VeMediaController::class . '@upload');
});

Route::group(['prefix' => 'v1'], function () {
    Route::post('sendOtp', [AuthVendorController::class, 'sendOtp']);
    Route::post('verifyOtp', [AuthVendorController::class, 'verifyOtp']);

    Route::get('countries/list', VeCountriesController::class . '@dropdown');
    Route::post('vendors/getSubdomain', UpVendorsController::class . '@getSubdomain');
    Route::post('vendors', [AuthTenantController::class, 'register']);
    Route::post('vendors/complete', [AuthTenantController::class, 'completeRegistration']);
    Route::post('login', [AuthVendorController::class, 'login']);
    Route::post('vendor_login', [AuthVendorController::class, 'login']);
    Route::post('register/edit', [AuthVendorController::class, 'editPhone']);
    Route::post('get_sub', [AuthVendorController::class, 'get_sub_domain']);

    Route::get('languages/list', UpLanguagesController::class . '@index');
    Route::post('languages/show', UpLanguagesController::class . '@getLanguageById');

    Route::post('generate_token', [AuthTenantController::class, 'generate_token']);
    Route::get('sectors/list', UpSectorsController::class . '@index');
    Route::get('active-sectors/list', UpSectorsController::class . '@list_register');
    Route::get('plans/list', UpPlanController::class . '@index');
});

Route::group(['prefix' => 'market/v1'], function () {
    Route::post('sendOtp', [AuthMarketController::class, 'sendOtp']);
    Route::post('verifyOtp', [AuthMarketController::class, 'verifyOtp']);

    Route::get('countries/list', VeCountriesController::class . '@dropdown');
    Route::post('markets/getSubdomain', UpVendorsController::class . '@getSubdomain');
    Route::post('markets', [MarketsAuthTenantController::class, 'register']);
    Route::post('markets/complete', [MarketsAuthTenantController::class, 'completeRegistration']);
    Route::post('market_login', [AuthMarketController::class, 'login']);
    Route::post('register/edit', [AuthMarketController::class, 'editPhone']);
    Route::post('get_sub', [AuthMarketController::class, 'get_sub_domain']);

    Route::get('languages/list', UpLanguagesController::class . '@index');
    Route::post('languages/show', UpLanguagesController::class . '@getLanguageById');

    Route::post('generate_token', [MarketsAuthTenantController::class, 'generate_token']);
    Route::get('sectors/list', UpSectorsController::class . '@index');
    Route::get('active-sectors/list', UpSectorsController::class . '@list_register');
    Route::get('plans/list', UpPlanController::class . '@index');
});
