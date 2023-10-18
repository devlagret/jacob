<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcctAccountOpeningBalance;
use App\Models\AcctBalanceSheetReport;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsAccountDetail;
use App\Models\AcctSavingsCloseBook;
use App\Models\SHULastYear;
use Illuminate\Support\Facades\DB;

class AcctSavingsCloseBookController extends Controller
{
    public function index()
    {
        return view('content.AcctSavingsCloseBook.index');
    }

    public function processAdd(Request $request)
    {
        $fields = $request->validate([
            'start_date' => ['required','date_format:d-m-Y']
        ]);

        $data_log = array(
            'savings_close_book_date'		=> date('Y-m-d', strtotime($fields['start_date'])),
            'savings_close_book_period'		=> date('mY', strtotime($fields['start_date'])),
            'branch_id'						=> auth()->user()->branch_id,
            'created_id'					=> auth()->user()->user_id,
        );

        DB::beginTransaction();

        try {
            AcctSavingsCloseBook::create($data_log);
            $acctsavingsaccount = AcctSavingsAccount::where('branch_id', auth()->user()->branch_id)
            ->orderBy('savings_account_no', 'ASC')
            ->get();

            foreach ($acctsavingsaccount as $key => $val) {
                $acctsavingsaccount_update                                  = AcctSavingsAccount::findOrFail($val['savings_account_id']);
                $acctsavingsaccount_update->savings_account_opening_balance = $val['savings_account_last_balance'];
                $acctsavingsaccount_update->updated_id                      = auth()->user()->user_id;
                $acctsavingsaccount_update->save();
            }

            foreach ($acctsavingsaccount as $key => $val) {
                $data_detail = array (
                    'branch_id'						=> auth()->user()->branch_id,
                    'member_id'						=> $val['member_id'],
                    'savings_id'					=> $val['savings_id'],
                    'savings_account_id'			=> $val['savings_account_id'],
                    'transaction_code'				=> 'Tutup Buku/Saldo Awal',
                    'today_transaction_date'		=> date('Y-m-d', strtotime($fields['start_date'])),
                    'yesterday_transaction_date'	=> date('Y-m-d', strtotime($fields['start_date'])),
                    'opening_balance'				=> $val['savings_account_last_balance'],
                    'last_balance'					=> $val['savings_account_last_balance'],
                    'operated_name'					=> 'SYSTEM',
                    'created_id'					=> auth()->user()->user_id,
                );

                AcctSavingsAccountDetail::create($data_detail);
            }

            $shu_last_year_amount = 0;
			$acctbalancesheetreport_right	= AcctBalanceSheetReport::select('balance_sheet_report_id', 'report_no', 'account_id2', 'account_code2', 'account_name2', 'report_formula2', 'report_operator2', 'report_type2', 'report_tab2', 'report_bold2', 'report_formula3', 'report_operator3')
			->where('acct_balance_sheet_report.account_name2','!=', ' ')
			->orderBy('acct_balance_sheet_report.report_no', 'ASC')
            ->get();

            foreach($acctbalancesheetreport_right as $key => $val){
                $year  = date('Y');
                $month = date('m')+1;
                if($month > 12){
                    $month = 1;
                    $year += 1;
                }

                if($val['report_type2']	== 10){
                    $last_balance210 	= AcctAccountOpeningBalance::select('opening_balance')
                    ->where('account_id', $val['account_id2'])
                    ->where('branch_id', auth()->user()->branch_id)
                    ->where('month_period', $month)
                    ->where('year_period', $year)
                    ->first()
                    ->opening_balance;

                    $account_amount_top[$val['report_no']] = $last_balance210;
                }
                
                if($val['report_type2'] == 11){
                    if(!empty($val['report_formula2']) && !empty($val['report_operator2'])){
                        $report_formula2 	= explode('#', $val['report_formula2']);
                        $report_operator2 	= explode('#', $val['report_operator2']);

                        $account_amount	= 0;
                        for($i = 0; $i < count($report_formula2); $i++){
                            if($report_operator2[$i] == '-'){
                                if($account_amount == 0 ){
                                    $account_amount = $account_amount + $account_amount_top[$report_formula2[$i]];
                                } else {
                                    $account_amount = $account_amount - $account_amount_top[$report_formula2[$i]];
                                }
                            } else if($report_operator2[$i] == '+'){
                                if($account_amount == 0){
                                    $account_amount = $account_amount - $account_amount_top[$report_formula2[$i]];
                                } else {
                                    $account_amount = $account_amount + $account_amount_top[$report_formula2[$i]];
                                }
                            }
                        }

                        $shu_last_year_amount = $account_amount;
                    }
                }
            }

            $data_shu_last_year = array(
                'branch_id'             => auth()->user()->branch_id,
                'last_year'             => date('Y'),
                'next_year'             => date('Y')+1,
                'shu_last_year_amount'  => $shu_last_year_amount,
            );
            
            SHULastYear::create($data_shu_last_year);

            DB::commit();
            $message = array(
                'pesan' => 'Tutup Buku berhasil',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Tutup Buku gagal',
                'alert' => 'error'
            );
        }

        return redirect('savings-close-book')->with($message);
    }
}
