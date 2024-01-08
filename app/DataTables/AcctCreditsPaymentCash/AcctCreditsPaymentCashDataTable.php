<?php

namespace App\DataTables\AcctCreditsPaymentCash;

use App\Models\AcctCreditsPayment;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctCreditsPaymentCashDataTable extends DataTable
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
            ->addIndexColumn()
            ->editColumn('credits_payment_date', function (AcctCreditsPayment $model) {
                return date('d-m-Y', strtotime($model->credits_payment_date));
            })
            ->editColumn('credits_payment_principal', function (AcctCreditsPayment $model) {
                return number_format($model->credits_payment_principal, 2);
            })
            ->editColumn('credits_payment_interest', function (AcctCreditsPayment $model) {
                return number_format($model->credits_payment_interest, 2);
            })
            ->editColumn('credits_payment_fine', function (AcctCreditsPayment $model) {
                return number_format($model->credits_payment_fine, 2);
            })
            ->addColumn('action', 'content.AcctCreditsPaymentCash.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctCreditsPaymentCash/AcctCreditsPaymentCashDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctCreditsPayment $model)
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

        $querydata = $model->newQuery()
        ->join('core_member','acct_credits_payment.member_id', '=', 'core_member.member_id')			
        ->join('acct_credits','acct_credits_payment.credits_id', '=', 'acct_credits.credits_id')
        ->join('acct_credits_account','acct_credits_payment.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->where('acct_credits_payment.credits_payment_type', 0)
        ->where('acct_credits_payment.credits_branch_status', 0)
        ->where('acct_credits_payment.credits_payment_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_credits_payment.credits_payment_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('core_member.branch_id', $sessiondata['branch_id'])
        ->where('acct_credits_payment.pickup_state', 1);
        if($sessiondata['credits_id'] != null || $sessiondata['credits_id'] != ''){
            $querydata = $querydata->where('acct_credits_payment.credits_id', $sessiondata['credits_id']);
        }

        return $querydata;
    }
    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('credits-payment-cash-table')
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
            Column::make('acct_credits_payment.savings_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('acct_credits_account.credits_account_serial')->title(__('No Akad Pinjaman'))->data('credits_account_serial'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('acct_credits.credits_name')->title(__('Jenis Pinjaman'))->data('credits_name'),
            Column::make('acct_credits_payment.credits_payment_date')->title(__('Tanggal Angsuran'))->data('credits_payment_date'),
            Column::make('acct_credits_payment.credits_payment_principal')->title(__('Angsuran Pokok'))->data('credits_payment_principal'),
            Column::make('acct_credits_payment.credits_payment_interest')->title(__('Angsuran Bunga'))->data('credits_payment_interest'),
            Column::make('acct_credits_payment.credits_payment_fine')->title(__('Denda'))->data('credits_payment_fine'),
            Column::computed('action') 
                    ->title(__('Aksi'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(150)
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
        return 'Pelunasan_Pinjaman_' . date('YmdHis');
    }
}
