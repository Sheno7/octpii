<?php

declare(strict_types=1);

use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\SectorController;
use App\Http\Controllers\Customer\ServiceController;
use App\Http\Controllers\markets\AuthCustomerController as MarketsAuthCustomerController;
use App\Http\Controllers\markets\AuthTenantController as MarketsAuthTenantController;
use App\Http\Controllers\markets\v1\MaBookingController;
use App\Http\Controllers\markets\v1\MaBookingDashboardController;
use App\Http\Controllers\markets\v1\MaCitiesController;
use App\Http\Controllers\markets\v1\MaCustomersController;
use App\Http\Controllers\markets\v1\MaMatcherController;
use App\Http\Controllers\markets\v1\MaServiceCategoryController;
use App\Http\Controllers\markets\v1\MaServiceController;
use App\Http\Controllers\markets\v1\MaTransactionsController;
use App\Http\Controllers\markets\v1\MaVendorController;
use App\Http\Controllers\MobileNotificationController;
use App\Http\Controllers\Provider\BookingController as ProviderBookingController;
use App\Http\Controllers\Provider\FinancialController;
use App\Http\Controllers\Provider\WorkingScheduleController;
use App\Http\Controllers\up\v1\UpAreasController;
use App\Http\Controllers\up\v1\UpCitiesController;
use App\Http\Controllers\up\v1\UpServiceCategoryController;
use App\Http\Controllers\vendors\AuthCustomerController;
use App\Http\Controllers\vendors\AuthProviderController;
use App\Http\Controllers\vendors\AuthTenantController;
use App\Http\Controllers\vendors\v1\VeAdjustmentController;
use App\Http\Controllers\vendors\v1\VeAreasController;
use App\Http\Controllers\vendors\v1\VeBookingController;
use App\Http\Controllers\vendors\v1\VeBranchController;
use App\Http\Controllers\vendors\v1\VeCancellationReasonController;
use App\Http\Controllers\vendors\v1\VeCitiesContrlller;
use App\Http\Controllers\vendors\v1\VeCountriesController;
use App\Http\Controllers\vendors\v1\VeCustomersController;
use App\Http\Controllers\vendors\v1\VeDashboardController;
use App\Http\Controllers\vendors\v1\VeExpenseCategoryController;
use App\Http\Controllers\vendors\v1\VeExpenseController;
use App\Http\Controllers\vendors\v1\VeFinancialController;
use App\Http\Controllers\vendors\v1\VeInsightsController;
use App\Http\Controllers\vendors\v1\VeMarketController;
use App\Http\Controllers\vendors\v1\VeMatcherController;
use App\Http\Controllers\vendors\v1\VeMediaController;
use App\Http\Controllers\vendors\v1\VeMonitorController;
use App\Http\Controllers\vendors\v1\VePackagesController;
use App\Http\Controllers\vendors\v1\VePricingModelsController;
use App\Http\Controllers\vendors\v1\VeProcurementController;
use App\Http\Controllers\vendors\v1\VeProductCategoryController;
use App\Http\Controllers\vendors\v1\VeProductController;
use App\Http\Controllers\vendors\v1\VeProvidersController;
use App\Http\Controllers\vendors\v1\VeSechduleController;
use App\Http\Controllers\vendors\v1\VeServicesController;
use App\Http\Controllers\vendors\v1\VeSettingsController;
use App\Http\Controllers\vendors\v1\VeTransactionsController;
use App\Http\Controllers\vendors\v1\VeUserController;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'api',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
    'auth',
])->prefix('api/ve/v1')->group(function () {
    Route::get('customers/list', VeCustomersController::class . '@index');
    Route::post('customers/show', VeCustomersController::class . '@show');
    Route::post('customers/favorite', VeCustomersController::class . '@favorite');
    Route::post('customers/favoriteList', VeCustomersController::class . '@favoriteList');
    Route::post('customers/block', VeCustomersController::class . '@block');
    Route::post('customers/blacklistList', VeCustomersController::class . '@blacklistList');
    Route::post('customers/address/list', VeCustomersController::class . '@listAddress');
    Route::post('customers/address/add', VeCustomersController::class . '@addAddress');
    Route::post('customers/address/edit', VeCustomersController::class . '@editAddress');
    Route::post('customers/address/delete', VeCustomersController::class . '@deleteAddress');
    Route::post('customers/add', VeCustomersController::class . '@add');
    Route::post('customers/edit', VeCustomersController::class . '@edit');
    Route::get('customers/dropdown', VeCustomersController::class . '@dropdown');

    Route::post('customers/dental/show', VeCustomersController::class . '@show_dental');
    Route::post('customers/dental/update', VeCustomersController::class . '@update_dental');

    Route::post('customers/xray/list', VeCustomersController::class . '@listXray');
    Route::post('customers/xray/upload', VeCustomersController::class . '@uploadXray');
    Route::post('customers/xray/delete', VeCustomersController::class . '@deleteXray');
    Route::get('providers/list', VeProvidersController::class . '@index');
    Route::get('providers/dropdown', VeProvidersController::class . '@dropdown');
    Route::post('providers/show', VeProvidersController::class . '@show');
    Route::post('providers/add', VeProvidersController::class . '@add');
    Route::post('providers/edit', VeProvidersController::class . '@edit');
    Route::post('providers/listAreasCoverd', VeProvidersController::class . '@listAreasCoverd');
    Route::post('providers/addAndRemoveArea', VeProvidersController::class . '@addAndRemoveArea');
    Route::post('providers/listServiceArea', VeProvidersController::class . '@ListServiceCoveredProviders');
    Route::post('providers/addAndRemoveService', VeProvidersController::class . '@addAndRemoveService');
    Route::post('providers/bookings/list', VeProvidersController::class . '@list_bookings');
    Route::post('providers/schedule/list', VeProvidersController::class . '@list_schedule');
    Route::post('providers/schedule/edit', VeProvidersController::class . '@edit_schedule');
    Route::post('providers/offdays/list', VeProvidersController::class . '@list_off_days');
    Route::post('providers/offdays/edit', VeProvidersController::class . '@add_edit_off_day');
    Route::post('providers/offdays/destroy', VeProvidersController::class . '@destroy_off_day');

    Route::post('countries/edit', VeCountriesController::class . '@edit');
    Route::get('cities/list', VeCitiesContrlller::class . '@list');
    Route::post('cities/edit', VeCitiesContrlller::class . '@change_status');
    Route::get('areas/list', VeAreasController::class . '@index');
    Route::get('areas/covered', VeAreasController::class . '@areas_covered');
    Route::post('areas/edit', VeAreasController::class . '@changeStatus');

    Route::get('services/dropdown', VeServicesController::class . '@dropdown')
        ->middleware([Authorize::using('services.dropdown')]);
    Route::get('services/list', VeServicesController::class . '@index')
        ->middleware([Authorize::using('services.index')]);
    Route::post('services/add', VeServicesController::class . '@add')
        ->middleware([Authorize::using('services.store')]);
    Route::post('services/edit', VeServicesController::class . '@change_status')
        ->middleware([Authorize::using('services.update')]);
    Route::delete('services/destroy', VeServicesController::class . '@remove_service_offered')
        ->middleware([Authorize::using('services.destroy')]);
    Route::get('services/trashed', VeServicesController::class . '@list_with_trash')
        ->middleware([Authorize::using('services.index')]);
    Route::post('services/show', VeServicesController::class . '@show')
        ->middleware([Authorize::using('services.show')]);

    Route::get('pricing-models/list', VePricingModelsController::class . '@dropdown');
    Route::post('wizard', VeDashboardController::class . '@wizard');
    Route::post('wizard/status', VeDashboardController::class . '@get_status');

    Route::get('setting/additional', VeDashboardController::class . '@get_additional_info')
        ->middleware([Authorize::using('setting.index')]);
    Route::get('setting/setting', VeDashboardController::class . '@get_setting')
        ->middleware([Authorize::using('setting.index')]);
    Route::get('setting/schedule', VeSettingsController::class . '@get_working_schedule')
        ->middleware([Authorize::using('schedule.index')]);
    Route::post('setting/schedule', VeSettingsController::class . '@update_working_schedule')
        ->middleware([Authorize::using('schedule.update')]);

    Route::post('setting/split', VeDashboardController::class . '@split_setting');

    Route::get('offdays/list', VeSechduleController::class . '@list_offDays');
    Route::post('offdays/add', VeSechduleController::class . '@add_offDays');
    Route::post('offdays/edit', VeSechduleController::class . '@edit_offDays');
    Route::post('offdays/destroy', VeSechduleController::class . '@delete_offDays');

    Route::get('matchers/list', VeMatcherController::class . '@index');
    Route::get('matcher/provider/edit', VeMatcherController::class . '@checkAvailabilityOneProvider');
    Route::post('matcher/provider/add', VeMatcherController::class . '@addProvider');
    Route::post('matcher/provider/edit', VeMatcherController::class . '@editProvider');
    Route::delete('matcher/provider/delete', VeMatcherController::class . '@deleteProvider');
    Route::delete('matcher/delete_rows', VeMatcherController::class . '@deleterows');
    Route::post('matchers/provider', VeMatcherController::class . '@provider_update_availability');
    Route::post('matcher/service_offered', VeMatcherController::class . '@filterServiceOffer');
    Route::post('matcher/filter/service', VeMatcherController::class . '@filter_service_offer');
    Route::post('matcher/check_availability', VeMatcherController::class . '@check_availability');
    Route::post('matcher/get_workload', VeMatcherController::class . '@get_workload');
    Route::post('matcher/filter_avalibility', VeMatcherController::class . '@filter_avalibility');
    Route::post('matcher/service_offered', VeMatcherController::class . '@filterServiceOffer');

    Route::get('booking/list', VeBookingController::class . '@index');
    Route::get('booking/list/canceled', VeBookingController::class . '@list_canceled');
    Route::post('booking/add', VeBookingController::class . '@add');
    Route::post('booking/edit', VeBookingController::class . '@edit');
    Route::post('booking/show', VeBookingController::class . '@show');
    Route::post('booking/cancel', VeBookingController::class . '@cancel');
    Route::post('booking/status/update', VeBookingController::class . '@update_status');
    Route::post('booking/customer', VeBookingController::class . '@listCustomerBookings');

    Route::post('booking/complete', VeBookingController::class . '@complete_booking');

    Route::get('cancellation_reasons/list', VeCancellationReasonController::class . '@index');

    Route::get('insights/list', VeInsightsController::class . '@index');

    Route::get('finances/dashboard', VeDashboardController::class . '@getRevenue');

    Route::get('financial/list', VeFinancialController::class . '@index');
    Route::post('financial/provider_action', VeFinancialController::class . '@index');
    Route::post('financial/add', VeFinancialController::class . '@add');
    Route::post('financial/update', VeFinancialController::class . '@update_financial');
    Route::post('financial/provider/list', VeFinancialController::class . '@provider_financial_list');

    Route::get('transaction/list', VeTransactionsController::class . '@index');
    Route::post('transaction/show', VeTransactionsController::class . '@getCustomerTotal');
    Route::post('transaction/add', VeTransactionsController::class . '@addTransaction');
    Route::post('transaction/edit', VeTransactionsController::class . '@update');
    Route::post('transaction/customer', VeTransactionsController::class . '@get_total_transaction');
    Route::get('transaction/payment_method/list', VeTransactionsController::class . '@list_payment_method');

    Route::post('package/add', VePackagesController::class . '@add');
    Route::post('package/edit', VePackagesController::class . '@edit_package');
    Route::post('package/show', VePackagesController::class . '@show');
    Route::post('package/service/check', VePackagesController::class . '@check_service_package');

    Route::post('package/service/update/service', VePackagesController::class . '@update_service');
    Route::post('package/service/update/status', VePackagesController::class . '@update_status');
    Route::post('package/service/update/complete', VePackagesController::class . '@complete_package');

    Route::get('monitor/booking', VeMonitorController::class . '@booking');
    Route::get('monitor/providers', VeMonitorController::class . '@providers');
    Route::get('monitor/rating', VeMonitorController::class . '@rating_average');
    Route::get('monitor/earning', VeMonitorController::class . '@total_earning');

    Route::post('media/upload', VeMediaController::class . '@upload');

    Route::get('branches/list', VeBranchController::class . '@index');
    Route::post('branches/add', VeBranchController::class . '@store');
    Route::post('branches/edit', VeBranchController::class . '@update');

    Route::get('users/list', VeUserController::class . '@index')
        ->middleware(Authorize::using('users.index'));
    Route::post('users/add', VeUserController::class . '@store')
        ->middleware(Authorize::using('users.store'));
    Route::post('users/edit', VeUserController::class . '@update')
        ->middleware(Authorize::using('users.update'));
    Route::delete('users/destroy', VeUserController::class . '@destroy')
        ->middleware(Authorize::using('users.destroy'));

    Route::get('expense_categories/list', VeExpenseCategoryController::class . '@index')
        ->middleware(Authorize::using('expense_categories.index'));
    Route::post('expense_categories/add', VeExpenseCategoryController::class . '@store')
        ->middleware(Authorize::using('expense_categories.store'));
    Route::post('expense_categories/edit', VeExpenseCategoryController::class . '@update')
        ->middleware(Authorize::using('expense_categories.update'));
    Route::delete('expense_categories/destroy', VeExpenseCategoryController::class . '@destroy')
        ->middleware(Authorize::using('expense_categories.destroy'));

    Route::get('expenses/list', VeExpenseController::class . '@index')
        ->middleware(Authorize::using('expenses.index'));
    Route::post('expenses/add', VeExpenseController::class . '@store')
        ->middleware(Authorize::using('expenses.store'));
    Route::post('expenses/edit', VeExpenseController::class . '@update')
        ->middleware(Authorize::using('expenses.update'));
    Route::delete('expenses/destroy', VeExpenseController::class . '@destroy')
        ->middleware(Authorize::using('expenses.destroy'));

    Route::get('product_categories/list', VeProductCategoryController::class . '@index')
        ->middleware(Authorize::using('product_categories.index'));
    Route::post('product_categories/add', VeProductCategoryController::class . '@store')
        ->middleware(Authorize::using('product_categories.store'));
    Route::post('product_categories/edit', VeProductCategoryController::class . '@update')
        ->middleware(Authorize::using('product_categories.update'));
    Route::delete('product_categories/destroy', VeProductCategoryController::class . '@destroy')
        ->middleware(Authorize::using('product_categories.destroy'));

    Route::get('products/list', VeProductController::class . '@index')
        ->middleware(Authorize::using('products.index'));
    Route::post('products/add', VeProductController::class . '@store')
        ->middleware(Authorize::using('products.store'));
    Route::post('products/edit', VeProductController::class . '@update')
        ->middleware(Authorize::using('products.update'));
    Route::delete('products/destroy', VeProductController::class . '@destroy')
        ->middleware(Authorize::using('products.destroy'));

    Route::get('procurements/list', VeProcurementController::class . '@index')
        ->middleware(Authorize::using('procurements.index'));
    Route::post('procurements/add', VeProcurementController::class . '@store')
        ->middleware(Authorize::using('procurements.store'));
    Route::post('procurements/edit', VeProcurementController::class . '@update')
        ->middleware(Authorize::using('procurements.update'));
    Route::delete('procurements/destroy', VeProcurementController::class . '@destroy')
        ->middleware(Authorize::using('procurements.destroy'));

    Route::get('adjustments/list', VeAdjustmentController::class . '@index')
        ->middleware(Authorize::using('adjustments.index'));
    Route::post('adjustments/add', VeAdjustmentController::class . '@store')
        ->middleware(Authorize::using('adjustments.store'));
    Route::post('adjustments/edit', VeAdjustmentController::class . '@update')
        ->middleware(Authorize::using('adjustments.update'));
    Route::delete('adjustments/destroy', VeAdjustmentController::class . '@destroy')
        ->middleware(Authorize::using('adjustments.destroy'));

    Route::get('service_categories/list', UpServiceCategoryController::class . '@index');

    Route::get('markets/list', [VeMarketController::class, 'index']);
    Route::post('markets/add', [VeMarketController::class, 'add']);
    Route::get('markets/services/list', [VeMarketController::class, 'listServices']);
    Route::post('markets/services/link', [VeMarketController::class, 'linkServices']);
    Route::get('markets/providers/list', [VeMarketController::class, 'listProviders']);
    Route::post('markets/providers/link', [VeMarketController::class, 'linkProviders']);
});



