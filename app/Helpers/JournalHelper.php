<?php
namespace App\Helpers;
use App\Models\AcctAccount;
use App\Models\AcctAccountSetting;
use App\Models\AcctCreditsAccount;
use App\Models\AcctDepositoAccount;
use App\Models\AcctJournalVoucher as JournalVoucher;
use App\Models\AcctJournalVoucherItem as JournalVoucherItem;
use App\Models\AcctSavingsAccount;
use App\Models\PreferenceTransactionModule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class JournalHelper
{
    protected static $journal_voucher_description;
    protected static $journal_voucher_title;
    protected static $transaction_journal_id;
    protected static $transaction_journal_no;
    protected static $journal_period;
    protected static $journal_date;
    protected static $journal_token;
    protected static $transaction_module_code;
    protected static $saving_data;
    /**
     * Set Journal Description (and Title)
     *
     * @param mixed $description
     * @param mixed $title
     * @return JournalHelper
     */
    public static function description($description,$title=null) {
         self::$journal_voucher_description = $description;
         if(!is_null($title)||!empty($title)){
             self::$journal_voucher_title = $title;
         }
         return new JournalHelper();
    }
    /**
     * Set Transaction Journal id (and no)
     *
     * @param int $id
     * @param int $no
     * @return JournalHelper
     */
    public static function journalId($id,$no=null) {
         self::$transaction_journal_id = $id;
         if(!is_null($no)||!empty($no)){
             self::$transaction_journal_no = $no;
         }
         return new JournalHelper();
    }
    /**
     * Set Transaction Journal no (and id)
     *
     * @param int $id
     * @param int $no
     * @return JournalHelper
     */
    public static function journalNo($no,$id=null) {
         self::$transaction_journal_no = $no;
         if(!is_null($id)||!empty($id)){
             self::$transaction_journal_id = $id;
         }
         return new JournalHelper();
    }
    /**
     * Set Journal Title (and Description)
     *
     * @param mixed $title
     * @param mixed $description
     * @return JournalHelper
     */
    public static function title($title,$description=null) {
        self::$journal_voucher_title = $title;
        if(!is_null($description)||!empty($description)){
            self::$journal_voucher_description = $description;
        }
        return new JournalHelper();
    }
    /**
     * Set credit account data to be used
     * used to set transaction id and no
     * @param int $credits_account_id
     * @return JournalHelper
     */
    public static function credit($credits_account_id) {
        $data = AcctCreditsAccount::find($credits_account_id);
        self::$transaction_journal_id = $credits_account_id;
        self::$transaction_journal_no = $data->credits_account_serial;
        return new JournalHelper();
    }
    /**
     * Set credit account data to be used
     * used to set transaction id and no
     * @param int $savings_account_id
     * @return JournalHelper
     */
    public static function saving($savings_account_id) {
        $data = AcctSavingsAccount::find($savings_account_id);
        self::$saving_data=$data;
        self::$transaction_journal_id = $savings_account_id;
        self::$transaction_journal_no = $data->savings_account_no;
        return new JournalHelper();
    }
    /**
     * Set credit account data to be used
     * used to set transaction id and no
     * @param int $deposito_account_id
     * @return JournalHelper
     */
    public static function deposito($deposito_account_id) {
        $data = AcctDepositoAccount::find($deposito_account_id);
        self::$transaction_journal_id = $deposito_account_id;
        self::$transaction_journal_no = $data->deposito_account_no;
        return new JournalHelper();
    }
    public static function token($token=null) {
        self::$journal_token=$token;
        if(empty($token)||is_null($token)){
            $token = Str::uuid();
        }
        return new JournalHelper();
    }
    /**
     * Set Transaction Module Code
     *
     * @param mixed|string $transaction_module_code
     * @return JournalHelper
     */
    public static function code($transaction_module_code) {
         self::$transaction_module_code = $transaction_module_code;
         return new JournalHelper();
    }
      /**
     * Make journal voucher and journal voucher item
     * if journal title not set, description will used as title
     *
     * @param [uuid] token
     * @param string $journal_voucher_description
     * @param array $account_setting_name
     * @param integer $total_amount
     * @param string|null $transaction_module_code
     * @return void
     */
    public static function make(array $account_setting_name,int $total_amount,string $transaction_module_code = null,string $journal_voucher_description=null,int $transaction_journal_id=null,int $transaction_journal_no=null,$token = null,$date=null){
        if(is_null($transaction_module_code)){
            if(is_null(self::$transaction_module_code)||empty(self::$transaction_module_code)){
                $transaction_module_code = preg_replace('/[^A-Z]/', '',$journal_voucher_description);
            }else{
                $transaction_module_code = self::$transaction_module_code;
            }
        }
        if(is_null($token)){
            self::token();
            $token = self::$journal_token;
        }
        if(is_null($journal_voucher_description)){
            $journal_voucher_description=self::$journal_voucher_description;
        }
        $journal_voucher_title = self::$journal_voucher_title;
        if(is_null($journal_voucher_title)){
            $journal_voucher_title=$journal_voucher_description;
        }
        $date = (is_null($date)?Carbon::now()->format('Y-m-d'):Carbon::parse($date)->format('Y-m-d'));
        if(!is_null(self::$journal_date)||!empty(self::$journal_date)){
            $date = self::$journal_date;
        }
        $period = (is_null($date)?Carbon::now()->format('Ym'):Carbon::parse($date)->format('Ym'));
        if(!is_null(self::$journal_period)||!empty(self::$journal_period)){
            $period = self::$journal_period;
        }
        JournalVoucher::create([
            'branch_id'                     => Auth::user()->branch_id,
            'journal_voucher_status'        => 1,
            'journal_voucher_description'   => $journal_voucher_description,
            'journal_voucher_title'         => $journal_voucher_title,
            'transaction_module_id'         => self::getTransactionModule($transaction_module_code)->id??'',
            'transaction_module_code'       => $transaction_module_code,
            'transaction_journal_id' 		=> $transaction_journal_id,
            'transaction_journal_no' 		=> $transaction_journal_no,
            'journal_voucher_date'          => $date,
            'journal_voucher_period'        => $period,
            'created_id'                    => Auth::id(),
            'journal_voucher_token'         => $token,
        ]);
        $jv = JournalVoucher::where('journal_voucher_token',$token)->first();
        foreach ($account_setting_name as $name){
            $account_id = self::getAccountSetting($name)->account_id;
            $account_setting_status = self::getAccountSetting($name)->status;
            if ($account_setting_status == 0){
                $debit_amount = $total_amount;
                $credit_amount = 0;
            } else {
                $debit_amount = 0;
                $credit_amount = $total_amount;
            }
            //* buat journal item
            JournalVoucherItem::create([
                'merchat_id' => Auth::user()->merchant_id??1,
                'company_id'        => Auth::user()->company_id,
                'journal_voucher_id'=>$jv->journal_voucher_id,
                'account_id'                    => $account_id,
                'journal_voucher_amount'        => $total_amount,
                'account_id_default_status'     => self::getAccountDefaultStatus($account_id),
                'account_id_status'             => $account_setting_status,
                'journal_voucher_debit_amount'  => $debit_amount,
                'journal_voucher_credit_amount' => $credit_amount,
                'updated_id'                    => Auth::id(),
                'created_id'                    => Auth::id()
            ]);
        }
    }
    /**
     * Reverse journal
     *
     * @param integer $journal_voucher_id
     * @return void
     */
    public static function reverse(int $journal_voucher_id){
        $token = Str::uuid();
        $journal = JournalVoucher::with('items')->find($journal_voucher_id);
        JournalVoucher::create([
            'company_id' => $journal->company_id,
            'transaction_module_id' => $journal->transaction_module_id,
            'journal_voucher_status' => $journal->journal_voucher_status,
            'transaction_journal_no' =>  $journal->transaction_journal_no,
            'transaction_module_code' => 'H'.$journal->transaction_module_code,
            'journal_voucher_date' =>(Carbon::parse($journal->journal_voucher_date)->format('Y-m')==date('Y-m')?date('Y-m-d'):$journal->journal_voucher_date),
            'journal_voucher_description' => (!is_null($journal->journal_voucher_description)?'Hapus '.Str::upper($journal->journal_voucher_description):''),
            'journal_voucher_period' => $journal->journal_voucher_period,
            'journal_voucher_title' =>  (!is_null($journal->journal_voucher_title)?'Hapus '. Str::upper($journal->journal_voucher_title):''),
            "data_state" => $journal->data_state,
            "journal_voucher_token" => $token,
            "reverse_state" => 1,
            'created_id' => Auth::id()
        ]);
        $journal->reverse_state = 1;
        $journal->save();
        $jv = JournalVoucher::where('journal_voucher_token', $token)->first();
        foreach ($journal->items as $key ){
        JournalVoucherItem::create([
            'company_id' => $key['company_id'],
            'journal_voucher_id' => $jv['journal_voucher_id'],
            'account_id' => $key['account_id'],
            'journal_voucher_amount' => $key['journal_voucher_amount'],
            'account_id_status' => (1-$key['account_id_status']),
            'account_id_default_status' => $key['account_id_default_status'],
            'journal_voucher_debit_amount' => $key['journal_voucher_credit_amount'],
            'journal_voucher_credit_amount' => $key['journal_voucher_debit_amount'],
            "data_state" => $key['data_state'],
            "reverse_state" => 1,
            'updated_id' => Auth::id(),
            'created_id' => Auth::id()
        ]);
        }
        return $journal->items()->update(['acct_journal_voucher_item.reverse_state' => 1]);
    }
     /**
     * Get Transaction Module
     *
     * @param [string] $transaction_module_code
     * @return Collection
     */
    public static function getTransactionModule(string $transaction_module_code)
    {
        return PreferenceTransactionModule::select(['transaction_module_name as name','transaction_module_id as id'])->where('transaction_module_code',$transaction_module_code)->first();
    }
     /**
     * Get Account Seting status and account id
     *
     * @param string $account_setting_name
     * @return Collection
     */
    public static function getAccountSetting(string $account_setting_name){
        return AcctAccountSetting::select(['account_setting_status as status','account_id'])->where('company_id', Auth::user()->company_id)->where('account_setting_name', $account_setting_name)->first();
    }
    /**
     * Get account default status
     *
     * @param [int] $account_id
     * @return string
     */
    public static function getAccountDefaultStatus(int $account_id)
    {
        $data = AcctAccount::where('account_id',$account_id)->first();
        return $data->account_default_status;
    }
}