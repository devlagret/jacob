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
     * Get schedule flat
     *
     * @param integer $credits_account_amount
     * @param integer $credits_account_principal_amount
     * @param integer $credits_account_interest
     * @param integer $credits_account_period
     * @param integer $credits_account_date
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
    /**
     * Get schedule anuitas
     *
     * @param integer $total_credits_account
     * @param integer $credits_account_interest
     * @param integer $credits_account_period
     * @param integer $credits_account_date
     * @param integer $credits_payment_period
     * @return Collection
     */
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
    /**
     * Get schedule slidingrate
     *
     * @param integer $total_credits_account
     * @param integer $credits_account_interest
     * @param integer $credits_account_period
     * @param integer $credits_account_date
     * @param integer $credits_payment_period
     * @return Collection
     */
    public function slidingrate($total_credits_account=null,$credits_account_interest=null,$credits_account_period=null,$credits_account_date=null,$credits_payment_period=1){
        $credits_payment_to=1;
        if(!empty($this->creditData)){
            $total_credits_account 		= $this->total_credits_account;
            $credits_account_interest 	= $this->creditData->credits_account_interest;
            $credits_account_period 	= $this->creditData->credits_account_period;
            $credits_payment_period     = $this->creditData->credits_payment_period;
            $credits_account_date       = $this->credits_account_date;
        }
        if(!empty($this->credits_payment_to)){
            $credits_payment_to         = $this->credits_payment_to;
        }
        $data	= collect();
        $opening_balance				= $total_credits_account;
        $period = self::paymentPeriod($credits_payment_period);
        for($i=$credits_payment_to; $i<=$credits_account_period; $i++){
            $row	= collect();
            $tanggal_angsuran   = Carbon::parse($credits_account_date)->add(($i-$credits_payment_to),$period)->format('d-m-Y');
            $angsuran_pokok		= $total_credits_account/$credits_account_period;
            $angsuran_margin	= $opening_balance*$credits_account_interest/100;
            $angsuran 			= $angsuran_pokok + $angsuran_margin;
            $last_balance       = $opening_balance - $angsuran_pokok;
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
    public function menurunharian($credits_account_amount=null,$credits_account_principal_amount=null,$credits_account_interest=null,$credits_account_period=null,$credits_account_date=null,$credits_payment_period=1){
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
