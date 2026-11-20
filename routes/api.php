<?php

use App\Http\Controllers\AbaPaywayController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeginingBalanceController;
use App\Http\Controllers\CashTransferController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CoffeeController;
use App\Http\Controllers\CoffeeIngredientsController;
use App\Http\Controllers\CoffeeVariantsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiscoundController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\IncomeTypeController;
use App\Http\Controllers\IngredientsController;
use App\Http\Controllers\MorCapitalController;
use App\Http\Controllers\OrderModeController;
use App\Http\Controllers\OrderTransactioncontroller;
use App\Http\Controllers\PaymentMethordController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseDetailController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionCoffeeController;
use App\Http\Controllers\TransactionPurchaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login',[AuthController::class, 'login']);
Route::apiResource('/categories', CategoriesController::class);

Route::middleware('auth:sanctum')->group(function (){

    Route::get('/user', function (Request $request) {

        return $request->user();

    });


    Route::apiResource('/coffees', CoffeeController::class);
    Route::apiResource('/coffee-variants', CoffeeVariantsController::class);
    Route::apiResource('/ingredients', IngredientsController::class);
    Route::apiResource('/coffee-ingredients', CoffeeIngredientsController::class);
    Route::apiResource('/suppliers', SupplierController::class);
    Route::apiResource('/purchases', PurchaseController::class);
    Route::apiResource('/purchase-details', PurchaseDetailController::class);
    Route::post('transaction-coffee', [TransactionCoffeeController::class, 'store']);
    Route::post('transaction-purchase', [TransactionPurchaseController::class, 'store']);
    Route::apiResource('/expent-types', ExpenseTypeController::class);
    Route::apiResource('/income-types', IncomeTypeController::class);
    Route::apiResource('/expenses', ExpensesController::class);
    Route::apiResource('/incomes', IncomeController::class);
    Route::apiResource('/exchanges', ExchangeController::class);
    Route::apiResource('/discound', DiscoundController::class);
    Route::apiResource('/order-modes', OrderModeController::class);
    Route::apiResource('/customers', CustomerController::class);
    Route::post('/transaction-order', [OrderTransactioncontroller::class, 'store']);
    Route::apiResource('/mor-capitals', MorCapitalController::class);
    Route::apiResource('/cash-transfers', CashTransferController::class);
    Route::apiResource('/begining-balances', BeginingBalanceController::class);
    Route::apiResource('/payment-methord',PaymentMethordController::class);

    // Stock Notifications Routes
    Route::get('/check-stock-notifications', [\App\Http\Controllers\StockNotificationController::class, 'checkAndNotify']);
    Route::get('/my-notifications', [\App\Http\Controllers\StockNotificationController::class, 'myNotifications']);
    Route::put('/notifications/{id}/read', [\App\Http\Controllers\StockNotificationController::class, 'markAsRead']);
    Route::put('/notifications/mark-all-read', [\App\Http\Controllers\StockNotificationController::class, 'markAllAsRead']);




    Route::post('/logout',[AuthController::class, 'logout']);
    Route::get('/get-user',[AuthController::class, 'currentUser']);
});

// Test ABA Payway
Route::post('aba/create-payment',[AbaPaywayController::class, 'createQrPayment']);
Route::post('aba/check-transaction',[AbaPaywayController::class, 'checkTransaction']);




