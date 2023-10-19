<?php

use App\Http\Controllers\Account\SettingsController;
use App\Http\Controllers\AcctNominativeSavingsPickupController;
use App\Http\Controllers\Auth\SocialiteLoginController;
use App\Http\Controllers\Documentation\ReferencesController;
use App\Http\Controllers\Logs\AuditLogsController;
use App\Http\Controllers\Logs\SystemLogsController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AcctAccountBlockirController;
use App\Http\Controllers\AcctBankAccountController;
use App\Http\Controllers\AcctCreditsAccountController;
use App\Http\Controllers\AcctCreditsAccountPaidOffReportController;
use App\Http\Controllers\AcctCreditsController;
use App\Http\Controllers\AcctCreditsAccountHistoryController;
use App\Http\Controllers\AcctCreditsAccountMasterController;
use App\Http\Controllers\AcctCreditsAcquittanceController;
use App\Http\Controllers\AcctCreditsAgunanController;
use App\Http\Controllers\AcctCreditsDailyMutationController;
use App\Http\Controllers\AcctCreditsPaymentCashController;
use App\Http\Controllers\AcctCreditsPaymentBankController;
use App\Http\Controllers\AcctCreditsPaymentBranchController;
use App\Http\Controllers\AcctCreditsPaymentDebetController;
use App\Http\Controllers\AcctDepositoAccountBlockirController;
use App\Http\Controllers\AcctDepositoController;
use App\Http\Controllers\AcctDepositoAccountController;
use App\Http\Controllers\AcctDepositoAccountClosedReportController;
use App\Http\Controllers\AcctDepositoAccountClosingController;
use App\Http\Controllers\AcctDepositoAccountExtensionController;
use App\Http\Controllers\AcctDepositoAccountMasterController;
use App\Http\Controllers\AcctDepositoProfitSharingController;
use App\Http\Controllers\AcctGeneralLedgerReportController;
use App\Http\Controllers\AcctMutationController;
use App\Http\Controllers\AcctNominativeSavingsReportPickupController;
use App\Http\Controllers\AcctProfitLossReportController;
use App\Http\Controllers\AcctSavingsController;
use App\Http\Controllers\AcctSavingsAccountBlockirController;
use App\Http\Controllers\AcctSavingsAccountController;
use App\Http\Controllers\AcctSavingsAccountCoverController;
use App\Http\Controllers\AcctSavingsAccountMasterController;
use App\Http\Controllers\AcctSavingsAccountMonitorController;
use App\Http\Controllers\AcctSavingsAccountMutationController;
use App\Http\Controllers\AcctSavingsBankMutationController;
use App\Http\Controllers\AcctSavingsCashMutationController;
use App\Http\Controllers\AcctSavingsAccountSrhController;
use App\Http\Controllers\AcctSavingsCloseBookController;
use App\Http\Controllers\AcctSavingsProfitSharingController;
use App\Http\Controllers\AcctSourceFundController;
use App\Http\Controllers\BalanceSheetController;
use App\Http\Controllers\CoreBranchController;
use App\Http\Controllers\CoreDusunController;
use App\Http\Controllers\CoreMemberController;
use App\Http\Controllers\CoreMemberStatusController;
use App\Http\Controllers\CoreMemberPrintBookController;
use App\Http\Controllers\CoreMemberPrintMutationController;
use App\Http\Controllers\CoreOfficeController;
use App\Http\Controllers\CreditsCollectibilityReportController;
use App\Http\Controllers\CreditsHasntPaidReportController;
use App\Http\Controllers\CreditsPaymentDailyReportController;
use App\Http\Controllers\CreditsPaymentDuePaidReportController;
use App\Http\Controllers\CreditsPaymentReportController;
use App\Http\Controllers\DailyCashFlowReportController;
use App\Http\Controllers\DepositoProfitSharingReportController;
use App\Http\Controllers\JournalMemorialController;
use App\Http\Controllers\JournalVoucherController;
use App\Http\Controllers\MemberSavingsDebetPrincipalController;
use App\Http\Controllers\MemberSavingsPaymentController;
use App\Http\Controllers\MemberSavingsTransferMutationController;
use App\Http\Controllers\NominativeMemberReportController;
use App\Http\Controllers\NominativeCreditsReportController;
use App\Http\Controllers\NominativeDepositoReportController;
use App\Http\Controllers\NominativeRecapReportController;
use App\Http\Controllers\NominativeSavingsReportController;
use App\Http\Controllers\OfficerCreditsAccountReportController;
use App\Http\Controllers\OfficerDepositoAccountReportController;
use App\Http\Controllers\OfficerSavingsAccountReportController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PPOBJournalController;
use App\Http\Controllers\PPOBPriceController;
use App\Http\Controllers\PPOBSettingController;
use App\Http\Controllers\PPOBTopUpController;
use App\Http\Controllers\PreferenceCollectibilityController;
use App\Http\Controllers\PreferenceIncomeController;
use App\Http\Controllers\SampleDataController;
use App\Http\Controllers\SavingsDailyTransferMutationController;
use App\Http\Controllers\SavingsMandatoryHasntPaidReportController;
use App\Http\Controllers\SavingsProfitSharingReportController;
use App\Http\Controllers\SavingsTransferMutationController;
use App\Http\Controllers\SystemBranchCloseController;
use App\Http\Controllers\SystemBranchOpenController;
use App\Http\Controllers\SystemUserGroupController;
use App\Http\Controllers\TaxReportController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WhatsappController;
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
    return redirect('index');
});

$menu = theme()->getMenu();
array_walk($menu, function ($val) {
    if (isset($val['path'])) {
        $route = Route::get($val['path'], [PagesController::class, 'index']);

        // Exclude documentation from auth middleware
        if (!Str::contains($val['path'], 'documentation')) {
            $route->middleware('auth');
        }
    }
});
Route::get('test',[SampleDataController::class,'test']);
// Documentations pages
Route::prefix('documentation')->group(function () {
    Route::get('getting-started/references', [ReferencesController::class, 'index']);
    Route::get('getting-started/changelog', [PagesController::class, 'index']);
});

