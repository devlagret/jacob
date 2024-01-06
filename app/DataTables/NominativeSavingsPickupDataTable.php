<?php

namespace App\DataTables;

use App\Models\AcctCreditsPayment;
use App\Models\AcctSavings;
use App\Models\AcctSavingsCashMutation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\DB;

class NominativeSavingsPickupDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            // ->editColumn('savings_profit_sharing', function (AcctSavings $model) {
            //     $savingsprofitsharing = Configuration::SavingsProfitSharing();

            //     return $savingsprofitsharing[$model->savings_profit_sharing];
            // })
            ->addColumn('action', 'content.NominativeSavings.Pickup.List._action-menu');
    }

    // public function query(AcctSavingsCashMutation $model)
    // {
    //     $sessiondata = Session::get('pickup-data');
    //     // return $model->newQuery()->with('member','mutation')
    //     // ->where('data_state', 0)
    //     // ->where('savings_cash_mutation_status', 1)
    //     // ->where('savings_cash_mutation_date','>=',Carbon::parse($sessiondata['start_date']??Carbon::now())->format('Y-m-d'))
    //     // ->where('savings_cash_mutation_date','<=',Carbon::parse($sessiondata['end_date']??Carbon::now())->format('Y-m-d'))
    //     // ;
    // }

    public function query()
    {
        $sessiondata = session()->get('filter_creditspaymentcash');
        if(!$sessiondata){
            $sessiondata = array(
                'start_date'    => date('Y-m-d'),
                'end_date'      => date('Y-m-d'),
                'credits_id'    => null,
                'branch_id'     => auth()->user()->branch_id,
            );
        }
        if(!$sessiondata['branch_id'] || !$sessiondata['branch_id']==0){
            $sessiondata['branch_id'] = auth()->user()->branch_id;
        }

        //Angsuran
        $querydata1 = AcctCreditsPayment::select(
        'credits_payment_id As id',
        'credits_payment_date As tanggal',
        'username As operator',
        'member_name As anggota',
        'credits_account_serial As no_transaksi',
        'credits_payment_amount As jumlah',
        'credits_name As keterangan')

        ->join('core_member','acct_credits_payment.member_id', '=', 'core_member.member_id')			
        ->join('acct_credits','acct_credits_payment.credits_id', '=', 'acct_credits.credits_id')
        ->join('system_user','system_user.user_id', '=', 'acct_credits_payment.created_id')
        ->join('acct_credits_account','acct_credits_payment.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->where('acct_credits_payment.credits_payment_type', 0)
        ->where('acct_credits_payment.credits_branch_status', 0)
        // ->where('acct_credits_payment.credits_payment_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        // ->where('acct_credits_payment.credits_payment_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('core_member.branch_id', $sessiondata['branch_id']);

        //Setor Tunai Tabungan
        $querydata2 = AcctSavingsCashMutation::select(
            'savings_cash_mutation_id As id',
            'savings_cash_mutation_date As tanggal',
            'username As operator',
            'member_name As anggota',
            'savings_account_no As no_transaksi',
            'savings_cash_mutation_amount As jumlah',
            'savings_name As keterangan'
        )
        ->withoutGlobalScopes()
        ->join('system_user','system_user.user_id', '=', 'acct_savings_cash_mutation.created_id')
        ->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id', '=', 'acct_mutation.mutation_id')
        ->join('acct_savings_account', 'acct_savings_cash_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('core_member', 'acct_savings_cash_mutation.member_id', '=', 'core_member.member_id')
        ->join('acct_savings', 'acct_savings_cash_mutation.savings_id', '=', 'acct_savings.savings_id')
        // ->where('acct_savings_cash_mutation.savings_cash_mutation_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        // ->where('acct_savings_cash_mutation.savings_cash_mutation_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('core_member.branch_id', auth()->user()->branch_id);

        // Combine the queries using UNION
        $querydata = $querydata1->union($querydata2);
        return $querydata;
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('pickup-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    ->orderBy(0, 'asc')
                    ->responsive()
                    ->autoWidth(true)
                    ->parameters(['scrollX' => true])
                    ->addTableClass('align-middle table-row-dashed fs-6 gy-5');
    }

    protected function getColumns()
    {
        return [
            Column::make('id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('tanggal')->title(__('Tanggal')),
            Column::make('operator')->title(__('Nama Operator')),
            Column::make('anggota')->title(__('Nama Anggota')),
            Column::make('no_transaksi')->title(__('No Transaksi')),
            Column::make('jumlah')->title(__('Jumlah')),
            Column::make('keterangan')->title(__('Keterangan')),
            Column::computed('action')
                    ->title(__('Aksi'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(300)
                    ->addClass('text-center'),
        ];
    }

    protected function filename()
    {
        return 'Mutation_' . date('YmdHis');
    }
}
