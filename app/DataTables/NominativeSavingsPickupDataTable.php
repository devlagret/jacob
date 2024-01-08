<?php

namespace App\DataTables;

use App\Models\AcctCreditsPayment;
use App\Models\AcctSavings;
use App\Models\AcctSavingsCashMutation;
use App\Models\CoreMember;
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

//------Angsuran
        $querydata1 = AcctCreditsPayment::selectRaw(
        '1 As type,
        credits_payment_id As id,
        credits_payment_date As tanggal,
        username As operator,
        member_name As anggota,
        credits_account_serial As no_transaksi,
        credits_payment_amount As jumlah,
        CONCAT("Angsuran ",credits_name) As keterangan,
        acct_credits_payment.pickup_state AS pickup_state')

        ->join('core_member','acct_credits_payment.member_id', '=', 'core_member.member_id')			
        ->join('acct_credits','acct_credits_payment.credits_id', '=', 'acct_credits.credits_id')
        ->join('system_user','system_user.user_id', '=', 'acct_credits_payment.created_id')
        ->join('acct_credits_account','acct_credits_payment.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->where('acct_credits_payment.credits_payment_type', 0)
        ->where('acct_credits_payment.credits_branch_status', 0)
        // ->where('acct_credits_payment.credits_payment_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        // ->where('acct_credits_payment.credits_payment_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('acct_credits_payment.branch_id', $sessiondata['branch_id'])
        ->where('acct_credits_payment.pickup_state', 0);
        ;


//------Setor Tunai Simpanan Biasa
        $querydata2 = AcctSavingsCashMutation::selectRaw(
            '2 As type,
            savings_cash_mutation_id As id,
            savings_cash_mutation_date As tanggal,
            username As operator,
            member_name As anggota,
            savings_account_no As no_transaksi,
            savings_cash_mutation_amount As jumlah,
            CONCAT("Setoran Tunai ",savings_name) As keterangan,
            acct_savings_cash_mutation.pickup_state AS pickup_state')

        ->withoutGlobalScopes()
        ->join('system_user','system_user.user_id', '=', 'acct_savings_cash_mutation.created_id')
        ->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id', '=', 'acct_mutation.mutation_id')
        ->join('acct_savings_account', 'acct_savings_cash_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('core_member', 'acct_savings_cash_mutation.member_id', '=', 'core_member.member_id')
        ->join('acct_savings', 'acct_savings_cash_mutation.savings_id', '=', 'acct_savings.savings_id')
        ->where('acct_savings_cash_mutation.mutation_id', 1)
        // ->where('acct_savings_cash_mutation.savings_cash_mutation_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        // ->where('acct_savings_cash_mutation.savings_cash_mutation_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('core_member.branch_id', auth()->user()->branch_id)
        ->where('acct_savings_cash_mutation.pickup_state', 0);


//------Tarik Tunai Simpanan Biasa
        $querydata3 = AcctSavingsCashMutation::selectRaw(
            '3 As type,
            savings_cash_mutation_id As id,
            savings_cash_mutation_date As tanggal,
            username As operator,
            member_name As anggota,
            savings_account_no As no_transaksi,
            savings_cash_mutation_amount As jumlah,
            CONCAT("Tarik Tunai ",savings_name) As keterangan,
            acct_savings_cash_mutation.pickup_state AS pickup_state')
        ->withoutGlobalScopes()
        ->join('system_user','system_user.user_id', '=', 'acct_savings_cash_mutation.created_id')
        ->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id', '=', 'acct_mutation.mutation_id')
        ->join('acct_savings_account', 'acct_savings_cash_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('core_member', 'acct_savings_cash_mutation.member_id', '=', 'core_member.member_id')
        ->join('acct_savings', 'acct_savings_cash_mutation.savings_id', '=', 'acct_savings.savings_id')
        ->where('acct_savings_cash_mutation.mutation_id', 2)
        // ->where('acct_savings_cash_mutation.savings_cash_mutation_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        // ->where('acct_savings_cash_mutation.savings_cash_mutation_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('core_member.branch_id', auth()->user()->branch_id)
        ->where('acct_savings_cash_mutation.pickup_state', 0);


//------Setor Tunai Simpanan Wajib
        $querydata4 = CoreMember::selectRaw(
            '4 As type,
            member_id As id,
            core_member.updated_at As tanggal,
            username As operator,
            member_name As anggota,
            member_no As no_transaksi,
            member_mandatory_savings As jumlah,
            CONCAT("Setor Tunai Simpanan Wajib ") As keterangan,
            core_member.pickup_state AS pickup_state')
        ->withoutGlobalScopes()
        ->join('system_user','system_user.user_id', '=', 'core_member.created_id')
        // ->where('acct_savings_cash_mutation.savings_cash_mutation_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        // ->where('acct_savings_cash_mutation.savings_cash_mutation_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('core_member.branch_id', auth()->user()->branch_id)
        ->where('core_member.pickup_state', 0);




//------Combine the queries using UNION
        $querydata = $querydata1->union($querydata2)->union($querydata3)->union($querydata4);
        // Add ORDER BY clause to sort by the "keterangan" column
        $querydata = $querydata->orderBy('tanggal','DESC');
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
            Column::make('id')->title(__('ID')),
            Column::make('tanggal')
                    ->addClass('text-right')
                    ->width(200)
                    ->title(__('Tanggal')),
            Column::make('operator')->title(__('Nama Operator')),
            Column::make('anggota')
                    ->addClass('text-right')
                    ->width(200)
                    ->title(__('Nama Anggota')),
            Column::make('no_transaksi')->title(__('No Transaksi')),
            Column::make('jumlah')
                    ->addClass('text-right')
                    ->width(200)
                    ->title(__('Jumlah')),
            Column::make('keterangan')
                    ->width(200)
                    ->title(__('Jenis')),
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
