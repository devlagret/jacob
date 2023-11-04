<?php
namespace App\Helpers;
use App\Models\AcctMutation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\AcctJournalVoucher as JournalVoucher;
use App\Models\AcctJournalVoucherItem as JournalVoucherItem;
use App\Models\AcctSavingsAccount;

class JournalSavingAccHelper extends JournalHelper{
    protected $principal,$madatory,$special,$token;
    protected $journal_voucher_id;
    protected $member;
    protected $journal_item_title;
    protected $journal_item_desctiption;
    public static function find(int $saving_account_id) {
         parent::saving($saving_account_id);
         $hlp = new JournalSavingAccHelper();
         $hlp->setmember($saving_account_id);
         $hlp->token();
         return $hlp;
    }
    public function mutation($mutation_id,$principal=0,$madatory=0,$special=0) {
         $mutation = self::mutationData($mutation_id);
         JournalVoucher::create([
            'branch_id'                     => Auth::user()->branch_id,
            'journal_voucher_status'        => 1,
            'journal_voucher_description'   => "{$mutation->mutation_journal_desc} {$this->member->member_name}",
            'journal_voucher_title'         => parent::$journal_voucher_title,
            'transaction_module_id'         => self::getTransactionModule(parent::$transaction_module_code)->id??'',
            'transaction_module_code'       => parent::$transaction_module_code,
            'transaction_journal_id' 		=> parent::$transaction_journal_id,
            'transaction_journal_no' 		=> parent::$transaction_journal_no,
            'journal_voucher_date'          => parent::$journal_date,
            'journal_voucher_period'        => parent::$journal_period,
            'created_id'                    => Auth::id(),
            'journal_voucher_token'         => $this->token,
        ]);
    }
    public static function make(array $account_setting_name=null,int $total_amount=null,string $transaction_module_code = null,string $journal_voucher_description=null,int $transaction_journal_id=null,int $transaction_journal_no=null,$token = null,$date=null) {
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
    }
    public function setorTab(string $desctiption = "SETORAN TABUNGAN",string $transaction_module_code="TAB") {
        $title =parent::$journal_voucher_title;
        if(is_null($title)){
            $title = $desctiption;
        }
        $transaction_mod_code = self::$transaction_module_code;
        if(is_null($transaction_mod_code)){
            $transaction_mod_code = $transaction_module_code;
        }
        JournalVoucher::create([
            'branch_id'                     => Auth::user()->branch_id,
            'journal_voucher_status'        => 1,
            'journal_voucher_description'   => "{$desctiption} {$this->member->member_name}",
            'journal_voucher_title'         => "{$title} {$this->member->member_name}",
            'transaction_module_id'         => self::getTransactionModule($transaction_mod_code)->id??'',
            'transaction_module_code'       => $transaction_mod_code,
            'transaction_journal_id' 		=> parent::$transaction_journal_id,
            'transaction_journal_no' 		=> parent::$transaction_journal_no,
            'journal_voucher_date'          => parent::$journal_date,
            'journal_voucher_period'        => parent::$journal_period,
            'created_id'                    => Auth::id(),
            'journal_voucher_token'         => $this->token,
        ]);
        $id = JournalVoucher::where('journal_voucher_token',$this->token)
        return JournalItemHelperSetoranTabugan::class;
    }
    public static function mutationType($mutation_id=null) {
        $data = AcctMutation::where('data_state',0)->get()->pluck('mutation_name','mutation_id');
        if(empty($mutation_id)){
            return $data;
        }
        return $data[$mutation_id];
    }
    public static function mutationData($mutation_id=null) {
        $data = AcctMutation::where('data_state',0);
        if(!empty($mutation_id)){
            return $data->where('mutation_id',$mutation_id)->first();
        }
        return $data->get();
    }
    public function setmember($saving_account_id) {
        $data=AcctSavingsAccount::without('member')->find($saving_account_id);
        self::$member = $data->member;
    }
    public function setJournalVoucherId($id) {
         $this->journal_voucher_id = $id;
         return $this;
    }
    public static function token($token=null) {
        if(!empty(parent::$journal_token)&&!is_null(parent::$journal_token)){
            self::$token=parent::$journal_token;
        }elseif(empty($token)||is_null($token)){
            $token = Str::uuid();
        }
        self::$token=$token;
        return new JournalSavingAccHelper();
    }
}