Route::middleware('auth')->group(function () {
    // Logs pages
    Route::prefix('log')->name('log.')->group(function () {
        Route::resource('system', SystemLogsController::class)->only(['index', 'destroy']);
        Route::resource('audit', AuditLogsController::class)->only(['index', 'destroy']);
    });

    // Account pages
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/add', [AccountController::class, 'add'])->name('add');
        Route::post('/process-add', [AccountController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{account_id}', [AccountController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [AccountController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{account_id}', [AccountController::class, 'delete'])->name('delete');
        Route::get('/export', [AccountController::class, 'export'])->name('export');
         //AcctAccountBlockir & Unblockir pages
        Route::controller(AcctAccountBlockirController::class)->group(function () {
            Route::prefix('deposito')->name('deposito.')->group(function () {
                Route::get('block',  'index')->name('block');
                Route::get('block/add',  'index')->name('block');
                Route::get('block',  'account')->name('account');
                Route::post('block/process-add',  'paymentViewport')->name('p-viewport');
                Route::post('block/process-unblock',  'accountViewport')->name('a-viewport');
            });
            Route::prefix('saving')->name('saving.')->group(function () {
                Route::get('payment',  'payment')->name('payment');
                Route::get('account',  'account')->name('account');
                Route::post('payment/viewport',  'paymentViewport')->name('p-viewport');
                Route::post('account/viewport',  'accountViewport')->name('a-viewport');
            });
        });
    });

    // AcctBankAccount pages
    Route::prefix('bank-account')->name('bank-account.')->group(function () {
        Route::get('/', [AcctBankAccountController::class, 'index'])->name('index');
        Route::get('/add', [AcctBankAccountController::class, 'add'])->name('add');
        Route::post('/process-add', [AcctBankAccountController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{bank_account_id}', [AcctBankAccountController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [AcctBankAccountController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{bank_account_id}', [AcctBankAccountController::class, 'delete'])->name('delete');
    });

    // AcctCredits pages
    Route::prefix('credits')->name('credits.')->group(function () {
        Route::get('/', [AcctCreditsController::class, 'index'])->name('index');
        Route::get('/add', [AcctCreditsController::class, 'add'])->name('add');
        Route::post('/process-add', [AcctCreditsController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{credits_id}', [AcctCreditsController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [AcctCreditsController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{credits_id}', [AcctCreditsController::class, 'delete'])->name('delete');
    });

    // AcctCreditsAccountHistory pages
    Route::prefix('credits-account-history')->name('credits-account-history.')->group(function () {
        Route::get('/', [AcctCreditsAccountHistoryController::class, 'index'])->name('index');
        Route::post('/filter', [AcctCreditsAccountHistoryController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctCreditsAccountHistoryController::class, 'filterReset'])->name('filter-reset');
        Route::get('/print-payment-schedule/{credits_account_id}', [AcctCreditsAccountHistoryController::class, 'printPaymentSchedule'])->name('print-payment-schedule');
        Route::get('/detail/{credits_account_id}', [AcctCreditsAccountHistoryController::class, 'detail'])->name('detail');
        Route::get('/print-payment-history/{credits_account_id}', [AcctCreditsAccountHistoryController::class, 'printPaymentHistory'])->name('print-payment-history');
    });

    // AcctCreditsAccountMaster pages
    Route::prefix('credits-account-master')->name('credits-account-master.')->group(function () {
        Route::get('/', [AcctCreditsAccountMasterController::class, 'index'])->name('index');
        Route::post('/filter', [AcctCreditsAccountMasterController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctCreditsAccountMasterController::class, 'filterReset'])->name('filter-reset');
        Route::get('/export', [AcctCreditsAccountMasterController::class, 'export'])->name('export');
    });

    // AcctCreditsAcquittance pages
    Route::prefix('credits-acquittance')->name('credits-acquittance.')->group(function () {
        Route::get('/', [AcctCreditsAcquittanceController::class, 'index'])->name('index');
        Route::post('/filter', [AcctCreditsAcquittanceController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctCreditsAcquittanceController::class, 'filterReset'])->name('filter-reset');
        Route::get('/add', [AcctCreditsAcquittanceController::class, 'add'])->name('add');
        Route::get('/modal-credits-account', [AcctCreditsAcquittanceController::class, 'modalAcctCreditsAccount'])->name('modal-credits-account');
        Route::get('/select-credits-account/{credits_account_id}', [AcctCreditsAcquittanceController::class, 'selectAcctCreditsAccount'])->name('select-credits-account');
        Route::post('/elements-add', [AcctCreditsAcquittanceController::class, 'elementsAdd'])->name('elements-add');
        Route::post('/process-add', [AcctCreditsAcquittanceController::class, 'processAdd'])->name('process-add');
        Route::get('/print-note/{savings_cash_mutation_id}', [AcctCreditsAcquittanceController::class, 'printNote'])->name('print-note');
    });

    // AcctCreditsAgunan pages
    Route::prefix('credits-agunan')->name('credits-agunan.')->group(function () {
        Route::get('/', [AcctCreditsAgunanController::class, 'index'])->name('index');
        Route::post('/filter', [AcctCreditsAgunanController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctCreditsAgunanController::class, 'filterReset'])->name('filter-reset');
        Route::get('/export', [AcctCreditsAgunanController::class, 'export'])->name('export');
        Route::get('/update-status/{credits_agunan_id}', [AcctCreditsAgunanController::class, 'updateStatus'])->name('update-status');
        Route::get('/print-receipt/{credits_agunan_id}', [AcctCreditsAgunanController::class, 'printReceipt'])->name('print-receipt');
    });

    // AcctCreditsPaymentCash pages
    Route::prefix('credits-payment-cash')->name('credits-payment-cash.')->group(function () {
        Route::get('/', [AcctCreditsPaymentCashController::class, 'index'])->name('index');
        Route::post('/filter', [AcctCreditsPaymentCashController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctCreditsPaymentCashController::class, 'filterReset'])->name('filter-reset');
        Route::get('/add', [AcctCreditsPaymentCashController::class, 'add'])->name('add');
        Route::get('/modal-credits-account', [AcctCreditsPaymentCashController::class, 'modalAcctCreditsAccount'])->name('modal-credits-account');
        Route::get('/select-credits-account/{credits_account_id}', [AcctCreditsPaymentCashController::class, 'selectAcctCreditsAccount'])->name('select-credits-account');
        Route::post('/elements-add', [AcctCreditsPaymentCashController::class, 'elementsAdd'])->name('elements-add');
        Route::post('/process-add', [AcctCreditsPaymentCashController::class, 'processAdd'])->name('process-add');
        Route::get('/print-note/{payment_cash_id}', [AcctCreditsPaymentCashController::class, 'printNote'])->name('print-note');
    });

    // AcctCreditsPaymentBank pages
    Route::prefix('credits-payment-bank')->name('credits-payment-bank.')->group(function () {
        Route::get('/', [AcctCreditsPaymentBankController::class, 'index'])->name('index');
        Route::post('/filter', [AcctCreditsPaymentBankController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctCreditsPaymentBankController::class, 'filterReset'])->name('filter-reset');
        Route::get('/add', [AcctCreditsPaymentBankController::class, 'add'])->name('add');
        Route::get('/modal-credits-account', [AcctCreditsPaymentBankController::class, 'modalAcctCreditsAccount'])->name('modal-credits-account');
        Route::get('/select-credits-account/{credits_account_id}', [AcctCreditsPaymentBankController::class, 'selectAcctCreditsAccount'])->name('select-credits-account');
        Route::post('/elements-add', [AcctCreditsPaymentBankController::class, 'elementsAdd'])->name('elements-add');
        Route::post('/process-add', [AcctCreditsPaymentBankController::class, 'processAdd'])->name('process-add');
        Route::get('/print-note/{payment_bank_id}', [AcctCreditsPaymentBankController::class, 'printNote'])->name('print-note');
    });

    // AcctCreditsPaymentBranch pages
    Route::prefix('credits-payment-branch')->name('credits-payment-branch.')->group(function () {
        Route::get('/', [AcctCreditsPaymentBranchController::class, 'index'])->name('index');
        Route::post('/filter', [AcctCreditsPaymentBranchController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctCreditsPaymentBranchController::class, 'filterReset'])->name('filter-reset');
        Route::get('/add', [AcctCreditsPaymentBranchController::class, 'add'])->name('add');
        Route::get('/modal-credits-account', [AcctCreditsPaymentBranchController::class, 'modalAcctCreditsAccount'])->name('modal-credits-account');
        Route::get('/select-credits-account/{credits_account_id}', [AcctCreditsPaymentBranchController::class, 'selectAcctCreditsAccount'])->name('select-credits-account');
        Route::post('/elements-add', [AcctCreditsPaymentBranchController::class, 'elementsAdd'])->name('elements-add');
        Route::post('/process-add', [AcctCreditsPaymentBranchController::class, 'processAdd'])->name('process-add');
        Route::get('/print-note/{payment_branch_id}', [AcctCreditsPaymentBranchController::class, 'printNote'])->name('print-note');
    });

    // AcctCreditsPaymentDebet pages
    Route::prefix('credits-payment-debet')->name('credits-payment-debet.')->group(function () {
        Route::get('/', [AcctCreditsPaymentDebetController::class, 'index'])->name('index');
        Route::post('/filter', [AcctCreditsPaymentDebetController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctCreditsPaymentDebetController::class, 'filterReset'])->name('filter-reset');
        Route::get('/add', [AcctCreditsPaymentDebetController::class, 'add'])->name('add');
        Route::get('/modal-credits-account', [AcctCreditsPaymentDebetController::class, 'modalAcctCreditsAccount'])->name('modal-credits-account');
        Route::get('/select-credits-account/{credits_account_id}', [AcctCreditsPaymentDebetController::class, 'selectAcctCreditsAccount'])->name('select-credits-account');
        Route::get('/modal-savings-account', [AcctCreditsPaymentDebetController::class, 'modalAcctSavingsAccount'])->name('modal-savings-account');
        Route::get('/select-savings-account/{savings_account_id}', [AcctCreditsPaymentDebetController::class, 'selectAcctSavingsAccount'])->name('select-savings-account');
        Route::post('/elements-add', [AcctCreditsPaymentDebetController::class, 'elementsAdd'])->name('elements-add');
        Route::post('/process-add', [AcctCreditsPaymentDebetController::class, 'processAdd'])->name('process-add');
        Route::get('/print-note/{payment_debet_id}', [AcctCreditsPaymentDebetController::class, 'printNote'])->name('print-note');
    });

    //AcctDeposito pages
    Route::prefix('deposito')->name('deposito.')->group(function () {
        Route::get('/', [AcctDepositoController::class, 'index'])->name('index');
        Route::get('/add', [AcctDepositoController::class, 'add'])->name('add');
        Route::post('/process-add', [AcctDepositoController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{deposito_id}', [AcctDepositoController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [AcctDepositoController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{deposito_id}', [AcctDepositoController::class, 'delete'])->name('delete');
    });

    // AcctDepositoAccount pages
    Route::prefix('deposito-account')->name('deposito-account.')->group(function () {
        Route::get('/', [AcctDepositoAccountController::class, 'index'])->name('index');
        Route::post('/filter', [AcctDepositoAccountController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctDepositoAccountController::class, 'filterReset'])->name('filter-reset');
        Route::get('/add', [AcctDepositoAccountController::class, 'add'])->name('add');
        Route::get('/modal-member', [AcctDepositoAccountController::class, 'modalCoreMember'])->name('modal-member');
        Route::get('/select-member/{member_id}', [AcctDepositoAccountController::class, 'selectCoreMember'])->name('select-member');
        Route::get('/modal-savings-account', [AcctDepositoAccountController::class, 'modalAcctSavingsAccount'])->name('modal-savings-account');
        Route::get('/select-savings-account/{savings_account_id}', [AcctDepositoAccountController::class, 'selectAcctSavingsAccount'])->name('select-savings-account');
        Route::post('/elements-add', [AcctDepositoAccountController::class, 'elementsAdd'])->name('elements-add');
        Route::post('/process-add', [AcctDepositoAccountController::class, 'processAdd'])->name('process-add');
        Route::get('/print-note/{deposito_account_id}', [AcctDepositoAccountController::class, 'printNote'])->name('print-note');
        Route::get('/validation/{deposito_account_id}', [AcctDepositoAccountController::class, 'validation'])->name('validation');
        Route::get('/print-validation/{deposito_account_id}', [AcctDepositoAccountController::class, 'printValidation'])->name('print-validation');
        Route::get('/print-certificate-back/{deposito_account_id}', [AcctDepositoAccountController::class, 'printCertificateBack'])->name('print-certificate-back');
        Route::get('/print-certificate-front/{deposito_account_id}', [AcctDepositoAccountController::class, 'printCertificateFront'])->name('print-certificate-front');
        Route::post('/get-deposito-detail', [AcctDepositoAccountController::class, 'getDepositoDetail'])->name('get-deposito-detail');
    });

    //AcctDepositoAccountBlockir
    Route::prefix('deposito-account-blockir')->name('deposito-account-blockir.')->group(function () {
        Route::get('/', [AcctDepositoAccountBlockirController::class, 'index'])->name('index');
        Route::get('/add', [AcctDepositoAccountBlockirController::class, 'add'])->name('add');
        Route::post('/elements-add', [AcctDepositoAccountBlockirController::class, 'elementsAdd'])->name('elements-add');
        Route::get('/reset-add', [AcctDepositoAccountBlockirController::class, 'resetAdd'])->name('reset-add');
        Route::post('/process-add', [AcctDepositoAccountBlockirController::class, 'processAdd'])->name('process-add');
        Route::get('/add-unblockir/{deposito_account_blockir_id}', [AcctDepositoAccountBlockirController::class, 'addUnblockir'])->name('add-unblockir');
        Route::get('/modal-member', [AcctDepositoAccountBlockirController::class, 'modalMember'])->name('modal-member');
        Route::get('/select-member/{deposito_account_id}', [AcctDepositoAccountBlockirController::class, 'selectMember'])->name('select-member');
    });

    // AcctDepositoAccountClosing pages
    Route::prefix('deposito-account-closing')->name('deposito-account-closing.')->group(function () {
        Route::get('/', [AcctDepositoAccountClosingController::class, 'index'])->name('index');
        Route::post('/filter', [AcctDepositoAccountClosingController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctDepositoAccountClosingController::class, 'filterReset'])->name('filter-reset');
        Route::get('/update/{deposito_account_id}', [AcctDepositoAccountClosingController::class, 'update'])->name('update');
        Route::post('/process-update', [AcctDepositoAccountClosingController::class, 'processUpdate'])->name('process-update');
        Route::get('/modal-savings-account', [AcctDepositoAccountClosingController::class, 'modalAcctSavingsAccount'])->name('modal-savings-account');
        Route::get('/select-savings-account/{savings_account_id}', [AcctDepositoAccountClosingController::class, 'selectAcctSavingsAccount'])->name('select-savings-account');
        Route::post('/elements-add', [AcctDepositoAccountClosingController::class, 'elementsAdd'])->name('elements-add');
    });

    // AcctDepositoAccountMaster pages
    Route::prefix('deposito-account-master')->name('deposito-account-master.')->group(function () {
        Route::get('/', [AcctDepositoAccountMasterController::class, 'index'])->name('index');
        Route::post('/filter', [AcctDepositoAccountMasterController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctDepositoAccountMasterController::class, 'filterReset'])->name('filter-reset');
        Route::get('/export', [AcctDepositoAccountMasterController::class, 'export'])->name('export');
    });

    // AcctDepositoProfitSharing pages
    Route::prefix('deposito-profit-sharing')->name('deposito-profit-sharing.')->group(function () {
        Route::get('/', [AcctDepositoProfitSharingController::class, 'index'])->name('index');
        Route::post('/filter', [AcctDepositoProfitSharingController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctDepositoProfitSharingController::class, 'filterReset'])->name('filter-reset');
        Route::get('/update/{deposito_profit_sharing_id}', [AcctDepositoProfitSharingController::class, 'update'])->name('update');
        Route::post('/process-update', [AcctDepositoProfitSharingController::class, 'processUpdate'])->name('process-update');
        Route::get('/modal-savings-account', [AcctDepositoProfitSharingController::class, 'modalAcctSavingsAccount'])->name('modal-savings-account');
        Route::get('/select-savings-account/{savings_account_id}', [AcctDepositoProfitSharingController::class, 'selectAcctSavingsAccount'])->name('select-savings-account');
        Route::post('/elements-add', [AcctDepositoProfitSharingController::class, 'elementsAdd'])->name('elements-add');
    });

    // AcctGeneralLedgerReport pages
    Route::prefix('general-ledger-report')->name('general-ledger-report.')->group(function () {
        Route::get('/', [AcctGeneralLedgerReportController::class, 'index'])->name('index');
        Route::post('/filter', [AcctGeneralLedgerReportController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctGeneralLedgerReportController::class, 'filterReset'])->name('filter-reset');
        Route::get('/print', [AcctGeneralLedgerReportController::class, 'processPrinting'])->name('print');
        Route::get('/export', [AcctGeneralLedgerReportController::class, 'export'])->name('export');
    });

    // AcctMutation pages
    Route::prefix('mutation')->name('mutation.')->group(function () {
        Route::get('/', [AcctMutationController::class, 'index'])->name('index');
        Route::get('/add', [AcctMutationController::class, 'add'])->name('add');
        Route::post('/process-add', [AcctMutationController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{mutation_id}', [AcctMutationController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [AcctMutationController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{mutation_id}', [AcctMutationController::class, 'delete'])->name('delete');
    });

    // AcctProfitLossReport pages
    Route::prefix('profit-loss-report')->name('profit-loss-report.')->group(function () {
        Route::get('/', [AcctProfitLossReportController::class, 'index'])->name('index');
        Route::post('/filter', [AcctProfitLossReportController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctProfitLossReportController::class, 'filterReset'])->name('filter-reset');
        Route::get('/process-shu', [AcctProfitLossReportController::class, 'processSHU'])->name('process-shu');
        Route::get('/print', [AcctProfitLossReportController::class, 'processPrinting'])->name('print');
        Route::get('/export', [AcctProfitLossReportController::class, 'export'])->name('export');
    });

    // AcctSavings pages
    Route::prefix('savings')->name('savings.')->group(function () {
        Route::get('/', [AcctSavingsController::class, 'index'])->name('index');
        Route::get('/add', [AcctSavingsController::class, 'add'])->name('add');
        Route::post('/process-add', [AcctSavingsController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{savings_id}', [AcctSavingsController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [AcctSavingsController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{savings_id}', [AcctSavingsController::class, 'delete'])->name('delete');
    });

    // AcctSavingsAccount pages
    Route::prefix('savings-account')->name('savings-account.')->group(function () {
        Route::get('/', [AcctSavingsAccountController::class, 'index'])->name('index');
        Route::get('/unblock/{savings_account_id}', [AcctSavingsAccountController::class, 'unblock'])->name('unblock');
        Route::post('/filter', [AcctSavingsAccountController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctSavingsAccountController::class, 'filterReset'])->name('filter-reset');
        Route::get('/add', [AcctSavingsAccountController::class, 'add'])->name('add');
        Route::get('/modal-member', [AcctSavingsAccountController::class, 'modalCoreMember'])->name('modal-member');
        Route::get('/select-member/{member_id}', [AcctSavingsAccountController::class, 'selectCoreMember'])->name('select-member');
        Route::post('/elements-add', [AcctSavingsAccountController::class, 'elementsAdd'])->name('elements-add');
        Route::post('/process-add', [AcctSavingsAccountController::class, 'processAdd'])->name('process-add');
        Route::get('/print-note/{savings_account_id}', [AcctSavingsAccountController::class, 'printNote'])->name('print-note');
        Route::get('/validation/{savings_account_id}', [AcctSavingsAccountController::class, 'validation'])->name('validation');
        Route::get('/print-validation/{savings_account_id}', [AcctSavingsAccountController::class, 'printValidation'])->name('print-validation');
        Route::post('/get-savings-interest-rate', [AcctSavingsAccountController::class, 'getSavingsInterestRate'])->name('get-savings-interest-rate');
    });

    //AcctSavingsAccountBlockir pages
    Route::prefix('savings-account-blockir')->name('savings-account-blockir.')->group(function () {
        Route::get('/', [AcctSavingsAccountBlockirController::class, 'index'])->name('index');
        Route::get('/add', [AcctSavingsAccountBlockirController::class, 'add'])->name('add');
        Route::post('/elements-add', [AcctSavingsAccountBlockirController::class, 'elementsAdd'])->name('elements-add');
        Route::get('/reset-add', [AcctSavingsAccountBlockirController::class, 'resetAdd'])->name('reset-add');
        Route::post('/process-add', [AcctSavingsAccountBlockirController::class, 'processAdd'])->name('process-add');
        Route::get('/add-unblockir/{savings_account_blockir_id}', [AcctSavingsAccountBlockirController::class, 'addUnblockir'])->name('add-unblockir');
        Route::get('/modal-member', [AcctSavingsAccountBlockirController::class, 'modalMember'])->name('modal-member');
        Route::get('/select-member/{savings_account_id}', [AcctSavingsAccountBlockirController::class, 'selectMember'])->name('select-member');
    });

    // AcctSavingsAccountCover pages
    Route::prefix('savings-account-cover')->name('savings-account-cover.')->group(function () {
        Route::get('/', [AcctSavingsAccountCoverController::class, 'index'])->name('index');
        Route::post('/filter', [AcctSavingsAccountCoverController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctSavingsAccountCoverController::class, 'filterReset'])->name('filter-reset');
        Route::get('/print/{savings_account_id}', [AcctSavingsAccountCoverController::class, 'processPrinting'])->name('print');
    });

    // AcctSavingsAccountMaster pages
    Route::prefix('savings-account-master')->name('savings-account-master.')->group(function () {
        Route::get('/', [AcctSavingsAccountMasterController::class, 'index'])->name('index');
        Route::post('/filter', [AcctSavingsAccountMasterController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctSavingsAccountMasterController::class, 'filterReset'])->name('filter-reset');
        Route::get('/export', [AcctSavingsAccountMasterController::class, 'export'])->name('export');
    });

    // AcctSavingsBankMutation pages
    Route::prefix('savings-bank-mutation')->name('savings-bank-mutation.')->group(function () {
        Route::get('/', [AcctSavingsBankMutationController::class, 'index'])->name('index');
        Route::post('/filter', [AcctSavingsBankMutationController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctSavingsBankMutationController::class, 'filterReset'])->name('filter-reset');
        Route::get('/add', [AcctSavingsBankMutationController::class, 'add'])->name('add');
        Route::get('/modal-savings-account', [AcctSavingsBankMutationController::class, 'modalAcctSavingsAccount'])->name('modal-savings-account');
        Route::get('/select-savings-account/{savings_account_id}', [AcctSavingsBankMutationController::class, 'selectAcctSavingsAccount'])->name('select-savings-account');
        Route::post('/elements-add', [AcctSavingsBankMutationController::class, 'elementsAdd'])->name('elements-add');
        Route::post('/process-add', [AcctSavingsBankMutationController::class, 'processAdd'])->name('process-add');
    });

    // AcctSavingsCashMutation pages
    Route::prefix('savings-cash-mutation')->name('savings-cash-mutation.')->group(function () {
        Route::get('/', [AcctSavingsCashMutationController::class, 'index'])->name('index');
        Route::post('/filter', [AcctSavingsCashMutationController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [AcctSavingsCashMutationController::class, 'filterReset'])->name('filter-reset');
        Route::get('/add', [AcctSavingsCashMutationController::class, 'add'])->name('add');
        Route::get('/modal-savings-account', [AcctSavingsCashMutationController::class, 'modalAcctSavingsAccount'])->name('modal-savings-account');
        Route::get('/select-savings-account/{savings_account_id}', [AcctSavingsCashMutationController::class, 'selectAcctSavingsAccount'])->name('select-savings-account');
        Route::post('/elements-add', [AcctSavingsCashMutationController::class, 'elementsAdd'])->name('elements-add');
        Route::post('/process-add', [AcctSavingsCashMutationController::class, 'processAdd'])->name('process-add');
        Route::get('/print-note/{savings_cash_mutation_id}', [AcctSavingsCashMutationController::class, 'printNote'])->name('print-note');
        Route::get('/validation/{savings_cash_mutation_id}', [AcctSavingsCashMutationController::class, 'validation'])->name('validation');
        Route::get('/print-validation/{savings_cash_mutation_id}', [AcctSavingsCashMutationController::class, 'printValidation'])->name('print-validation');
    });

    //AcctSavingsCloseBook pages
    Route::prefix('savings-close-book')->name('savings-close-book.')->group(function () {
        Route::get('/', [AcctSavingsCloseBookController::class, 'index'])->name('index');
        Route::post('/process-add', [AcctSavingsCloseBookController::class, 'processAdd'])->name('process-add');
    });

    // AcctSourceFund pages
    Route::prefix('source-fund')->name('source-fund.')->group(function () {
        Route::get('/', [AcctSourceFundController::class, 'index'])->name('index');
        Route::get('/add', [AcctSourceFundController::class, 'add'])->name('add');
        Route::post('/process-add', [AcctSourceFundController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{source_fund_id}', [AcctSourceFundController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [AcctSourceFundController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{source_fund_id}', [AcctSourceFundController::class, 'delete'])->name('delete');
    });

    // AcctSavingsProfitSharing pages
    Route::prefix('savings-profit-sharing')->name('savings-profit-sharing.')->group(function () {
        Route::get('/', [AcctSavingsProfitSharingController::class, 'index'])->name('index');
        Route::get('/list-data', [AcctSavingsProfitSharingController::class, 'listData'])->name('list-data');
        Route::post('/process-add', [AcctSavingsProfitSharingController::class, 'processAdd'])->name('process-add');
        Route::get('/process-update', [AcctSavingsProfitSharingController::class, 'processUpdate'])->name('process-update');
        Route::put('/recalculate', [AcctSavingsProfitSharingController::class, 'recalculate'])->name('recalculate');
    });

    // BalanceSheet pages
    Route::prefix('balance-sheet')->name('balance-sheet.')->group(function () {
        Route::get('/', [BalanceSheetController::class, 'index'])->name('index');
        Route::post('/filter', [BalanceSheetController::class, 'filter'])->name('filter');
        Route::get('/reset-filter', [BalanceSheetController::class, 'resetFilter'])->name('reset-filter');
        Route::get('/preview', [BalanceSheetController::class, 'preview'])->name('preview');
        Route::get('/export', [BalanceSheetController::class, 'export'])->name('export');
    });

    //CoreBranch pages
    Route::prefix('branch')->name('branch.')->group(function () {
        Route::get('/', [CoreBranchController::class, 'index'])->name('index');
        Route::get('/edit/{branch_id}', [CoreBranchController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [CoreBranchController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{branch_id}', [CoreBranchController::class, 'delete'])->name('delete');
    });

    //CoreMember pages
    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/', [CoreMemberController::class, 'index'])->name('index');
        Route::get('/detail/{member_id}', [CoreMemberController::class, 'detail'])->name('detail');
        Route::get('/add', [CoreMemberController::class, 'add'])->name('add');
        Route::post('/process-add', [CoreMemberController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{member_id}', [CoreMemberController::class, 'edit'])->name('edit');
        Route::post('/process-edit', [CoreMemberController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{member_id}', [CoreMemberController::class, 'delete'])->name('delete');
        Route::get('/activate/{member_id}', [CoreMemberController::class, 'activate'])->name('activate');
        Route::get('/non-activate/{member_id}', [CoreMemberController::class, 'nonActivate'])->name('non-activate');
        Route::get('/export', [CoreMemberController::class, 'export'])->name('export');
        Route::post('/get-city', [CoreMemberController::class, 'getCity'])->name('get-city');
        Route::post('/get-kecamatan', [CoreMemberController::class, 'getKecamatan'])->name('get-kecamatan');
        Route::post('/get-kelurahan', [CoreMemberController::class, 'getKelurahan'])->name('get-kelurahan');
        Route::post('/elements-add', [CoreMemberController::class, 'elementsAdd'])->name('elements-add');
    });

    //CoreMemberStatus pages
    Route::prefix('member-status')->name('member-status.')->group(function () {
        Route::get('/', [CoreMemberStatusController::class, 'index'])->name('index');
        Route::get('/update-status/{member_id}', [CoreMemberStatusController::class, 'updateStatus'])->name('update-status');
    });

    //CoreMemberPrintBook pages
    Route::prefix('member-print-book')->name('member-print-book.')->group(function () {
        Route::get('/', [CoreMemberPrintBookController::class, 'index'])->name('index');
        Route::get('/print/{member_id}', [CoreMemberPrintBookController::class, 'processPrinting'])->name('print');
    });

    //CoreMemberPrintMutation pages
    Route::prefix('member-print-mutation')->name('member-print-mutation.')->group(function () {
        Route::get('/', [CoreMemberPrintMutationController::class, 'index'])->name('index');
        Route::post('/print', [CoreMemberPrintMutationController::class, 'processPrinting'])->name('print');
        Route::post('/elements-add', [CoreMemberPrintMutationController::class, 'elementsAdd'])->name('elements-add');
        Route::get('/modal-member', [CoreMemberPrintMutationController::class, 'modalCoreMember'])->name('modal-member');
        Route::get('/select-member/{member_id}', [CoreMemberPrintMutationController::class, 'selectCoreMember'])->name('select-member');
        Route::get('/reset', [CoreMemberPrintMutationController::class, 'reset'])->name('reset');
        Route::post('/change-date', [CoreMemberPrintMutationController::class, 'changeDate'])->name('change-date');
    });

    //CoreOffice (BO) pages
    Route::prefix('office')->name('office.')->group(function () {
        Route::get('/', [CoreOfficeController::class, 'index'])->name('index');
        Route::get('/add', [CoreOfficeController::class, 'add'])->name('add');
        Route::post('/process-add', [CoreOfficeController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{office_id}', [CoreOfficeController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [CoreOfficeController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{office_id}', [CoreOfficeController::class, 'delete'])->name('delete');
    });

    //CreditsAccount pages
    Route::prefix('credits-account')->name('credits-account.')->group(function () {
        Route::get('/', [AcctCreditsAccountController::class, 'index'])->name('index');
        Route::get('/add', [AcctCreditsAccountController::class, 'add'])->name('add');
        Route::post('/filter', [AcctCreditsAccountController::class, 'filter'])->name('filter');
        Route::get('/reset-filter', [AcctCreditsAccountController::class, 'resetFilter'])->name('reset-filter');
        Route::post('/process-add', [AcctCreditsAccountController::class, 'processAdd'])->name('process-add');
        Route::get('/modal-member', [AcctCreditsAccountController::class, 'modalMember'])->name('modal-member');
        Route::get('/select-member/{member_id}', [AcctCreditsAccountController::class, 'selectMember'])->name('select-member');
        Route::post('/process-add-array-agunan', [AcctCreditsAccountController::class, 'processAddArrayAgunan'])->name('process-add-array-agunan');
        Route::post('/process-delete-array-agunan', [AcctCreditsAccountController::class, 'processDeleteArrayAgunan'])->name('process-delete-array-agunan');
        Route::post('/elements-add', [AcctCreditsAccountController::class, 'elementsAdd'])->name('elements-add');
        Route::get('/reset-elements-add', [AcctCreditsAccountController::class, 'resetElementsAdd'])->name('reset-elements-add');
        Route::post('/rate4', [AcctCreditsAccountController::class, 'rate4'])->name('rate4');
        Route::get('/print-note/{credits_account_id}', [AcctCreditsAccountController::class, 'printNote'])->name('print-note');
        Route::get('/print-akad/{credits_account_id}', [AcctCreditsAccountController::class, 'printAkad'])->name('print-akad');
        Route::get('/edit-date/{credits_account_id}', [AcctCreditsAccountController::class, 'editDate'])->name('edit-date');
        Route::post('/process-edit-date', [AcctCreditsAccountController::class, 'processEditDate'])->name('process-edit-date');
        Route::get('/print-schedule/{credits_account_id}', [AcctCreditsAccountController::class, 'printSchedule'])->name('print-schedule');
        Route::get('/print-schedule-member/{credits_account_id}', [AcctCreditsAccountController::class, 'printScheduleMember'])->name('print-schedule-member');
        Route::get('/print-agunan/{credits_account_id}', [AcctCreditsAccountController::class, 'printAgunan'])->name('print-agunan');
        Route::get('/delete/{credits_account_id}', [AcctCreditsAccountController::class, 'delete'])->name('delete');
        Route::get('/approving/{credits_account_id}', [AcctCreditsAccountController::class, 'approving'])->name('approving');
        Route::post('/process-approving', [AcctCreditsAccountController::class, 'processApproving'])->name('process-approving');
        Route::get('/reject/{credits_account_id}', [AcctCreditsAccountController::class, 'reject'])->name('reject');
        Route::get('/detail', [AcctCreditsAccountController::class, 'detail'])->name('detail');
    });

    // CreditsAccountPaidOffReport pages
    Route::prefix('credits-account-paid-off-report')->name('credits-account-paid-off-report.')->group(function () {
        Route::get('/', [AcctCreditsAccountPaidOffReportController::class, 'index'])->name('index');
    });

    // CreditsCollectibilityReport pages
    Route::prefix('credits-collectibility-report')->name('credits-collectibility-report.')->group(function () {
        Route::get('/', [CreditsCollectibilityReportController::class, 'index'])->name('index');
    });

    //CreditsHasntPaidReport pages
    Route::prefix('credits-hasnt-paid-report')->name('credits-hasnt-paid-report.')->group(function () {
        Route::get('/', [CreditsHasntPaidReportController::class, 'index'])->name('index');
        Route::post('/viewport', [CreditsHasntPaidReportController::class, 'viewport'])->name('viewport');
    });

    //CreditsPaymentDailyReport pages
    Route::prefix('credits-payment-daily-report')->name('credits-payment-daily-report.')->group(function () {
        Route::get('/', [CreditsPaymentDailyReportController::class, 'index'])->name('index');
        Route::post('/viewport', [CreditsPaymentDailyReportController::class, 'viewport'])->name('viewport');
    });

    //CreditsPaymentDuePaidReport pages
    Route::prefix('credits-payment-due-report')->name('credits-payment-due-report.')->group(function () {
        Route::get('/', [CreditsPaymentDuePaidReportController::class, 'index'])->name('index');
        Route::post('/viewport', [CreditsPaymentDuePaidReportController::class, 'viewport'])->name('viewport');
    });

    //DepositoAccountClosedReport pages
    Route::prefix('deposito-account-closed-report')->name('deposito-account-closed-report.')->group(function () {
        Route::get('/', [AcctDepositoAccountClosedReportController::class, 'index'])->name('index');
        Route::post('/viewport', [AcctDepositoAccountClosedReportController::class, 'viewport'])->name('viewport');
    });

    //DailyCashFlowReport pages
    Route::prefix('daily-cash-flow-report')->name('daily-cash-flow-report.')->group(function () {
        Route::get('/', [DailyCashFlowReportController::class, 'index'])->name('index');
        Route::post('/print', [DailyCashFlowReportController::class, 'print'])->name('print');
    });

    //DepositoAccountExtension pages
    Route::prefix('deposito-account-extension')->name('deposito-account-extension.')->group(function () {
        Route::get('/', [AcctDepositoAccountExtensionController::class, 'index'])->name('index');
        Route::post('/filter', [AcctDepositoAccountExtensionController::class, 'filter'])->name('filter');
        Route::get('/reset-filter', [AcctDepositoAccountExtensionController::class, 'resetFilter'])->name('reset-filter');
        Route::get('/edit/{deposito_account_id}', [AcctDepositoAccountExtensionController::class, 'edit'])->name('edit');
        Route::post('/process-edit', [AcctDepositoAccountExtensionController::class, 'processEdit'])->name('process-edit');
        Route::post('/elements-add', [AcctDepositoAccountExtensionController::class, 'elementsAdd'])->name('elements-add');
        Route::get('/reset-elements-add', [AcctDepositoAccountExtensionController::class, 'resetElementsAdd'])->name('reset-elements-add');
    });

    //DepositoProfitSharingReport pages
    Route::prefix('deposito-profit-sharing-report')->name('deposito-profit-sharing-report.')->group(function () {
        Route::get('/', [DepositoProfitSharingReportController::class, 'index'])->name('index');
        Route::post('/viewport', [DepositoProfitSharingReportController::class, 'viewport'])->name('viewport');
    });

    //Dropdown region
    Route::prefix('dropdown')->name('dropdown.')->group(function () {
        Route::post('/dropdown-city', [SampleDataController::class, 'dropdownCity'])->name('dropdown-city');
        Route::post('/dropdown-kecamatan', [SampleDataController::class, 'dropdownKecamatan'])->name('dropdown-kecamatan');
        Route::post('/dropdown-kelurahan', [SampleDataController::class, 'dropdownKelurahan'])->name('dropdown-kelurahan');
        Route::post('/dropdown-dusun', [SampleDataController::class, 'dropdownDusun'])->name('dropdown-dusun');
    });

    //JournalMemorial pages
    Route::prefix('journal-memorial')->name('journal-memorial.')->group(function () {
        Route::get('/', [JournalMemorialController::class, 'index'])->name('index');
        Route::post('/filter', [JournalMemorialController::class, 'filter'])->name('filter');
        Route::get('/reset-filter', [JournalMemorialController::class, 'resetFilter'])->name('reset-filter');
    });

    //JournalVoucher pages
    Route::prefix('journal-voucher')->name('journal-voucher.')->group(function () {
        Route::get('/', [JournalVoucherController::class, 'index'])->name('index');
        Route::get('/add', [JournalVoucherController::class, 'add'])->name('add');
        Route::post('/process-add', [JournalVoucherController::class, 'processAdd'])->name('process-add');
        Route::post('/filter', [JournalVoucherController::class, 'filter'])->name('filter');
        Route::get('/reset-filter', [JournalVoucherController::class, 'resetFilter'])->name('reset-filter');
        Route::post('/add-array', [JournalVoucherController::class, 'addArray'])->name('add-array');
        Route::post('/elements-add', [JournalVoucherController::class, 'elementsAdd'])->name('elements-add');
        Route::get('/reset-elements-add', [JournalVoucherController::class, 'resetElementsAdd'])->name('reset-elements-add');
        Route::get('/print/{journal_voucher_id}', [JournalVoucherController::class, 'print'])->name('print');
    });

    //MemberSavingsDebetPrincipal pages
    Route::prefix('member-savings-debet-principal')->name('member-savings-debet-principal.')->group(function () {
        Route::get('/', [MemberSavingsDebetPrincipalController::class, 'index'])->name('index');
        Route::put('/process-edit', [MemberSavingsDebetPrincipalController::class, 'processEdit'])->name('process-edit');
        Route::post('/elements-add', [MemberSavingsDebetPrincipalController::class, 'elementsAdd'])->name('elements-add');
        Route::get('/reset-elements-add', [MemberSavingsDebetPrincipalController::class, 'resetElementsAdd'])->name('reset-elements-add');
        Route::get('/modal-member', [MemberSavingsDebetPrincipalController::class, 'modalMember'])->name('modal-member');
        Route::get('/select-member/{member_id}', [MemberSavingsDebetPrincipalController::class, 'selectMember'])->name('select-member');
    });

    //MemberSavingsPayment pages
    Route::prefix('member-savings-payment')->name('member-savings-payment.')->group(function () {
        Route::get('/', [MemberSavingsPaymentController::class, 'index'])->name('index');
        Route::put('/process-edit', [MemberSavingsPaymentController::class, 'processEdit'])->name('process-edit');
        Route::post('/elements-add', [MemberSavingsPaymentController::class, 'elementsAdd'])->name('elements-add');
        Route::get('/reset-elements-add', [MemberSavingsPaymentController::class, 'resetElementsAdd'])->name('reset-elements-add');
        Route::get('/modal-member', [MemberSavingsPaymentController::class, 'modalMember'])->name('modal-member');
        Route::get('/select-member/{member_id}', [MemberSavingsPaymentController::class, 'selectMember'])->name('select-member');
        Route::get('/process-printing/{member_id}', [MemberSavingsPaymentController::class, 'processPrinting'])->name('process-printing');
    });

    //MemberSavingsTransferMutation pages
    Route::prefix('member-savings-transfer-mutation')->name('member-savings-transfer-mutation.')->group(function () {
        Route::get('/', [MemberSavingsTransferMutationController::class, 'index'])->name('index');
        Route::get('/add', [MemberSavingsTransferMutationController::class, 'add'])->name('add');
        Route::post('/elements-add', [MemberSavingsTransferMutationController::class, 'elementsAdd'])->name('elements-add');
        Route::get('/reset-elements-add', [MemberSavingsTransferMutationController::class, 'resetElementsAdd'])->name('reset-elements-add');
        Route::post('/process-add', [MemberSavingsTransferMutationController::class, 'processAdd'])->name('process-add');
        Route::post('/filter', [MemberSavingsTransferMutationController::class, 'filter'])->name('filter');
        Route::get('/filter-reset', [MemberSavingsTransferMutationController::class, 'filterReset'])->name('filter-reset');
        Route::get('/modal-member', [MemberSavingsTransferMutationController::class, 'modalMember'])->name('modal-member');
        Route::get('/select-member/{member_id}', [MemberSavingsTransferMutationController::class, 'selectMember'])->name('select-member');
        Route::get('/modal-savings-account', [MemberSavingsTransferMutationController::class, 'modalSavingsAccount'])->name('modal-savings-account');
        Route::get('/select-savings-account/{savings_account_id}', [MemberSavingsTransferMutationController::class, 'selectSavingsAccount'])->name('select-savings-account');
        Route::get('/validation/{member_transfer_mutation_id}', [MemberSavingsTransferMutationController::class, 'validation'])->name('validation');
        Route::get('/print-validation/{member_transfer_mutation_id}', [MemberSavingsTransferMutationController::class, 'printvalidation'])->name('print-validation');
        Route::get('/print-mutation/{member_transfer_mutation_id}', [MemberSavingsTransferMutationController::class, 'printMutation'])->name('print-mutation');
    });

    //NominativeCreditsReport pages
    Route::prefix('nominative-credits-report')->name('nominative-credits-report.')->group(function () {
        Route::get('/', [NominativeCreditsReportController::class, 'index'])->name('index');
        Route::post('/viewport', [NominativeCreditsReportController::class, 'viewport'])->name('viewport');
    });

    //NominativeDepositoReport pages
    Route::prefix('nominative-deposito-report')->name('nominative-deposito-report.')->group(function () {
        Route::get('/', [NominativeDepositoReportController::class, 'index'])->name('index');
        Route::post('/viewport', [NominativeDepositoReportController::class, 'viewport'])->name('viewport');
    });

    //NominativeMemberReport pages
    Route::prefix('nominative-member-report')->name('nominative-member-report.')->group(function () {
        Route::get('/', [NominativeMemberReportController::class, 'index'])->name('index');
        Route::post('/viewport', [NominativeMemberReportController::class, 'viewport'])->name('viewport');
    });

    //NominativeRecapReport pages
    Route::prefix('nominative-recap-report')->name('nominative-recap-report.')->group(function () {
        Route::get('/', [NominativeRecapReportController::class, 'index'])->name('index');
        Route::post('/viewport', [NominativeRecapReportController::class, 'viewport'])->name('viewport');
    });

    //NominativeSavingsReport pages
    Route::prefix('nominative-savings-report')->name('nominative-savings-report.')->group(function () {
        Route::get('/', [NominativeSavingsReportController::class, 'index'])->name('index');
        Route::post('/viewport', [NominativeSavingsReportController::class, 'viewport'])->name('viewport');
    });

    //OfficerCreditsAccountReport pages
    Route::prefix('officer-credits-account-report')->name('officer-credits-account-report.')->group(function () {
        Route::get('/', [OfficerCreditsAccountReportController::class, 'index'])->name('index');
        Route::post('/viewport', [OfficerCreditsAccountReportController::class, 'viewport'])->name('viewport');
    });

    //OfficerDepositoAccountReport pages
    Route::prefix('officer-deposito-account-report')->name('officer-deposito-account-report.')->group(function () {
        Route::get('/', [OfficerDepositoAccountReportController::class, 'index'])->name('index');
        Route::post('/viewport', [OfficerDepositoAccountReportController::class, 'viewport'])->name('viewport');
    });

    //OfficerSavingsAccountReport pages
    Route::prefix('officer-savings-account-report')->name('officer-savings-account-report.')->group(function () {
        Route::get('/', [OfficerSavingsAccountReportController::class, 'index'])->name('index');
        Route::post('/viewport', [OfficerSavingsAccountReportController::class, 'viewport'])->name('viewport');
    });

    //PPOBJournal pages
    Route::prefix('ppob-journal')->name('ppob-journal.')->group(function () {
        Route::get('/', [PPOBJournalController::class, 'index'])->name('index');
        Route::post('/filter', [PPOBJournalController::class, 'filter'])->name('filter');
        Route::get('/reset-filter', [PPOBJournalController::class, 'resetFilter'])->name('reset-filter');
    });

    //PPOBPrice pages
    Route::prefix('ppob-price')->name('ppob-price.')->group(function () {
        Route::get('/', [PPOBPriceController::class, 'index'])->name('index');
    });

    //PPOBSetting pages
    Route::prefix('ppob-setting')->name('ppob-setting.')->group(function () {
        Route::get('/', [PPOBSettingController::class, 'index'])->name('index');
        Route::post('/process-add', [PPOBSettingController::class, 'process-add'])->name('process-add');
    });

    //PPOBTopUp pages
    Route::prefix('ppob-topup')->name('ppob-topup.')->group(function () {
        Route::get('/', [PPOBTopUpController::class, 'index'])->name('index');
        Route::post('/add', [PPOBTopUpController::class, 'add'])->name('add');
        Route::post('/process-add', [PPOBTopUpController::class, 'process-add'])->name('process-add');
    });

    // PreferenceCollectibilty pages
    Route::prefix('preference-collectibility')->name('preference-collectibility.')->group(function () {
        Route::get('/', [PreferenceCollectibilityController::class, 'index'])->name('index');
        Route::put('/process-edit', [PreferenceCollectibilityController::class, 'processEdit'])->name('process-edit');
    });

    // SavingsAccountMonitor pages
    Route::prefix('savings-account-monitor')->name('savings-account-monitor.')->group(function () {
        Route::get('/', [AcctSavingsAccountMonitorController::class, 'index'])->name('index');
        Route::post('/filter', [AcctSavingsAccountMonitorController::class, 'filter'])->name('filter');
        Route::get('/reset-filter', [AcctSavingsAccountMonitorController::class, 'resetFilter'])->name('reset-filter');
        Route::get('/modal-savings-account', [AcctSavingsAccountMonitorController::class, 'modalSavingsAccount'])->name('modal-savings-account');
        Route::get('/select-savings-account/{savings_account_id}', [AcctSavingsAccountMonitorController::class, 'selectSavingsAccount'])->name('select-savings-account');
        Route::post('/print', [AcctSavingsAccountMonitorController::class, 'processPrinting'])->name('print');
        Route::get('/syncronize-data', [AcctSavingsAccountMonitorController::class, 'syncronizeData'])->name('syncronize-data');
    });

    // SavingsAccountMutation pages
    Route::prefix('savings-account-mutation')->name('savings-account-mutation.')->group(function () {
        Route::get('/', [AcctSavingsAccountMutationController::class, 'index'])->name('index');
        Route::post('/elements-add', [AcctSavingsAccountMutationController::class, 'elementsAdd'])->name('elements-add');
        Route::post('/filter', [AcctSavingsAccountMutationController::class, 'filter'])->name('filter');
        Route::get('/reset-filter', [AcctSavingsAccountMutationController::class, 'resetFilter'])->name('reset-filter');
        Route::get('/modal-savings-account', [AcctSavingsAccountMutationController::class, 'modalSavingsAccount'])->name('modal-savings-account');
        // Route::get('/select-savings-account/{savings_account_id}', [SavingsAccountMutationController::class, 'selectSavingsAccount'])->name('select-savings-account');
        Route::post('/print', [AcctSavingsAccountMutationController::class, 'processPrinting'])->name('print');
    });

    // SavingsAccountSrh pages
    Route::prefix('savings-account-srh')->name('savings-account-srh.')->group(function () {
        Route::get('/', [AcctSavingsAccountSrhController::class, 'index'])->name('index');
        Route::post('/filter', [AcctSavingsAccountSrhController::class, 'filter'])->name('filter');
        Route::get('/reset-filter', [AcctSavingsAccountSrhController::class, 'resetFilter'])->name('reset-filter');
        Route::get('/modal-savings-account', [AcctSavingsAccountSrhController::class, 'modalSavingsAccount'])->name('modal-savings-account');
        Route::get('/select-savings-account/{savings_account_id}', [AcctSavingsAccountSrhController::class, 'selectSavingsAccount'])->name('select-savings-account');
    });

    // SavingsDailyTransferMutation pages
    Route::prefix('savings-daily-transfer-mutation')->name('savings-daily-transfer-mutation.')->group(function () {
        Route::get('/', [SavingsDailyTransferMutationController::class, 'index'])->name('index');
        Route::post('/viewport', [SavingsDailyTransferMutationController::class, 'viewport'])->name('viewport');
    });

    // SavingsProfitSharingReport pages
    Route::prefix('savings-profit-sharing-report')->name('savings-profit-sharing-report.')->group(function () {
        Route::get('/', [SavingsProfitSharingReportController::class, 'index'])->name('index');
    });

    // SavingsTransferMutation pages
    Route::prefix('savings-transfer-mutation')->name('savings-transfer-mutation.')->group(function () {
        Route::get('/', [SavingsTransferMutationController::class, 'index'])->name('index');
        Route::get('/add', [SavingsTransferMutationController::class, 'add'])->name('add');
        Route::post('/filter', [SavingsTransferMutationController::class, 'filter'])->name('filter');
        Route::get('/reset-filter', [SavingsTransferMutationController::class, 'resetFilter'])->name('reset-filter');
        Route::get('/modal-savings-account-from', [SavingsTransferMutationController::class, 'modalSavingsAccountFrom'])->name('modal-savings-account-from');
        Route::get('/modal-savings-account-to', [SavingsTransferMutationController::class, 'modalSavingsAccountTo'])->name('modal-savings-account-to');
        Route::get('/select-savings-account-from/{savings_account_id}', [SavingsTransferMutationController::class, 'selectSavingsAccountFrom'])->name('select-savings-account-from');
        Route::get('/select-savings-account-to/{savings_account_id}', [SavingsTransferMutationController::class, 'selectSavingsAccountTo'])->name('select-savings-account-to');
        Route::post('/elements-add', [SavingsTransferMutationController::class, 'elementsAdd'])->name('elements-add');
        Route::get('/reset-elements-add', [SavingsTransferMutationController::class, 'resetElementsAdd'])->name('reset-elements-add');
        Route::post('/process-add', [SavingsTransferMutationController::class, 'processAdd'])->name('process-add');
        Route::get('/validation/{savings_transfer_mutation_id}', [SavingsTransferMutationController::class, 'validation'])->name('validation');
        Route::get('/print-validation/{savings_transfer_mutation_id}', [SavingsTransferMutationController::class, 'printValidation'])->name('print-validation');
    });

    // SystemUser pages
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('/add', [UsersController::class, 'add'])->name('add');
        Route::post('/process-add', [UsersController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{user_id}', [UsersController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [UsersController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{user_id}', [UsersController::class, 'delete'])->name('delete');
        Route::get('/reset-password/{user_id}', [UsersController::class, 'resetPassword'])->name('reset-password');
        Route::get('settings', [SettingsController::class, 'index'])->name('settings-index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings-update');
        Route::put('settings/email', [SettingsController::class, 'changeEmail'])->name('settings-changeemail');
        Route::put('settings/password', [SettingsController::class, 'changePassword'])->name('settings-changepassword');
    });

    // SystemUserGroup pages
    Route::prefix('user-group')->name('user-group.')->group(function () {
        Route::get('/', [SystemUserGroupController::class, 'index'])->name('index');
        Route::get('/add', [SystemUserGroupController::class, 'add'])->name('add');
        Route::post('/process-add', [SystemUserGroupController::class, 'processAdd'])->name('process-add');
        Route::get('/edit/{user_group_id}', [SystemUserGroupController::class, 'edit'])->name('edit');
        Route::put('/process-edit', [SystemUserGroupController::class, 'processEdit'])->name('process-edit');
        Route::get('/delete/{user_group_id}', [SystemUserGroupController::class, 'delete'])->name('delete');
    });

    // SystemBranchClose pages
    Route::prefix('branch-close')->name('branch-close.')->group(function () {
        Route::get('/', [SystemBranchCloseController::class, 'index'])->name('index');
        Route::put('/process', [SystemBranchCloseController::class, 'process'])->name('process');
    });

    // SystemBranchOpen pages
    Route::prefix('branch-open')->name('branch-open.')->group(function () {
        Route::get('/', [SystemBranchOpenController::class, 'index'])->name('index');
        Route::get('/process', [SystemBranchOpenController::class, 'process'])->name('process');
    });

    //TaxReport pages
    Route::prefix('tax-report')->name('tax-report.')->group(function () {
        Route::get('/', [TaxReportController::class, 'index'])->name('index');
        Route::post('/viewport', [TaxReportController::class, 'viewport'])->name('viewport');
    });
    Route::prefix('nominative-savings-pickup')->name('nomv-sv-pickup.')->group(function () {
        Route::get('/', [AcctNominativeSavingsPickupController::class, 'index'])->name('index');
        Route::post('/filter', [AcctNominativeSavingsPickupController::class, 'filter'])->name('filter');
        Route::post('/filter/reset', [AcctNominativeSavingsPickupController::class, 'filterReset'])->name('filter-reset');
        Route::get('/add/{savings_cash_mutation_id}', [AcctNominativeSavingsPickupController::class, 'add'])->name('add');
        Route::post('/process-add', [AcctNominativeSavingsPickupController::class, 'processAdd'])->name('process-add');
    });
    Route::prefix('nominative-savings-pickup-report')->name('nomv-sv-pickup-r.')->group(function () {
        Route::get('/', [AcctNominativeSavingsReportPickupController::class, 'index'])->name('index');
        Route::post('/viewport', [AcctNominativeSavingsReportPickupController::class, 'viewport'])->name('viewport');
    });
    //CreditsHasntPaidReport pages
    Route::prefix('saving-madatory-hasnt-paid-report')->name('sm-hasnt-paid-report.')->group(function () {
        Route::get('/', [SavingsMandatoryHasntPaidReportController::class, 'index'])->name('index');
        Route::post('/viewport', [SavingsMandatoryHasntPaidReportController::class, 'viewport'])->name('viewport');
    });
    //CreditsPaymentReport pages
    Route::prefix('credits-payment-report')->name('cp-report.')->group(function () {
        Route::get('/', [CreditsPaymentReportController::class, 'index'])->name('index');
        Route::post('/viewport', [CreditsPaymentReportController::class, 'viewport'])->name('viewport');
    });
     //CoreDusun pages
     Route::prefix('dusun')->controller(CoreDusunController::class)->name('dusun.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/add', 'add')->name('add');
        Route::get('/filter', 'filter')->name('filter');
        Route::post('/get-kecamatan', 'getKecamatan')->name('get-kecamatan');
        Route::post('/get-kelurahan', 'getKelurahan')->name('get-kelurahan');
        Route::post('/process-add', 'processAdd')->name('process-add');
        Route::post('/elements-add', 'elemenAdd')->name('elements-add');
        Route::get('/filter/reset', 'filterReset')->name('filter-reset');
        Route::put('/process-edit', 'processEdit')->name('process-edit');
        Route::get('/edit/{dusun_id}', 'edit')->name('edit');
        Route::get('/delete/{dusun_id}', 'delete')->name('delete');
    });
     //PreferenceIncome pages
     Route::prefix('preference-income')->controller(PreferenceIncomeController::class)->name('preference-income.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/filter', 'filter')->name('filter');
        Route::post('/process-add', 'processAdd')->name('process-add');
        Route::post('/elements-add', 'elemenAdd')->name('elements-add');
        Route::get('/filter/reset', 'filterReset')->name('filter-reset');
        Route::post('/process-edit', 'processEdit')->name('process-edit');
        Route::get('/delete/{income_id}', 'delete')->name('delete');
    });
    //CreditsDailyMutation pages
    Route::prefix('credits-daily-mutation')->controller(AcctCreditsDailyMutationController::class)->name('crd-daily-mutation.')->group(function () {
        Route::get('/payment',  'payment')->name('payment');
        Route::get('/ao', 'getOffice')->name('get-ao');
        Route::get('/account',  'account')->name('account');
        Route::post('/payment/viewport',  'paymentViewport')->name('p-viewport');
        Route::post('/account/viewport',  'accountViewport')->name('a-viewport');
    });
   
    //Whatsapp pages
    // Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
    //     Route::get('/', [WhatsappController::class, 'index'])->name('index');
    //     Route::post('/reload', [WhatsappController::class, 'reload'])->name('reload');
    //     Route::post('/add', [WhatsappController::class, 'add'])->name('add');
    //     Route::post('/process-add', [WhatsappController::class, 'process-add'])->name('process-add');
    // });

});

// Route::resource('users', UsersController::class);

Route::get('/auth/redirect/{provider}', [SocialiteLoginController::class, 'redirect']);

require __DIR__.'/auth.php';
