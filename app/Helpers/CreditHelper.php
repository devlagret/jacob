<?php
namespace App\Helpers;

use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPaymentSuspend;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CreditHelper{
    protected $credits_account_date;
    protected $credits_payment_to;
    protected $credits_account_interest;
    protected $total_credits_account;
    protected $creditData;
    /**
     * Get Schedule
     * leave param empty if want input data manualy
     * @param int $credit_account_id
     * @return CreditHelper
     */
    public static function sedule($credit_account_id=null) {
        $ch = new CreditHelper();
        if(!is_null($credit_account_id)){
            $cd = AcctCreditsAccount::find($credit_account_id);
            $ch->credits_account_date=$cd->credits_account_date;
            $ch->total_credits_account=$cd->credits_account_amount;
            $ch->credits_account_interest=$cd->credits_account_interest;
            $ch->credits_payment_to=0;
            $ch->setData($cd);
        }
        return $ch;
    }
    /**
     * Get Schedule Suspended payment
     * leave param empty if want input data manualy
     * @param int $credit_account_id
     * @return CreditHelper
     */
    public static function reSedule($credits_payment_suspend_id=null) {
        $ch = new CreditHelper();
        if(!is_null($credits_payment_suspend_id)){
            $cs = AcctCreditsPaymentSuspend::with('account')->find($credits_payment_suspend_id);
            $ch->credits_account_date=$cs->credits_payment_date_new;
            $ch->credits_payment_to=$cs->credits_account_payment_to;
            $ch->total_credits_account=$cs->credits_account_last_balance;
            $ch->credits_account_interest=$cs->account->credits_account_interest;
            $ch->setData($cs->account);
        }
        return $ch;
    }
    /**
     * Set Credit Account data
     *
     * @param mixed $data
     * @return void
     */
    public function setData($data) {
         $this->creditData = $data;
    }
    /**
     * Get scedule flat
     *
     * @param [type] $credits_account_amount
     * @param [type] $credits_account_principal_amount
     * @param [type] $credits_account_interest
     * @param [type] $credits_account_period
     * @param [type] $credits_account_date
     * @param integer $credits_payment_period
     * @return Collection
     */
    public function flat($total_credits_account=null,$credits_account_principal_amount=null,$credits_account_interest_amount=null,$credits_account_period=null,$credits_account_date=null,$credits_payment_period=1){
        $credits_payment_to=1;
        if(!empty($this->creditData)){
            $total_credits_account 		= $this->total_credits_account;
            $credits_account_interest_amount 	= $this->creditData->credits_account_interest_amount;
            $credits_account_period 	= $this->creditData->credits_account_period;
            $credits_account_principal_amount 	= $this->creditData->credits_account_principal_amount;
            $credits_payment_period     = $this->creditData->credits_payment_period;
            $credits_account_date       = $this->credits_account_date;
        }
        if(!empty($this->credits_payment_to)){
            $credits_payment_to         = $this->credits_payment_to;
        }
        $data	= collect();
        $opening_balance				= $total_credits_account;
        $period = self::paymentPeriod($credits_payment_period);
        for($i=$credits_payment_to; $i<=$credits_account_period-1; $i++){
            $row	= collect();
            $tanggal_angsuran = Carbon::parse($credits_account_date)->add(($i-$credits_payment_to),$period)->format('d-m-Y');
            $angsuran_pokok									= $credits_account_principal_amount;
            $angsuran_margin								= $credits_account_interest_amount;
            $angsuran 										= $angsuran_pokok + $angsuran_margin;
            $last_balance 									= $opening_balance - $angsuran_pokok;
            $row->put('opening_balance',$opening_balance);
            $row->put('ke',$i+1);
            $row->put('tanggal_angsuran',$tanggal_angsuran);
            $row->put('angsuran',$angsuran);
            $row->put('angsuran_pokok',$angsuran_pokok);
            $row->put('angsuran_bunga',$angsuran_margin);
            $row->put('last_balance',$last_balance);
            $opening_balance = $last_balance;
            $data->push($row);
        }
        return $data;
    }
    public function anuitas($total_credits_account=null,$credits_account_interest=null,$credits_account_period=null,$credits_account_date=null,$credits_payment_period=1)
    {
        $bunga 		= $credits_account_interest / 100;
        $credits_payment_to=1;
        if(!empty($this->creditData)){
            $total_credits_account 		= $this->total_credits_account;
            $credits_account_period 	= $this->creditData->credits_account_period;
            $credits_payment_period     = $this->creditData->credits_payment_period;
            $credits_account_date       = $this->credits_account_date;
            $bunga   = $this->credits_account_interest/ 100;
        }
        if(!empty($this->credits_payment_to)){
            $credits_payment_to         = $this->credits_payment_to;
        }
        $data	= collect();
        $totangsuran 	= round(($total_credits_account*($bunga))+$total_credits_account/$credits_account_period);
        $rate			= $this->rate3($credits_account_period, $totangsuran, $total_credits_account);
        $period = self::paymentPeriod($credits_payment_period);
        $sisapinjaman = $total_credits_account;
        for ($i=$credits_payment_to; $i <= $credits_account_period-1 ; $i++) {
            $row	= collect();
            $tanggal_angsuran = Carbon::parse($credits_account_date)->add(($i-$credits_payment_to),$period)->format('d-m-Y');
            $angsuranbunga 		= $sisapinjaman * $rate;
            $angsuranpokok 		= $totangsuran - $angsuranbunga;
            $sisapokok 			= $sisapinjaman - $angsuranpokok;
            $row->put('opening_balance',$sisapinjaman);
            $row->put('ke',$i+1);
            $row->put('tanggal_angsuran',$tanggal_angsuran);
            $row->put('angsuran',$totangsuran);
            $row->put('angsuran_pokok',$angsuranpokok);
            $row->put('angsuran_bunga',$angsuranbunga);
            $row->put('last_balance',$sisapokok);
            $sisapinjaman = $sisapinjaman - $angsuranpokok;
            $data->push($row);
        }
        return $data;
    }
    public function slidingrate($credits_account_amount=null,$credits_account_principal_amount=null,$credits_account_interest=null,$credits_account_period=null,$credits_account_date=null,$credits_payment_period=1){
        $credistaccount					= AcctCreditsAccount::select('acct_credits_account.*', 'core_member.member_name', 'core_member.member_no', 'core_member.member_address', 'core_member.province_id', 'core_province.province_name','core_member.member_mother', 'core_member.city_id', 'core_city.city_name', 'core_member.kecamatan_id', 'core_kecamatan.kecamatan_name', 'acct_credits.credits_id','core_member.member_identity', 'core_member.member_identity_no', 'acct_credits.credits_name', 'core_branch.branch_name', 'core_member.member_phone', 'core_member_working.member_company_name', 'core_member_working.member_company_job_title', 'core_member.member_mandatory_savings_last_balance', 'core_member.member_principal_savings_last_balance')
        ->join('core_branch', 'acct_credits_account.branch_id','=','core_branch.branch_id')
        ->join('acct_credits', 'acct_credits_account.credits_id','=','acct_credits.credits_id')
        ->join('core_member', 'acct_credits_account.member_id','=','core_member.member_id')
        ->join('core_member_working', 'acct_credits_account.member_id','=','core_member_working.member_id')
        ->join('core_province', 'core_member.province_id','=','core_province.province_id')
        ->join('core_city', 'core_member.city_id','=','core_city.city_id')
        ->join('core_kecamatan', 'core_member.kecamatan_id','=','core_kecamatan.kecamatan_id')
        ->where('acct_credits_account.data_state', 0)
        ->where('acct_credits_account.credits_account_id', $id)
        ->first();

        $total_credits_account 			= ($credistaccount['credits_account_amount']??0);
        $credits_account_interest 		= ($credistaccount['credits_account_interest']??0);
        $credits_account_period 		= ($credistaccount['credits_account_period']??0);

        $installment_pattern			= array();
        $opening_balance				= $total_credits_account;

        for($i=1; $i<=$credits_account_period; $i++){

            if($credistaccount['credits_payment_period'] == 2){
                $a = $i * 7;

                $tanggal_angsuran 								= date('d-m-Y', strtotime("+".$a." days", strtotime($credistaccount['credits_account_date'])));

            } else {

                $tanggal_angsuran 								= date('d-m-Y', strtotime("+".$i." months", strtotime($credistaccount['credits_account_date'])));
            }

            $angsuran_pokok									= ($credistaccount['credits_account_amount']??0)/$credits_account_period;

            $angsuran_margin								= $opening_balance*$credits_account_interest/100;

            $angsuran 										= $angsuran_pokok + $angsuran_margin;

            $last_balance 									= $opening_balance - $angsuran_pokok;

            $installment_pattern[$i]['opening_balance']		= $opening_balance;
            $installment_pattern[$i]['ke'] 					= $i;
            $installment_pattern[$i]['tanggal_angsuran'] 	= $tanggal_angsuran;
            $installment_pattern[$i]['angsuran'] 			= $angsuran;
            $installment_pattern[$i]['angsuran_pokok']		= $angsuran_pokok;
            $installment_pattern[$i]['angsuran_bunga'] 		= $angsuran_margin;
            $installment_pattern[$i]['last_balance'] 		= $last_balance;

            $opening_balance 								= $last_balance;
        }

        return $installment_pattern;

    }

    public function menurunharian($credits_account_amount=null,$credits_account_principal_amount=null,$credits_account_interest=null,$credits_account_period=null,$credits_account_date=null,$credits_payment_period=1){
        $credistaccount					= AcctCreditsAccount::select('acct_credits_account.*', 'core_member.member_name', 'core_member.member_no', 'core_member.member_address', 'core_member.province_id', 'core_province.province_name','core_member.member_mother', 'core_member.city_id', 'core_city.city_name', 'core_member.kecamatan_id', 'core_kecamatan.kecamatan_name', 'acct_credits.credits_id','core_member.member_identity', 'core_member.member_identity_no', 'acct_credits.credits_name', 'core_branch.branch_name', 'core_member.member_phone', 'core_member_working.member_company_name', 'core_member_working.member_company_job_title', 'core_member.member_mandatory_savings_last_balance', 'core_member.member_principal_savings_last_balance')
        ->join('core_branch', 'acct_credits_account.branch_id','=','core_branch.branch_id')
        ->join('acct_credits', 'acct_credits_account.credits_id','=','acct_credits.credits_id')
        ->join('core_member', 'acct_credits_account.member_id','=','core_member.member_id')
        ->join('core_member_working', 'acct_credits_account.member_id','=','core_member_working.member_id')
        ->join('core_province', 'core_member.province_id','=','core_province.province_id')
        ->join('core_city', 'core_member.city_id','=','core_city.city_id')
        ->join('core_kecamatan', 'core_member.kecamatan_id','=','core_kecamatan.kecamatan_id')
        ->where('acct_credits_account.data_state', 0)
        ->where('acct_credits_account.credits_account_id', $id)
        ->first();

        $total_credits_account 			= $credistaccount['credits_account_amount'];
        $credits_account_interest 		= $credistaccount['credits_account_interest'];
        $credits_account_period 		= $credistaccount['credits_account_period'];

        $installment_pattern			= array();
        $opening_balance				= $total_credits_account;

        return $installment_pattern;

    }
	/**
     * Set credits payment to
	 * @param mixed $credits_payment_to
	 * @return self
	 */
	public function paymentTo($credits_payment_to): self {
		$this->credits_payment_to = $credits_payment_to;
		return $this;
	}
    /**
     * Get payment period for date manipulation
     *
     * @param int $payment_period
     * @return Collection|string
     */
    public static function paymentPeriod($payment_period=null) {
         $period = collect([1=>'month', 2=>'week']);
         if(!is_null($payment_period)){
            return $period[$payment_period];
         }
         return $period;
    }
    protected function rate3($nprest, $vlrparc, $vp, $guess = 0.25) {
        $maxit      = 100;
        $precision  = 14;
        $guess      = round($guess,$precision);
        for ($i=0 ; $i<$maxit ; $i++) {
            $divdnd = $vlrparc - ( $vlrparc * (pow(1 + $guess , -$nprest)) ) - ($vp * $guess);
            $divisor = $nprest * $vlrparc * pow(1 + $guess , (-$nprest - 1)) - $vp;
            $newguess = $guess - ( $divdnd / $divisor );
            $newguess = round($newguess, $precision);
            if ($newguess == $guess) {
                return $newguess;
            } else {
                $guess = $newguess;
            }
        }
        return null;
    }
}
