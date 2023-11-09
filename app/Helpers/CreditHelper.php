<?php
namespace App\Helpers;

use App\Models\AcctCreditsAccount;
use Illuminate\Support\Collection;

class CreditHelper{
    protected $creditData;
    /**
     * Get Schedule
     * leave param empty if want input data manualy
     * @param [int] $credit_account_id
     * @return CreditHelper
     */
    public static function sedule($credit_account_id=null) {
        $ch = new CreditHelper();
        if(!is_null($credit_account_id)){
            $cd = AcctCreditsAccount::find($credit_account_id);
            $cd->setData($ch);
        }
        return $ch;
    }
    /**
     * Set Credit Account data
     *
     * @param Collection $data
     * @return void
     */
    public function setData(Collection $data) {
         $this->creditData = $data;
    }
}
