<?php
namespace App\Helpers;
use App\Models\AcctMutation;
use App\Models\PreferenceInventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\AcctJournalVoucher as JournalVoucher;
use App\Models\AcctJournalVoucherItem as JournalVoucherItem;
use App\Models\AcctSavingsAccount;

class JournalItemHelperSetoranTabugan extends JournalSavingAccHelper{
    public function firstDeposit(int $amount) {
        if($amount > 0){

        }
        return $this;
    }
    public function admin($amount) {
        if($amount > 0){
            $preferenceinventory = PreferenceInventory::first();

            $data_credit =array(
                'journal_voucher_id'			=> parent::$journal_voucher_id,
                'account_id'					=> $preferenceinventory['inventory_adm_id'],
                'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                'journal_voucher_amount'		=> $preferencecompany['savings_account_administration'],
                'journal_voucher_credit_amount'	=> $preferencecompany['savings_account_administration'],
                'account_id_status'				=> 1,
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucherItem::create($data_credit);
        }
        return $this;
    }
    public function savingJournalItem($account_id=null) {
         // content
        return $this;
    }

}