Route::middleware([
    'api',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api/ve/v1')->group(function () {
    Route::get('countries/list', VeCountriesController::class . '@dropdown');
    Route::post('login', [AuthTenantController::class, 'login']);
    Route::post('profile', [AuthTenantController::class, 'profile']);

    Route::post('request_otp', [AuthTenantController::class, 'requestOtp']);
    Route::post('verify_otp', [AuthTenantController::class, 'verifyOtp']);
    Route::post('reset_password', [AuthTenantController::class, 'resetPassword']);

    Route::group(['prefix' => 'customer_app'], function ($route) {
        $route->post('register', [AuthCustomerController::class, 'register']);
        $route->post('login', [AuthCustomerController::class, 'login']);
        $route->post('request_otp', [AuthCustomerController::class, 'requestOtp']);
        $route->post('verify_otp', [AuthCustomerController::class, 'verifyOtp']);

        Route::group(['middleware' => 'auth'], function ($route) {
            $route->post('delete_account', [AuthCustomerController::class, 'deleteAccount']);
            $route->get('profile', [AuthCustomerController::class, 'profile']);
            $route->post('logout', [AuthCustomerController::class, 'logout']);
            $route->post('reset_password', [AuthCustomerController::class, 'resetPassword']);
            $route->get('bookings', [BookingController::class, 'index']);
            $route->get('services', [ServiceController::class, 'index']);
            $route->get('services/{id}', [ServiceController::class, 'show']);
        });
    });

    Route::group(['prefix' => 'provider_app'], function ($route) {
        $route->post('login', [AuthProviderController::class, 'login']);
        $route->post('request_otp', [AuthProviderController::class, 'requestOtp']);
        $route->post('verify_otp', [AuthProviderController::class, 'verifyOtp']);

        Route::group(['middleware' => 'auth'], function ($route) {
            $route->get('profile', [AuthProviderController::class, 'profile']);
            $route->post('logout', [AuthProviderController::class, 'logout']);
            $route->post('reset_password', [AuthProviderController::class, 'resetPassword']);
            $route->get('bookings', [ProviderBookingController::class, 'index']);
            $route->get('bookings/{id}', [ProviderBookingController::class, 'show']);
            $route->post('bookings/{id}', [ProviderBookingController::class, 'update']);
            $route->post('bookings/{id}/payment', ProviderBookingController::class . '@addTransaction');
            $route->get('schedule/list', [WorkingScheduleController::class, 'listSchedule']);
            $route->post('schedule/edit', [WorkingScheduleController::class, 'editSchedule']);
            $route->get('days_off/list', [WorkingScheduleController::class, 'listDaysOff']);
            $route->post('days_off/edit', [WorkingScheduleController::class, 'addEditDayOff']);
            $route->delete('days_off/destroy', [WorkingScheduleController::class, 'destroyDayOff']);
            $route->get('notifications/list', [MobileNotificationController::class, 'index']);
            $route->get('transactions/list', [FinancialController::class, 'index']);
        });
    });
});



