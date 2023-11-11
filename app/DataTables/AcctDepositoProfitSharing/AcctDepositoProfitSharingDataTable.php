<?php

namespace App\DataTables\AcctDepositoProfitSharing;

use App\Models\AcctDepositoProfitSharing;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctDepositoProfitSharingDataTable extends DataTable
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
            ->editColumn('deposito_account_date', function (AcctDepositoProfitSharing $model) {
                return date('d-m-Y', strtotime($model->deposito_account_date));
            })
            ->editColumn('deposito_account_due_date', function (AcctDepositoProfitSharing $model) {
                return date('d-m-Y', strtotime($model->deposito_account_due_date));
            })
            ->editColumn('deposito_profit_sharing_due_date', function (AcctDepositoProfitSharing $model) {
                return date('d-m-Y', strtotime($model->deposito_profit_sharing_due_date));
            })
            ->editColumn('deposito_account_last_balance', function (AcctDepositoProfitSharing $model) {
                return number_format($model->deposito_account_last_balance, 2);
            })
            ->editColumn('deposito_profit_sharing_amount', function (AcctDepositoProfitSharing $model) {
                return number_format($model->deposito_profit_sharing_amount, 2);
            })
            ->addColumn('action', 'content.AcctDepositoProfitSharing.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctDepositoProfitSharing/AcctDepositoProfitSharingDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctDepositoProfitSharing $model)
    {
        $sessiondata = session()->get('filter_depositoprofitsharing');
        if(!$sessiondata){
            $sessiondata = array(
                'start_date'    => date('Y-m-d'),
                'end_date'      => date('Y-m-d'),
                'branch_id'     => auth()->user()->branch_id,
            );
        }

        $querydata = $model->newQuery()
        ->withoutGlobalScopes()
        ->select('acct_deposito_profit_sharing.deposito_profit_sharing_id', 'acct_deposito_profit_sharing.deposito_account_id', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_serial_no', 'acct_deposito_profit_sharing.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_deposito_profit_sharing.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_profit_sharing.deposito_profit_sharing_amount', 'acct_deposito_profit_sharing.deposito_account_last_balance', 'acct_deposito_profit_sharing.deposito_profit_sharing_date', 'acct_deposito_profit_sharing.deposito_profit_sharing_due_date', 'acct_deposito_profit_sharing.deposito_profit_sharing_status', 'acct_deposito_account.deposito_account_status')
        ->join('acct_deposito_account', 'acct_deposito_profit_sharing.deposito_account_id', '=', 'acct_deposito_account.deposito_account_id')
        ->join('acct_savings_account', 'acct_deposito_profit_sharing.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('core_member', 'acct_deposito_profit_sharing.member_id', '=', 'core_member.member_id')
        ->where('acct_deposito_profit_sharing.deposito_profit_sharing_due_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_deposito_profit_sharing.deposito_profit_sharing_due_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('acct_deposito_profit_sharing.branch_id', $sessiondata['branch_id']);

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
                    ->setTableId('deposito-profit-sharing-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    ->orderBy(0, 'asc')
                    ->responsive()
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
            Column::make('acct_deposito_account.deposito_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('acct_deposito_account.deposito_account_no')->title(__('No Rek Simp Berjangka'))->data('deposito_account_no'),
            Column::make('acct_deposito_account.deposito_account_serial_no')->title(__('No Seri'))->data('deposito_account_serial_no'),
            Column::make('acct_savings_account.savings_account_no')->title(__('No Rek Tabungan'))->data('savings_account_no'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('core_member.member_address')->title(__('Alamat'))->data('member_address'),
            Column::make('acct_deposito_account.deposito_account_date')->title(__('Tanggal Buka'))->data('deposito_account_date'),
            Column::make('acct_deposito_account.deposito_account_due_date')->title(__('Jatuh Tempo'))->data('deposito_account_due_date'),
            Column::make('acct_deposito_profit_sharing.deposito_profit_sharing_due_date')->title(__('Tanggal Bunga'))->data('deposito_profit_sharing_due_date'),
            Column::make('acct_deposito_profit_sharing.deposito_account_last_balance')->title(__('Saldo'))->data('deposito_account_last_balance'),
            Column::make('acct_deposito_profit_sharing.deposito_profit_sharing_amount')->title(__('Bunga'))->data('deposito_profit_sharing_amount'),
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
        return 'Bunga_Simpanan_Berjangka_' . date('YmdHis');
    }
}
