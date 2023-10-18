<?php

namespace App\DataTables\AcctCreditsAccount;

use App\Models\AcctCreditsAccount;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctCreditsAccountDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('payment_type_id', function(AcctCreditsAccount $model){

                return Configuration::PaymentType()[$model->payment_type_id];

            })
            ->editColumn('credits_account_date', function(AcctCreditsAccount $model){

                return date('d-m-Y', strtotime($model->credits_account_date));

            })
            ->editColumn('credits_account_amount', function(AcctCreditsAccount $model){

                return number_format($model->credits_account_amount,2);

            })
            ->editColumn('credits_account_status', function(AcctCreditsAccount $model){

                return Configuration::CreditsAccountStatus()[$model->credits_account_status];

            })
            ->addIndexColumn()
            ->addColumn('approve', 'content.AcctCreditsAccount.List._approve-status')
            ->addColumn('action', 'content.AcctCreditsAccount.List._action-menu')
            ->rawColumns(['approve', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\CreditsAccount/CreditsAccountDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */ 
    public function query(AcctCreditsAccount $model)
    {
        $session = session()->get('filter_creditsaccount');
        if (empty($session['start_date'])) {
            $start_date = date('Y-m-d');
        } else {
            $start_date = date('Y-m-d', strtotime($session['start_date']));
        }
        if (empty($session['end_date'])) {
            $end_date = date('Y-m-d');
        } else {
            $end_date = date('Y-m-d', strtotime($session['end_date']));
        }

        $table = $model->newQuery()
        ->select('acct_credits_account.credits_account_serial','core_member.member_name','acct_credits.credits_name','acct_credits_account.payment_type_id','acct_source_fund.source_fund_name','acct_credits_account.credits_account_date','acct_credits_account.credits_account_amount','acct_credits_account.credits_account_status','acct_credits_account.credits_account_id','acct_credits_account.credits_approve_status')
        ->join('core_member','acct_credits_account.member_id','=','core_member.member_id')
        ->join('acct_credits','acct_credits_account.credits_id','=','acct_credits.credits_id')
        ->join('acct_source_fund','acct_credits_account.source_fund_id','=','acct_source_fund.source_fund_id')
        ->where('acct_credits_account.credits_account_date','>=', $start_date)
        ->where('acct_credits_account.credits_account_date','<=', $end_date)
        ->where('acct_credits_account.data_state', 0);
        if(!empty($session['credits_id'])){
            $table = $table->where('acct_credits_account.credits_id', $session['credits_id']);
        }
        if(!empty($session['branch_id'])){
            $table = $table->where('acct_credits_account.branch_id', $session['branch_id']);
        }

        return $table;
    }  

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('creditsaccount-creditsaccountdatatable-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    ->orderBy(0, 'asc')
                    ->responsive(false)
                    ->autoWidth(true)
                    ->parameters(['scrollX' => true])
                    ->addTableClass('align-middle table-row-dashed fs-6 gy-5');
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('credits_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('credits_account_serial')->title(__('No. Perjanjian Kredit')),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('acct_credits.credits_name')->title(__('Jenis Pinjaman'))->data('credits_name'),
            Column::make('payment_type_id')->title(__('Jenis Angsuran')),
            Column::make('acct_source_fund.source_fund_name')->title(__('Jenis Sumber Dana'))->data('source_fund_name'),
            Column::make('credits_account_date')->title(__('Tanggal Pinjaman')),
            Column::make('credits_account_amount')->title(__('Jumlah Pinjaman')),
            Column::make('credits_account_status')->title(__('Status Pinjaman')),
            Column::computed('approve') 
                    ->title(__('Tindak Lanjut'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(150)
                    ->addClass('text-center'),
            Column::computed('action') 
                    ->title(__('Aksi'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(500)
                    ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'AcctCreditsAccount/AcctCreditsAccount_' . date('YmdHis');
    }
}
