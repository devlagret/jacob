<?php

namespace App\DataTables\AcctDepositoAccount;

use App\Models\AcctDepositoAccount;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AcctDepositoAccountDataTable extends DataTable
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
            ->editColumn('deposito_account_date', function (AcctDepositoAccount $model) {
                return date('d-m-Y', strtotime($model->deposito_account_date));
            })
            ->editColumn('deposito_account_due_date', function (AcctDepositoAccount $model) {
                return date('d-m-Y', strtotime($model->deposito_account_due_date));
            })
            ->editColumn('deposito_account_extra_type', function (AcctDepositoAccount $model) {
                if($model->deposito_account_extra_type == 1){
                    $type_extra = "ARO";
                }else{
                    $type_extra = "Manual";
                }

                return $type_extra;
            })
            ->editColumn('deposito_account_amount', function (AcctDepositoAccount $model) {
                return number_format($model->deposito_account_amount, 2);
            })
            ->editColumn('deposito_account_interest_amount', function (AcctDepositoAccount $model) {
                return number_format($model->deposito_account_interest_amount, 2);
            })
            ->addColumn('action', 'content.AcctDepositoAccount.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctDepositoAccount/AcctDepositoAccountDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctDepositoAccount $model)
    {
        $sessiondata = session()->get('filter_depositoaccount');
        if(!$sessiondata){
            $sessiondata = array(
                'deposito_id' => null,
                'branch_id' => auth()->user()->branch_id,
            );
        }
        if(!$sessiondata['branch_id'] || !$sessiondata['branch_id']==0){
            $sessiondata['branch_id'] = auth()->user()->branch_id;
        }

        $querydata = $model->newQuery()
        ->withoutGlobalScopes()
        ->select('acct_deposito_account.deposito_account_id', 'core_member.member_name', 'acct_deposito.deposito_name', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_extra_type', 'acct_deposito_account.deposito_account_serial_no', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_interest_amount', 'acct_deposito_account.validation')
        ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
        ->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
        ->where('acct_deposito_account.deposito_account_status', 0)
        ->where('acct_deposito_account.data_state', 0)
        ->where('core_member.branch_id', $sessiondata['branch_id']);
        if($sessiondata['deposito_id']){
            $querydata = $querydata->where('acct_deposito_account.deposito_id', $sessiondata['deposito_id']);
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
                    ->setTableId('deposito-account-table')
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
            Column::make('acct_deposito_account.deposito_account_no')->title(__('No Simpanan Berjangka'))->data('deposito_account_no'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('acct_deposito.deposito_name')->title(__('Jenis Simpanan Berjangka'))->data('deposito_name'),
            Column::make('acct_deposito_account.deposito_account_serial_no')->title(__('Nomor Seri'))->data('deposito_account_serial_no'),
            Column::make('acct_deposito_account.deposito_account_date')->title(__('Tanggal Buka'))->data('deposito_account_date'),
            Column::make('acct_deposito_account.deposito_account_due_date')->title(__('Tanggal Jatuh Tempo'))->data('deposito_account_due_date'),
            Column::make('acct_deposito_account.deposito_account_amount')->title(__('Nominal'))->data('deposito_account_amount'),
            Column::make('acct_deposito_account.deposito_account_interest_amount')->title(__('Bunga Diterima'))->data('deposito_account_interest_amount'),
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
        return 'Simpanan_Berjangka_' . date('YmdHis');
    }
}
