<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\AcctAccount;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPayment;
use App\Models\AcctDepositoAccount;
use App\Models\AcctDepositoProfitSharing;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsCashMutation;
use App\Models\CoreMember;
use App\Models\CoreMemberTransferMutation;
use App\Models\SystemEndOfDays;
use App\Models\SystemLogUser;
use App\Models\User;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use Illuminate\Support\Facades\DB;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $preferencecompany = PreferenceCompany::first();
        $data_user = User::where('username', $this->input('username'))
        ->first();
        $end_of_days = SystemEndOfDays::orderBy('created_at','DESC')
        ->first();
        if(empty($data_user)){
            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }
        if ($data_user['user_group_id'] == 1 || $data_user['user_group_id'] == 5) {
            if ($data_user->user_id == 1) {
                $this->ensureIsNotRateLimited();

                if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
                    RateLimiter::hit($this->throttleKey());
                    throw ValidationException::withMessages([
                        'username' => __('auth.failed'),
                    ]);
                }

                $this->loginRequest($this->input('username'));

                RateLimiter::clear($this->throttleKey());
            } else {
                if ($preferencecompany->maintenance_status == 0){
                    $this->ensureIsNotRateLimited();

                    if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
                        RateLimiter::hit($this->throttleKey());

                        throw ValidationException::withMessages([
                            'username' => __('auth.failed'),
                        ]);
                    }

                    $this->loginRequest($this->input('username'));

                    RateLimiter::clear($this->throttleKey());
                } else {
                    throw ValidationException::withMessages([
                        'username' => __('auth.maintenance'),
                    ]);
                }
            }
        } else {
            if ($end_of_days['end_of_days_status'] == 1 && date('Y-m-d',strtotime($end_of_days['open_at'])) == date('Y-m-d')) {
                if ($data_user->user_id == 1) {
                    $this->ensureIsNotRateLimited();

                    if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
                        RateLimiter::hit($this->throttleKey());

                        throw ValidationException::withMessages([
                            'username' => __('auth.failed'),
                        ]);
                    }

                    $this->loginRequest($this->input('username'));

                    RateLimiter::clear($this->throttleKey());
                } else {
                    if ($preferencecompany->maintenance_status == 0){
                        $this->ensureIsNotRateLimited();

                        if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
                            RateLimiter::hit($this->throttleKey());

                            throw ValidationException::withMessages([
                                'username' => __('auth.failed'),
                            ]);
                        }

                        $this->loginRequest($this->input('username'));

                        RateLimiter::clear($this->throttleKey());
                    } else {
                        throw ValidationException::withMessages([
                            'username' => __('auth.maintenance'),
                        ]);
                    }
                }
            } else {
                // if ($data_user['user_group_id'] != 1 || $data_user['user_group_id'] != 5 || $data_user['user_group_id'] != 5) {
                //     throw ValidationException::withMessages([
                //         'username' => __('auth.closebranch'),
                //     ]);
                // } else {
                    if ($data_user->user_id == 1) {
                        $this->ensureIsNotRateLimited();

                        if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
                            RateLimiter::hit($this->throttleKey());

                            throw ValidationException::withMessages([
                                'username' => __('auth.failed'),
                            ]);
                        }

                        $this->loginRequest($this->input('username'));

                        RateLimiter::clear($this->throttleKey());
                    } else {
                        if ($preferencecompany->maintenance_status == 0){
                            $this->ensureIsNotRateLimited();

                            if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
                                RateLimiter::hit($this->throttleKey());

                                throw ValidationException::withMessages([
                                    'username' => __('auth.failed'),
                                ]);
                            }

                            $this->loginRequest($this->input('username'));

                            RateLimiter::clear($this->throttleKey());
                        } else {
                            throw ValidationException::withMessages([
                                'username' => __('auth.maintenance'),
                            ]);
                        }
                    }
                // }
            }
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('username')).'|'.$this->ip();
    }

    public function loginRequest($username)
    {
        $user = User::where('username', $username)
        ->first();

        $this->automaticRoleOverAcctDepositeAccount($user->user_id, $user->branch_id);
        $this->autoDebetCreditsAccount($user->user_id, $user->branch_id);
        $this->autoDebetMandatorySavings($user->user_id, $user->branch_id, $username);

        if (!empty($user)) {
            $data = array(
                'user_id' => $user->user_id,
                'username' => $username,
                'id_previllage' => 1001,
                'log_stat' => 1,
                'class_name' => 'Application.validationprocess.verifikasi',
                'pk' => $username,
                'remark' => 'Login System',
                'log_time' => date('Y-m-d H:i:s'),
            );

            SystemLogUser::create($data);
        }
    }

    public function automaticRoleOverAcctDepositeAccount($user_id, $branch_id)
    {
        $deposito_account_due_date_one_month_before = strtotime("-1 month", strtotime(date('Y-m-d')));
        $acct_deposito_account = AcctDepositoAccount::select('deposito_account_extra_type', 'deposito_account_id', 'deposito_account_due_date', 'deposito_account_date', 'deposito_account_period', 'deposito_id', 'deposito_account_interest_amount', 'deposito_account_amount', 'savings_account_id', 'member_id', 'deposito_account_closed_date')
        ->where('data_state',0)
        ->where('deposito_account_extra_type',1)
        ->where('deposito_account_closed_date', null)
        ->where('deposito_account_due_date','>=', date('Y-m-d', $deposito_account_due_date_one_month_before))
        ->orderBy('deposito_account_id', 'ASC')
        ->get();

        DB::beginTransaction();

        try {
            foreach($acct_deposito_account as $deposito_account){

                $deposito_account_due_date_new = strtotime("+1 day", strtotime($deposito_account['deposito_account_due_date']));

                if(date('Y-m-d', $deposito_account_due_date_new) <= date('Y-m-d')){

                    $period_extra = $deposito_account['deposito_account_period'];
                    $deposito_account_due_date = strtotime("+".$period_extra." month", strtotime($deposito_account['deposito_account_due_date']));

                    $table                              = AcctDepositoAccount::findOrFail($deposito_account['deposito_account_id']);
                    $table->deposito_account_due_date   = date('Y-m-d', $deposito_account_due_date);
                    $table->updated_id                  = $user_id;

                    if($table->save()){
                        $date 	= date('d', strtotime($deposito_account['deposito_account_due_date']));
                        $month 	= date('m', strtotime($deposito_account['deposito_account_due_date']));
                        $year 	= date('Y', strtotime($deposito_account['deposito_account_due_date']));

                        for ($i=1; $i<= $deposito_account['deposito_account_period']; $i++) {
                            $depositoprofitsharing = array ();

                            $month = $month + 1;

                            if($month == 13){
                                $month = 01;
                                $year = $year + 1;
                            }

                            $deposito_profit_sharing_due_date = $year.'-'.$month.'-'.$date;

                            $depositoprofitsharing = array (
                                'deposito_account_id'				=> $deposito_account['deposito_account_id'],
                                'branch_id'							=> $branch_id,
                                'deposito_id'						=> $deposito_account['deposito_id'],
                                'deposito_account_interest_amount'  => $deposito_account['deposito_account_interest_amount'],
                                'member_id'							=> $deposito_account['member_id'],
                                'deposito_profit_sharing_due_date'	=> $deposito_profit_sharing_due_date,
                                'deposito_daily_average_balance'	=> $deposito_account['deposito_account_amount'],
                                'deposito_account_last_balance'		=> $deposito_account['deposito_account_amount'],
                                'savings_account_id'				=> $deposito_account['savings_account_id'],
                                'created_id'                        => $user_id,
                            );

                            $depositoprofitsharing_data = AcctDepositoProfitSharing::select('acct_deposito_profit_sharing.deposito_profit_sharing_id', 'acct_deposito_profit_sharing.deposito_account_id', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.member_id', 'core_member.member_name')
                            ->join('core_member','acct_deposito_profit_sharing.member_id', '=', 'core_member.member_id')
                            ->join('acct_deposito_account','acct_deposito_profit_sharing.deposito_account_id', '=', 'acct_deposito_account.deposito_account_id')
                            ->where('acct_deposito_profit_sharing.deposito_account_id', $depositoprofitsharing['deposito_account_id'])
                            ->where('acct_deposito_profit_sharing.deposito_profit_sharing_due_date', $depositoprofitsharing['deposito_profit_sharing_due_date'])
                            ->get();

                            if(count($depositoprofitsharing_data) == 0){
                                AcctDepositoProfitSharing::create($depositoprofitsharing);
                            }

                        }
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    public function autoDebetCreditsAccount($user_id, $branch_id)
    {
        $acctcreditsaccount 	= AcctCreditsAccount::select('credits_account_id', 'savings_account_id', 'credits_account_principal_amount', 'credits_account_interest_amount', 'credits_account_id', 'credits_account_period', 'credits_account_payment_to', 'credits_payment_period', 'credits_account_payment_date', 'payment_type_id', 'credits_id', 'credits_account_interest_last_balance', 'credits_account_accumulated_fines', 'credits_account_serial')
        ->where('data_state', 0)
        ->where('payment_preference_id', 2)
        ->where('credits_approve_status', 1)
        ->where('credits_account_status', 0)
        ->where('credits_account_payment_date','<=', date('Y-m-d'))
        ->orderBy('credits_account_id','ASC')
        ->get();

        DB::beginTransaction();

        try {

            foreach($acctcreditsaccount as $key => $val){
                $norek 				= $val['savings_account_id'];
                $pokok 				= $val['credits_account_principal_amount'];
                $interest 			= $val['credits_account_interest_amount'];
                $interest_income 	= 0;
                $others_income 		= 0;
                $id_pinjaman 		= $val['credits_account_id'];
                $total 				= $pokok+$interest+$interest_income+$others_income;
                $simpanan 			= AcctSavingsAccount::select('acct_savings_account.member_id','acct_savings.savings_name', 'acct_savings_account.savings_account_last_balance','acct_savings.savings_id')
                ->join('core_member', 'acct_savings_account.member_id','=','core_member.member_id')
                ->join('acct_savings', 'acct_savings_account.savings_id','=','acct_savings.savings_id')
                ->where('acct_savings_account.data_state', 0)
                ->where('acct_savings_account.savings_account_id', $norek)
                ->first();
                $pinjaman 			= AcctCreditsAccount::select('credits_account_last_balance')
                ->where('data_state',0)
                ->where('credits_account_id', $id_pinjaman)
                ->first();
                $last_balance 		= $pinjaman['credits_account_last_balance']-$pokok;

                $total_angsuran = $val['credits_account_period'];
                $angsuran_ke 	= $val['credits_account_payment_to']+1;
                $angsuran_tiap 	= $val['credits_payment_period'];

                if($angsuran_ke < $total_angsuran){
                    if($angsuran_tiap == 1){
                        $credits_account_payment_date_old 	= $val['credits_account_payment_date'];
                        $credits_account_payment_date 		= date('Y-m-d', strtotime("+1 months", strtotime($credits_account_payment_date_old)));
                    } else {
                        $credits_account_payment_date_old 	= $val['credits_account_payment_date'];
                        $credits_account_payment_date 		= date('Y-m-d', strtotime("+1 weeks", strtotime($credits_account_payment_date_old)));
                    }

                }

                if($angsuran_ke == $total_angsuran){
                    $credits_account_status = 1;
                } else {
                    $credits_account_status = 0;
                }

                if($val['payment_type_id'] == 1){
                    $angsuranpokok 		= $val['credits_account_principal_amount'];
                    $angsuranbunga 	 	= $val['credits_account_interest_amount'];
                } else if($val['payment_type_id'] == 2){
                    $angsuranbunga 	 	= ($val['credits_account_last_balance'] * $val['credits_account_interest']) /100;
                    $angsuranpokok 		= $val['credits_account_payment_amount'] - $angsuranbunga;
                }

                $credits_payment_date 			= date('Y-m-d');
                $date1 							= date_create($credits_payment_date);
                $date2 							= date_create($val['credits_account_payment_date']);
                $angsuranke 					= $val['credits_account_payment_to'] + 1;
                $tambah 						= $angsuranke.'month';

                if($date1 > $date2){
                    $interval                       = $date1->diff($date2);
                    $credits_payment_day_of_delay   = $interval->days;
                } else {
                    $credits_payment_day_of_delay 		= 0;
                }

                $data_cash = array(
                    'branch_id'									=> $branch_id,
                    'member_id'									=> $simpanan['member_id'],
                    'credits_id'								=> $val['credits_id'],
                    'credits_account_id'						=> $val['credits_account_id'],
                    'savings_account_id'						=> $val['savings_account_id'],
                    'credits_payment_date'						=> date('Y-m-d'),
                    'credits_payment_amount'					=> $total,
                    'credits_payment_principal'					=> $pokok,
                    'credits_payment_interest'					=> $interest,
                    'credits_interest_income'					=> $interest_income,
                    'credits_others_income'						=> $others_income,
                    'credits_principal_opening_balance'			=> $pinjaman['credits_account_last_balance'],
                    'credits_principal_last_balance'			=> $last_balance,
                    'credits_interest_opening_balance'			=> $val['credits_account_interest_last_balance'],
                    'credits_interest_last_balance'				=> $val['credits_account_interest_last_balance'] + $angsuranbunga,
                    'credits_account_payment_date'				=> $val['credits_account_payment_date'],
                    'credits_payment_to'						=> $val['credits_account_payment_to']+1,
                    'credits_payment_day_of_delay'				=> $credits_payment_day_of_delay,
                    'credits_payment_fine'						=> 0,
                    'credits_payment_type'						=> 1,
                    'created_id'								=> $user_id,
                );

                $transaction_module_code 	= 'ANGS';
                $transaction_module_id 		= PreferenceTransactionModule::where('transaction_module_code',$transaction_module_code)
                ->first()
                ->transaction_module_id;
                $preferencecompany 			= PreferenceCompany::first();

                if(AcctCreditsPayment::create($data_cash)){
                    AcctCreditsAccount::where('credits_account_id',$data_cash['credits_account_id'])
                    ->update([
                        "credits_account_last_balance" 					=> $data_cash['credits_principal_last_balance'],
                        "credits_account_last_payment_date"				=> $data_cash['credits_payment_date'],
                        "credits_account_payment_date"					=> $credits_account_payment_date,
                        "credits_account_payment_to"					=> $data_cash['credits_payment_to'],
                        "credits_account_interest_last_balance"			=> $data_cash['credits_interest_last_balance'],
                        "credits_account_accumulated_fines"				=> $val['credits_account_accumulated_fines'],
                        "credits_account_status"						=> $credits_account_status,
                        'updated_id'								    => $user_id,
                    ]);

                    AcctSavingsAccount::where('savings_account_id',$norek)
                    ->update([
                        "savings_account_last_balance"  => $simpanan['savings_account_last_balance'] - $total,
                        'updated_id'				    => $user_id,
                    ]);

                    $last_balance 	= ($simpanan['savings_account_last_balance']) - $total;
                    $mutasi_data 	=array(
                        "savings_account_id" 					=> $norek,
                        "savings_id" 							=> $simpanan['savings_id'],
                        "member_id" 							=> $simpanan['member_id'],
                        "branch_id" 							=> $branch_id,
                        "mutation_id" 							=> 4,
                        "savings_cash_mutation_date" 			=> date('Y-m-d'),
                        "savings_cash_mutation_opening_balance" => $simpanan['savings_account_last_balance'],
                        "savings_cash_mutation_last_balance" 	=> $last_balance,
                        "savings_cash_mutation_amount" 			=> $total,
                        "savings_cash_mutation_remark"	 		=> "Pembayaran Kredit No.".$val['credits_account_serial'],
                        'created_id'						    => $user_id,
                    );

                    AcctSavingsCashMutation::create($mutasi_data);

                    if($data_cash['credits_payment_fine'] > 0){
                        $last_balance_after_fine 	= $last_balance - $data_cash['credits_payment_fine'];
                        $mutasi_data 	=array(
                            "savings_account_id" 					=> $norek,
                            "savings_id" 							=> $simpanan['savings_id'],
                            "member_id" 							=> $simpanan['member_id'],
                            "branch_id" 							=> $branch_id,
                            "mutation_id" 							=> 4,
                            "savings_cash_mutation_date" 			=> date('Y-m-d'),
                            "savings_cash_mutation_opening_balance" => $last_balance,
                            "savings_cash_mutation_last_balance" 	=> $last_balance_after_fine,
                            "savings_cash_mutation_amount" 			=> $data_cash['credits_payment_fine'],
                            "savings_cash_mutation_remark"	 		=> "Pembayaran Denda Atas Kredit No.".$val['credits_account_serial'],
                            'created_id'						    => $user_id,
                        );

                        AcctSavingsCashMutation::create($mutasi_data);
                    }

                    $acctcashpayment_last 	= AcctCreditsPayment::select('core_member.member_name','acct_credits.credits_name','acct_credits_account.credits_id','acct_credits_payment.credits_payment_id','acct_credits_account.credits_account_serial')
                    ->where('acct_credits_payment.created_id',$data_cash['created_id'])
                    ->join('core_member','acct_credits_payment.member_id','=','core_member.member_id')
                    ->join('acct_credits_account','acct_credits_payment.credits_account_id','=','acct_credits_account.credits_account_id')
                    ->join('acct_credits','acct_credits_account.credits_id','=','acct_credits.credits_id')
                    ->orderBy('acct_credits_payment.created_at','DESC')
                    ->first();

                    $journal_voucher_period = date("Ym", strtotime($data_cash['credits_payment_date']));

                    $data_journal = array(
                        'branch_id'						=> $data_cash['branch_id'],
                        'journal_voucher_period' 		=> $journal_voucher_period,
                        'journal_voucher_date'			=> date('Y-m-d'),
                        'journal_voucher_title'			=> 'ANGSURAN AUTO DEBET '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
                        'journal_voucher_description'	=> 'ANGSURAN AUTO DEBET '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
                        'transaction_module_id'			=> $transaction_module_id,
                        'transaction_module_code'		=> $transaction_module_code,
                        'transaction_journal_id' 		=> $acctcashpayment_last['credits_payment_id'],
                        'transaction_journal_no' 		=> $acctcashpayment_last['credits_account_serial'],
                        'created_id' 					=> $data_cash['created_id'],
                    );

                    AcctJournalVoucher::create($data_journal);

                    $journal_voucher_id 		= AcctJournalVoucher::where('created_id',$data_cash['created_id'])
                    ->orderBy('journal_voucher_id','DESC')
                    ->first()
                    ->journal_voucher_id;

                    $savingsaccount_id 			= AcctSavings::where('savings_id',$mutasi_data['savings_id'])
                    ->first()
                    ->account_id;

                    $account_id_default_status 	= AcctAccount::where('account_id',$savingsaccount_id)
                    ->where('data_state',0)
                    ->first()
                    ->account_default_status;

                    $data_debet = array (
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $savingsaccount_id,
                        'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                        'journal_voucher_amount'		=> $data_cash['credits_payment_amount'],
                        'journal_voucher_debit_amount'	=> $data_cash['credits_payment_amount'],
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 0,
                        'created_id' 					=> $user_id,
                    );

                    AcctJournalVoucherItem::create($data_debet);

                    $receivable_account_id 		= AcctCredits::where('credits_id',$acctcashpayment_last['credits_id'])
                    ->first()
                    ->receivable_account_id;

                    $account_id_default_status 	= AcctAccount::where('account_id',$receivable_account_id)
                    ->where('data_state',0)
                    ->first()
                    ->account_default_status;

                    $data_credit = array (
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $receivable_account_id,
                        'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                        'journal_voucher_amount'		=> $data_cash['credits_payment_principal'],
                        'journal_voucher_credit_amount'	=> $data_cash['credits_payment_principal'],
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 1,
                        'created_id' 					=> $user_id,
                    );

                    AcctJournalVoucherItem::create($data_credit);

                    $account_id_default_status 			=  AcctAccount::where('account_id',$preferencecompany['account_interest_id'])
                    ->where('data_state',0)
                    ->first()
                    ->account_default_status;

                    $data_credit =array(
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $preferencecompany['account_interest_id'],
                        'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                        'journal_voucher_amount'		=> $data_cash['credits_payment_interest'],
                        'journal_voucher_credit_amount'	=> $data_cash['credits_payment_interest'],
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 1,
                        'created_id' 					=> $user_id
                    );

                    AcctJournalVoucherItem::create($data_credit);

                    if($data_cash['credits_interest_income'] > 0){

                        $account_id_default_status 			= AcctAccount::where('account_id',$preferencecompany['account_interest_income_id'])
                        ->where('data_state',0)
                        ->first()
                        ->account_default_status;

                        $data_credit =array(
                            'journal_voucher_id'			=> $journal_voucher_id,
                            'account_id'					=> $preferencecompany['account_interest_income_id'],
                            'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                            'journal_voucher_amount'		=> $data_cash['credits_interest_income'],
                            'journal_voucher_credit_amount'	=> $data_cash['credits_interest_income'],
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 1,
                            'created_id' 					=> $user_id,
                        );

                        AcctJournalVoucherItem::create($data_credit);
                    }

                    if($data_cash['credits_others_income'] > 0){

                        $account_id_default_status 			= AcctAccount::where('account_id',$preferencecompany['account_others_income_id'])
                        ->where('data_state',0)
                        ->first()
                        ->account_default_status;

                        $data_credit =array(
                            'journal_voucher_id'			=> $journal_voucher_id,
                            'account_id'					=> $preferencecompany['account_others_income_id'],
                            'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                            'journal_voucher_amount'		=> $data_cash['credits_others_income'],
                            'journal_voucher_credit_amount'	=> $data_cash['credits_others_income'],
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 1,
                            'created_id' 					=> $user_id,
                        );

                        AcctJournalVoucherItem::create($data_credit);
                    }

                    if($data_cash['credits_payment_fine'] > 0){

                        $account_id_default_status 			= AcctAccount::where('account_id',$savingsaccount_id)
                        ->where('data_state',0)
                        ->first()
                        ->account_default_status;

                        $data_debit =array(
                            'journal_voucher_id'			=> $journal_voucher_id,
                            'account_id'					=> $savingsaccount_id,
                            'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                            'journal_voucher_amount'		=> $data_cash['credits_payment_fine'],
                            'journal_voucher_debit_amount'	=> $data_cash['credits_payment_fine'],
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 0,
                            'created_id' 					=> $user_id,
                        );

                        AcctJournalVoucherItem::create($data_debit);

                        $account_id_default_status 			= AcctAccount::where('account_id',$preferencecompany['account_credits_payment_fine'])
                        ->where('data_state',0)
                        ->first()
                        ->account_default_status;

                        $data_credit =array(
                            'journal_voucher_id'			=> $journal_voucher_id,
                            'account_id'					=> $preferencecompany['account_credits_payment_fine'],
                            'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                            'journal_voucher_amount'		=> $data_cash['credits_payment_fine'],
                            'journal_voucher_credit_amount'	=> $data_cash['credits_payment_fine'],
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 1,
                            'created_id' 					=> $user_id,
                        );

                        AcctJournalVoucherItem::create($data_credit);
                    }

                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    public function autoDebetMandatorySavings($user_id, $branch_id, $username)
    {
        $coremember	= CoreMember::with('savingacc')->get();

        DB::beginTransaction();

        try {

            foreach($coremember as $key => $value){
                foreach ($value->savingacc as $key => $val) {
                if($val['member_mandatory_savings'] <= $val['savings_account_last_balance']){
                    if($val['member_mandatory_savings_last_balance'] == 0){
                        $data = array(
                            'branch_id'										=> $branch_id,
                            'member_id'										=> $value['member_id'],
                            'savings_id'									=> $val['savings_id'],
                            'savings_account_id'							=> $val['savings_account_id'],
                            'mutation_id'									=> 5,
                            'member_transfer_mutation_date'					=> date('Y-m-d'),
                            'member_mandatory_savings_opening_balance'		=> $value['member_mandatory_savings_last_balance'],
                            'member_mandatory_savings'						=> $value['member_mandatory_savings'],
                            'member_mandatory_savings_last_balance'			=> $value['member_mandatory_savings_last_balance'] + $value['member_mandatory_savings'],
                            'operated_name'									=> $username,
                            'created_id'									=> $user_id,
                        );

                        $transaction_module_code = "AGTTR";

                        $transaction_module_id 	= PreferenceTransactionModule::where('transaction_module_code',$transaction_module_code)
                        ->first()
                        ->transaction_module_id;

                        if(CoreMemberTransferMutation::create($data)){

                            $membertransfer_last 	= CoreMemberTransferMutation::select('core_member.member_name','core_member.member_transfer_mutation_id','core_member.member_no')
                            ->join('core_member','core_member_transfer_mutation.member_id','=','core_member.member_id')
                            ->where('core_member_transfer_mutation.created_id', $data['created_id'])
                            ->orderBy('core_member_transfer_mutation.member_transfer_mutation_id','DESC')
                            ->first();

                            $journal_voucher_period = date("Ym", strtotime($data['member_transfer_mutation_date']));

                            $data_journal = array(
                                'branch_id'						=> $branch_id,
                                'journal_voucher_period' 		=> $journal_voucher_period,
                                'journal_voucher_date'			=> $data['member_transfer_mutation_date'],
                                'journal_voucher_title'			=> 'AUTO DEBET SIMPANAN WAJIB '.$membertransfer_last['member_name'],
                                'journal_voucher_description'	=> 'AUTO DEBET SIMPANAN WAJIB '.$membertransfer_last['member_name'],
                                'transaction_module_id'			=> $transaction_module_id,
                                'transaction_module_code'		=> $transaction_module_code,
                                'transaction_journal_id' 		=> $membertransfer_last['member_transfer_mutation_id'],
                                'transaction_journal_no' 		=> $membertransfer_last['member_no'],
                                'created_id' 					=> $data['created_id'],
                            );

                            AcctJournalVoucher::create($data_journal);

                            $journal_voucher_id = AcctJournalVoucher::where('created_id',$data['created_id'])
                            ->orderBy('journal_voucher_id','DESC')
                            ->first()
                            ->journal_voucher_id;

                            $preferencecompany 	= PreferenceCompany::first();

                            $account_id 		= AcctSavings::where('savings_id',$data['savings_id'])
                            ->first()
                            ->account_id;

                            $account_id_default_status = AcctAccount::where('account_id',$account_id)
                            ->where('data_state',0)
                            ->first()
                            ->account_default_status;

                            $data_debit = array(
                                'journal_voucher_id'			=> $journal_voucher_id,
                                'account_id'					=> $account_id,
                                'journal_voucher_description'	=> 'AUTO DEBET SIMPANAN WAJIB '.$value->member_name,
                                'journal_voucher_amount'		=> $data['member_mandatory_savings'],
                                'journal_voucher_debit_amount'	=> $data['member_mandatory_savings'],
                                'account_id_status'				=> 1,
                                'created_id'					=> $user_id,
                                'account_id_default_status'		=> $account_id_default_status,
                            );

                            AcctJournalVoucherItem::create($data_debit);

                            $account_id = AcctSavings::where('savings_id',$preferencecompany['mandatory_savings_id'])
                            ->first()
                            ->account_id;

                            $account_id_default_status = AcctAccount::where('account_id',$account_id)
                            ->where('data_state',0)
                            ->first()
                            ->account_default_status;

                            $data_credit =array(
                                'journal_voucher_id'			=> $journal_voucher_id,
                                'account_id'					=> $account_id,
                                'journal_voucher_description'	=> 'AUTO DEBET SIMPANAN WAJIB '.$value->member_name,
                                'journal_voucher_amount'		=> $data['member_mandatory_savings'],
                                'journal_voucher_credit_amount'	=> $data['member_mandatory_savings'],
                                'account_id_status'				=> 0,
                                'created_id'					=> $user_id,
                                'account_id_default_status'		=> $account_id_default_status,
                            );

                            AcctJournalVoucherItem::create($data_credit);
                        }

                    }else{
                        $lastmembertransfermutation = CoreMemberTransferMutation::where('member_id',$val['member_id'])
                        ->where('data_state', 0)
                        ->orderBy('member_transfer_mutation_id','DESC')
                        ->first();
                        if($lastmembertransfermutation['member_transfer_mutation_date'] <=  date("Y-m-d", strtotime("-1 months"))){
                            $data = array(
                                'branch_id'										=> $branch_id,
                                'member_id'										=> $val['member_id'],
                                'savings_id'									=> $val['savings_id'],
                                'savings_account_id'							=> $val['savings_account_id'],
                                'mutation_id'									=> 5,
                                'member_transfer_mutation_date'					=> date('Y-m-d'),
                                'member_mandatory_savings_opening_balance'		=> $value['member_mandatory_savings_last_balance'],
                                'member_mandatory_savings'						=> $value['member_mandatory_savings'],
                                'member_mandatory_savings_last_balance'			=> $value['member_mandatory_savings_last_balance'] + $value['member_mandatory_savings'],
                                'operated_name'									=> $username,
                                'created_id'									=> $user_id,
                            );

                            $transaction_module_code = "AGTTR";

                            $transaction_module_id 	=PreferenceTransactionModule::where('transaction_module_code',$transaction_module_code)
                            ->first()
                            ->transaction_module_id;

                            if(CoreMemberTransferMutation::create($data)){
                                $membertransfer_last 	= CoreMemberTransferMutation::select('core_member.member_name','core_member.member_transfer_mutation_id','core_member.member_no')
                                ->join('core_member','core_member_transfer_mutation.member_id','=','core_member.member_id')
                                ->where('core_member_transfer_mutation.created_id', $data['created_id'])
                                ->orderBy('core_member_transfer_mutation.member_transfer_mutation_id','DESC')
                                ->first();

                                $journal_voucher_period = date("Ym", strtotime($data['member_transfer_mutation_date']));

                                $data_journal = array(
                                    'branch_id'						=> $branch_id,
                                    'journal_voucher_period' 		=> $journal_voucher_period,
                                    'journal_voucher_date'			=> $data['member_transfer_mutation_date'],
                                    'journal_voucher_title'			=> 'AUTO DEBET SIMPANAN WAJIB '.$membertransfer_last['member_name'],
                                    'journal_voucher_description'	=> 'AUTO DEBET SIMPANAN WAJIB '.$membertransfer_last['member_name'],
                                    'transaction_module_id'			=> $transaction_module_id,
                                    'transaction_module_code'		=> $transaction_module_code,
                                    'transaction_journal_id' 		=> $membertransfer_last['member_transfer_mutation_id'],
                                    'transaction_journal_no' 		=> $membertransfer_last['member_no'],
                                    'created_id' 					=> $data['created_id'],
                                );

                                AcctJournalVoucher::create($data_journal);

                                $journal_voucher_id = AcctJournalVoucher::where('created_id',$data['created_id'])
                                ->orderBy('journal_voucher_id','DESC')
                                ->first()
                                ->journal_voucher_id;

                                $preferencecompany 	= PreferenceCompany::first();

                                $account_id 		= AcctSavings::where('savings_id',$data['savings_id'])
                                ->first()
                                ->account_id;

                                $account_id_default_status = AcctAccount::where('account_id',$account_id)
                                ->where('data_state',0)
                                ->first()
                                ->account_default_status;

                                $data_debit = array(
                                    'journal_voucher_id'			=> $journal_voucher_id,
                                    'account_id'					=> $account_id,
                                    'journal_voucher_description'	=> 'AUTO DEBET SIMPANAN WAJIB '.$value->member_name,
                                    'journal_voucher_amount'		=> $data['member_mandatory_savings'],
                                    'journal_voucher_debit_amount'	=> $data['member_mandatory_savings'],
                                    'account_id_status'				=> 1,
                                    'created_id'					=> $user_id,
                                    'account_id_default_status'		=> $account_id_default_status,
                                );

                                AcctJournalVoucherItem::create($data_debit);

                                $account_id = AcctSavings::where('savings_id',$preferencecompany['mandatory_savings_id'])
                                ->first()
                                ->account_id;

                                $account_id_default_status = AcctAccount::where('account_id',$account_id)
                                ->where('data_state',0)
                                ->first()
                                ->account_default_status;

                                $data_credit =array(
                                    'journal_voucher_id'			=> $journal_voucher_id,
                                    'account_id'					=> $account_id,
                                    'journal_voucher_description'	=> 'AUTO DEBET SIMPANAN WAJIB '.$value->member_name,
                                    'journal_voucher_amount'		=> $data['member_mandatory_savings'],
                                    'journal_voucher_credit_amount'	=> $data['member_mandatory_savings'],
                                    'account_id_status'				=> 0,
                                    'created_id'					=> $user_id,
                                    'account_id_default_status'		=> $account_id_default_status,
                                );

                                AcctJournalVoucherItem::create($data_credit);
                            }
                        }
                    }
                }
            }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }
}
