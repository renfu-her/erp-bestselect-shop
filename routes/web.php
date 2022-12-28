<?php

use App\Http\Controllers\Cms\AdminManagement\PetitionCtrl;
use App\Http\Controllers\Cms\AuthCtrl;
use App\Http\Controllers\Cms\CustomerResetCtrl;
use App\Http\Controllers\Cms\DashboardCtrl;
use App\Http\Controllers\Cms\Marketing\EdmCtrl;
use App\Http\Controllers\Cms\PaymentCtrl;
use App\Http\Controllers\Cms\StyleDemo;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    // dd(app('url')->route('test',[],false));
    return redirect()->route('cms.dashboard');
});

Route::get('/demo', StyleDemo::class)->name('cms.styleDemo');

Route::get('/login', [AuthCtrl::class, 'login'])->name('cms.login');
Route::post('/login', [AuthCtrl::class, 'authenticate']);
Route::get('/logout', [AuthCtrl::class, 'logout'])->name('cms.logout');
Route::post('/erp-login', [AuthCtrl::class, 'erpLogin']);

Route::group(['prefix' => 'cms', 'as' => 'cms.', 'middleware' => 'auth:user'], function () {
    Route::get('dashboard', DashboardCtrl::class)->name('dashboard');
    require base_path('routes/cms/Product.php');
    require base_path('routes/cms/Category.php');
    require base_path('routes/cms/Collection.php');
    require base_path('routes/cms/Supplier.php');
    require base_path('routes/cms/SaleChannel.php');
    require base_path('routes/cms/Shipment.php');
    require base_path('routes/cms/User.php');
    require base_path('routes/cms/Customer.php');
    require base_path('routes/cms/Role.php');
    require base_path('routes/cms/Permission.php');
    require base_path('routes/cms/Purchase.php');
    require base_path('routes/cms/ComboPurchase.php');
    require base_path('routes/cms/Depot.php');
    require base_path('routes/cms/Spec.php');
    require base_path('routes/cms/Stock.php');
    require base_path('routes/cms/Homepage.php');
    require base_path('routes/cms/NaviNode.php');
    require base_path('routes/cms/Order.php');
    require base_path('routes/cms/GeneralLedger.php');
    require base_path('routes/cms/IncomeStatement.php');
    require base_path('routes/cms/PayableDefault.php');
    require base_path('routes/cms/ReceivedDefault.php');
    require base_path('routes/cms/CollectionPayment.php');
    require base_path('routes/cms/CollectionReceived.php');
    require base_path('routes/cms/ArCsnOrder.php');
    require base_path('routes/cms/FirstGrade.php');
    require base_path('routes/cms/Delivery.php');
    require base_path('routes/cms/DeliveryProduct.php');
    require base_path('routes/cms/Logistic.php');
    require base_path('routes/cms/UserMnt.php');
    require base_path('routes/cms/CustomPages.php');

    require base_path('routes/cms/Discount.php');
    require base_path('routes/cms/PromoCoupon.php');
    require base_path('routes/cms/UtmUrl.php');
    require base_path('routes/cms/GoogleMarketing.php');
    require base_path('routes/cms/Consignment.php');
    require base_path('routes/cms/ConsignmentOrder.php');
    require base_path('routes/cms/ConsignmentStock.php');
    require base_path('routes/cms/GroupbyCompany.php');

    require base_path('routes/cms/CustomerProfit.php');
    require base_path('routes/cms/CreditManager.php');
    require base_path('routes/cms/CreditCard.php');
    require base_path('routes/cms/CreditBank.php');
    require base_path('routes/cms/CreditPercent.php');
    require base_path('routes/cms/OrderInvoiceManager.php');
    require base_path('routes/cms/RemittanceRecord.php');

    require base_path('routes/cms/AccountReceived.php');
    require base_path('routes/cms/OrderBonus.php');

    require base_path('routes/cms/RequestOrder.php');
    require base_path('routes/cms/StituteOrder.php');

    require base_path('routes/cms/InboundImport.php');
    require base_path('routes/cms/InboundFix0917Import.php');
    require base_path('routes/cms/TransferVoucher.php');
    require base_path('routes/cms/AccountsPayable.php');
    require base_path('routes/cms/NoteReceivable.php');
    require base_path('routes/cms/BulletinBoard.php');
    require base_path('routes/cms/NotePayable.php');
    require base_path('routes/cms/MailSet.php');
    require base_path('routes/cms/Refund.php');
    require base_path('routes/cms/DayEnd.php');
    require base_path('routes/cms/Ledger.php');

    require base_path('routes/cms/UserPerformanceReport.php');
    require base_path('routes/cms/ProductManagerReport.php');
    require base_path('routes/cms/ProductProfitReport.php');
    require base_path('routes/cms/VolumeOfBusinessPerformanceReport.php');
    require base_path('routes/cms/CouponEvent.php');
    require base_path('routes/cms/Organize.php');

    require base_path('routes/cms/Petition.php');
    require base_path('routes/cms/Expenditure.php');
    require base_path('routes/cms/RefExpenditurePetition.php');

    require base_path('routes/cms/Edm.php');
    require base_path('routes/cms/OnePage.php');
    

    Route::get('reverse-bind-page/{sn}', [PetitionCtrl::class, 'reverseBindPage'])->name('reverse-bind-page');
    Route::post('reverse-bind-page/{sn}', [PetitionCtrl::class, 'reverseBindPageUpdate']);

});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => 'guest:customer'], function () {

    Route::get('/forgot-password', [CustomerResetCtrl::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [CustomerResetCtrl::class, 'sendResetPwMail'])->name('password.email');

    Route::get('/reset-password/{token?}', [CustomerResetCtrl::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password', [CustomerResetCtrl::class, 'resetPasswordStore'])->name('password.update');

    Route::get('/login-reset-status', [CustomerResetCtrl::class, 'loginResetStatus'])->name('login-reset-status');
});

Route::group(['prefix' => 'payment', 'as' => 'payment.'], function () {
    Route::get('credit_card/{id}/{unique_id}', [PaymentCtrl::class, 'credit_card'])->name('credit-card');
    Route::match(['get', 'post'], 'credit_card_checkout/{id}/{unique_id}', [PaymentCtrl::class, 'credit_card_checkout'])->name('credit-card-checkout');
    Route::post('credit_card_result/{id}', [PaymentCtrl::class, 'credit_card_result'])->name('credit-card-result');

    Route::get('line_pay/{source_type}/{source_id}/{unique_id}', [PaymentCtrl::class, 'line_pay'])->name('line-pay');
    Route::get('line_pay_confirm/{source_type}/{source_id}/{unique_id}', [PaymentCtrl::class, 'line_pay_confirm'])->name('line-pay-confirm');
});

Route::get('edm/{id}/{type}/{mcode}', [EdmCtrl::class, 'print'])->name('print-edm');

Route::get('_info', function () {
    // dd(app('url')->route('test',[],false));
    return phpinfo();
});