/* Market */
Route::middleware([
    'api',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api/mk/v1')->group(function () {
    Route::get('countries/list', [VeCountriesController::class, 'dropdown']);
    Route::post('login', [MarketsAuthTenantController::class, 'login']);
    Route::post('profile', [MarketsAuthTenantController::class, 'profile']);

    Route::post('request_otp', [MarketsAuthTenantController::class, 'requestOtp']);
    Route::post('verify_otp', [MarketsAuthTenantController::class, 'verifyOtp']);
    Route::post('reset_password', [MarketsAuthTenantController::class, 'resetPassword']);

    Route::group(['middleware' => ['auth']], function ($route) {
        $route->get('sectors/list', [SectorController::class, 'index']);
        $route->group(['prefix' => 'customers'], function ($route) {
            $route->get('/', [MaCustomersController::class, 'index']);
            $route->get('/booking', [MaBookingDashboardController::class, 'index']);
            $route->post('/booking/cancel', [MaBookingDashboardController::class, 'cancel']);
            $route->post('/', [MaCustomersController::class, 'store']);
            $route->get('/{id}', [MaCustomersController::class, 'show']);
            $route->put('/{id}', [MaCustomersController::class, 'update']);
            $route->post('/changes-status/{customers}', MaCustomersController::class . '@updateStatus');

        });
        $route->group(['prefix' => 'services'], function ($route) {
            $route->get('/', [MaServiceController::class, 'index']);
            $route->post('/', [MaServiceController::class, 'store']);
            $route->get('/{id}', [MaServiceController::class, 'show']);
            $route->put('/{id}', [MaServiceController::class, 'update']);
        });
        $route->group(['prefix' => 'service-categories'], function ($route) {
            $route->get('/', [MaServiceCategoryController::class, 'index']);
            $route->post('/', [MaServiceCategoryController::class, 'store']);
            $route->get('/{id}', [MaServiceCategoryController::class, 'show']);
            $route->put('/{id}', [MaServiceCategoryController::class, 'update']);
        });
        $route->get('/up-vendors', [MaVendorController::class, 'listUp']);
        $route->put('/vendor-services/{id}', [MaVendorController::class, 'updateServiceDetails']);
        $route->group(['prefix' => 'vendors'], function ($route) {
            $route->get('/', [MaVendorController::class, 'listAll']);
            $route->post('/', [MaVendorController::class, 'store']);
            $route->get('/{id}', [MaVendorController::class, 'getVendorDetails']);
            $route->put('/{id}', [MaVendorController::class, 'update']);
        });
    });

    Route::group(['prefix' => 'customer_app'], function ($route) {
        $route->post('login', [MarketsAuthCustomerController::class, 'login']);
        $route->post('request_otp', [MarketsAuthCustomerController::class, 'requestOtp']);
        $route->post('verify_otp', [MarketsAuthCustomerController::class, 'verifyOtp']);
        $route->get('sectors', [SectorController::class, 'index']);
        $route->get('sectors/{id}', [SectorController::class, 'show']);
        $route->get('services', [MaServiceController::class, 'index']);
        $route->get('services/{id}', [MaServiceController::class, 'show']);
        $route->get('vendors', [MaVendorController::class, 'index']);
        $route->get('vendors/{id}', [MaVendorController::class, 'show']);
        Route::group(['middleware' => 'auth'], function ($route) {
            $route->post('register', [MarketsAuthCustomerController::class, 'register']);
            $route->post('delete_account', [MarketsAuthCustomerController::class, 'deleteAccount']);
            $route->get('profile', [MarketsAuthCustomerController::class, 'profile']);
            $route->post('profile', [MarketsAuthCustomerController::class, 'updateProfile']);
            $route->get('profile/favorites', [MaVendorController::class, 'listFavorites']);
            $route->post('profile/favorites', [MaVendorController::class, 'toggleFavorites']);
            $route->post('recently_viewed', [MarketsAuthCustomerController::class, 'getRecentlyViewed']);

            $route->post('logout', [MarketsAuthCustomerController::class, 'logout']);

            $route->get('cities/list', [MaCitiesController::class, 'listCities']);
            $route->get('areas/list', [MaCitiesController::class, 'listAreas']);
            $route->post('address/list', [MaCustomersController::class, 'listAddress']);
            $route->post('address/add', [MaCustomersController::class, 'addAddress']);
            $route->post('address/edit', [MaCustomersController::class, 'editAddress']);
            $route->post('address/delete', [MaCustomersController::class, 'deleteAddress']);

            $route->post('matcher/check_availability', [MaMatcherController::class, 'check_availability']);

            $route->post('booking/add', [MaBookingController::class, 'add']);

            $route->get('booking/list', [MaBookingController::class, 'index']);
            $route->post('booking/show', [MaBookingController::class, 'show']);
            $route->post('booking/cancel', [MaBookingController::class, 'cancel']);

            $route->get('transaction/list', [MaTransactionsController::class, 'index']);
            $route->get('transaction/payment_method/list', [MaTransactionsController::class, 'list_payment_method']);

            $route->get('notifications/list', [MobileNotificationController::class, 'index']);
        });
    });
});
