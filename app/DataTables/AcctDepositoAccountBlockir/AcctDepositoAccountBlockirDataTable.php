<?php

namespace App\DataTables\AcctDepositoAccountBlockir;

use App\Models\AcctDepositoAccountBlockir;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctDepositoAccountBlockirDataTable extends DataTable
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
            ->editColumn('deposito_account_blockir_type', function (AcctDepositoAccountBlockir $model) {
                $blockirtype = Configuration::BlockirType();

                return $blockirtype[$model->deposito_account_blockir_type];
            })
            ->editColumn('deposito_account_blockir_status', function (AcctDepositoAccountBlockir $model) {
                $blockirstatus = Configuration::BlockirStatus();

                return $blockirstatus[$model->deposito_account_blockir_status];
            })
            ->editColumn('deposito_account_blockir_date', function (AcctDepositoAccountBlockir $model) {

                return date('d-m-Y', strtotime($model->deposito_account_blockir_date));
            })
            ->editColumn('deposito_account_unblockir_date', function (AcctDepositoAccountBlockir $model) {

                if ($model->deposito_account_unblockir_date == null) {
                    return '';
                } else {
                    return date('d-m-Y', strtotime($model->deposito_account_unblockir_date));
                }
            })
            ->editColumn('deposito_account_blockir_amount', function (AcctDepositoAccountBlockir $model) {

                return number_format($model->deposito_account_blockir_amount, 2);
            })
            ->addIndexColumn()
            ->addColumn('action', 'content.AcctDepositoAccountBlockir.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctDepositoAccountBlockir/AcctDepositoAccountBlockirDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctDepositoAccountBlockir $model)
    {
        return $model->newQuery()
        ->withoutGlobalScopes()
        ->select('acct_deposito_account_blockir.deposito_account_blockir_id','acct_deposito_account_blockir.deposito_account_blockir_type','acct_deposito_account_blockir.deposito_account_blockir_status','acct_deposito_account_blockir.deposito_account_blockir_date','acct_deposito_account_blockir.deposito_account_unblockir_date','acct_deposito_account_blockir.deposito_account_blockir_amount','acct_deposito_account.deposito_account_no','core_member.member_name','core_member.member_address')
        ->join('core_member','core_member.member_id','=','acct_deposito_account_blockir.member_id')
        ->join('acct_deposito_account','acct_deposito_account.deposito_account_id','=','acct_deposito_account_blockir.deposito_account_id')
        ->where('acct_deposito_account_blockir.data_state', 0);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('deposito-account-blockir-table')
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
            Column::make('deposito_account_blockir_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('acct_deposito_account.deposito_account_no')->title(__('No. Rekening'))->data('deposito_account_no'),
            Column::make('core_member.member_name')->title(__('Nama'))->data('member_name'),
            Column::make('core_member.member_address')->title(__('Alamat'))->data('member_address'),
            Column::make('deposito_account_blockir_type')->title(__('Sifat Blockir')),
            Column::make('deposito_account_blockir_status')->title(__('Status')),
            Column::make('deposito_account_blockir_date')->title(__('Tanggal Blockir')),
            Column::make('deposito_account_unblockir_date')->title(__('Tanggal UnBlockir')),
            Column::make('deposito_account_blockir_amount')->title(__('Saldo Diblokir')),
            Column::computed('action') 
                    ->title(__('Aksi'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(100)
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
        return 'AcctDepositoAccountBlockir/AcctDepositoAccountBlockir_' . date('YmdHis');
    }
}
