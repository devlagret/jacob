<?php

namespace App\DataTables\AcctCreditsAcquittance;

use App\Models\AcctCreditsAcquittance;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctCreditsAcquittanceDataTable extends DataTable
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
            ->editColumn('credits_acquittance_date', function (AcctCreditsAcquittance $model) {
                return date('d-m-Y', strtotime($model->credits_acquittance_date));
            })
            ->editColumn('credits_acquittance_principal', function (AcctCreditsAcquittance $model) {
                return number_format($model->credits_acquittance_principal, 2);
            })
            ->editColumn('credits_acquittance_interest', function (AcctCreditsAcquittance $model) {
                return number_format($model->credits_acquittance_interest, 2);
            })
            ->editColumn('credits_acquittance_fine', function (AcctCreditsAcquittance $model) {
                return number_format($model->credits_acquittance_fine, 2);
            })
            ->editColumn('credits_acquittance_penalty', function (AcctCreditsAcquittance $model) {
                return number_format($model->credits_acquittance_penalty, 2);
            })
            ->editColumn('credits_acquittance_amount', function (AcctCreditsAcquittance $model) {
                return number_format($model->credits_acquittance_amount, 2);
            })
            ->addColumn('action', 'content.AcctCreditsAcquittance.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctCreditsAcquittance/AcctCreditsAcquittanceDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctCreditsAcquittance $model)
    {
        $sessiondata = session()->get('filter_creditsacquittance');
        if(!$sessiondata){
            $sessiondata = array(
                'start_date'    => date('Y-m-d'),
                'end_date'      => date('Y-m-d'),
                'credits_id'    => null,
            );
        }

        $querydata = $model->newQuery()
        ->join('core_member','acct_credits_acquittance.member_id', '=', 'core_member.member_id')
        ->join('acct_credits','acct_credits_acquittance.credits_id', '=', 'acct_credits.credits_id')
        ->join('acct_credits_account','acct_credits_acquittance.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->where('acct_credits_acquittance.credits_acquittance_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_credits_acquittance.credits_acquittance_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])));
        if($sessiondata['credits_id'] != null || $sessiondata['credits_id'] != ''){
            $querydata = $querydata->where('acct_credits_acquittance.credits_id', $sessiondata['credits_id']);
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
                    ->setTableId('savings-cash-mutation-table')
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
            Column::make('acct_credits_account.savings_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('acct_credits_account.credits_account_serial')->title(__('No Akad Pinjaman'))->data('credits_account_serial'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('acct_credits.credits_name')->title(__('Jenis Pinjaman'))->data('credits_name'),
            Column::make('acct_credits_account.credits_acquittance_date')->title(__('Tanggal Pelunasan'))->data('credits_acquittance_date'),
            Column::make('acct_mutation.credits_acquittance_principal')->title(__('Pelunasan Sisa Pokok'))->data('credits_acquittance_principal'),
            Column::make('acct_credits_account.credits_acquittance_interest')->title(__('Pelunasan Sisa Bunga'))->data('credits_acquittance_interest'),
            Column::make('acct_credits_account.credits_acquittance_fine')->title(__('Pelunasan Akm. Sanksi'))->data('credits_acquittance_fine'),
            Column::make('acct_credits_account.credits_acquittance_penalty')->title(__('Jumlah Penalti'))->data('credits_acquittance_penalty'),
            Column::make('acct_credits_account.credits_acquittance_amount')->title(__('Total Pelunasan'))->data('credits_acquittance_amount'),
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
