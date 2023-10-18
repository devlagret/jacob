<?php

namespace App\DataTables\AcctSavingsAccountBlockir;

use App\Models\AcctSavingsAccountBlockir;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctSavingsAccountBlockirDataTable extends DataTable
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
            ->editColumn('savings_account_blockir_type', function (AcctSavingsAccountBlockir $model) {
                $blockirtype = Configuration::BlockirType();

                return $blockirtype[$model->savings_account_blockir_type];
            })
            ->editColumn('savings_account_blockir_status', function (AcctSavingsAccountBlockir $model) {
                $blockirstatus = Configuration::BlockirStatus();

                return $blockirstatus[$model->savings_account_blockir_status];
            })
            ->editColumn('savings_account_blockir_date', function (AcctSavingsAccountBlockir $model) {

                if ($model->savings_account_blockir_date == null) {
                    return '';
                } else {
                    return date('d-m-Y', strtotime($model->savings_account_blockir_date));
                }
            })
            ->editColumn('savings_account_unblockir_date', function (AcctSavingsAccountBlockir $model) {

                if ($model->savings_account_unblockir_date == null) {
                    return '';
                } else {
                    return date('d-m-Y', strtotime($model->savings_account_unblockir_date));
                }
            })
            ->editColumn('savings_account_blockir_amount', function (AcctSavingsAccountBlockir $model) {

                return number_format($model->savings_account_blockir_amount, 2);
            })
            ->addIndexColumn()
            ->addColumn('action', 'content.AcctSavingsAccountBlockir.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctSavingsAccountBlockirDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctSavingsAccountBlockir $model)
    {
        return $model->newQuery()
        ->select('acct_savings_account_blockir.savings_account_blockir_id','acct_savings_account_blockir.savings_account_blockir_type','acct_savings_account_blockir.savings_account_blockir_status','acct_savings_account_blockir.savings_account_blockir_date','acct_savings_account_blockir.savings_account_unblockir_date','acct_savings_account_blockir.savings_account_blockir_amount','acct_savings_account.savings_account_no','core_member.member_name','core_member.member_address')
        ->join('core_member','core_member.member_id','=','acct_savings_account_blockir.member_id')
        ->join('acct_savings_account','acct_savings_account.savings_account_id','=','acct_savings_account_blockir.savings_account_id')
        ->where('acct_savings_account_blockir.data_state', 0);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('savings-account-blockir-table')
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
            Column::make('savings_account_blockir_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('acct_savings_account.savings_account_no')->title(__('No. Rekening'))->data('savings_account_no'),
            Column::make('core_member.member_name')->title(__('Nama'))->data('member_name'),
            Column::make('core_member.member_address')->title(__('Alamat'))->data('member_address'),
            Column::make('savings_account_blockir_type')->title(__('Sifat Blockir')),
            Column::make('savings_account_blockir_status')->title(__('Status')),
            Column::make('savings_account_blockir_date')->title(__('Tanggal Blockir')),
            Column::make('savings_account_unblockir_date')->title(__('Tanggal UnBlockir')),
            Column::make('savings_account_blockir_amount')->title(__('Saldo Diblokir')),
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
        return 'AcctSavingsAccountBlockir_' . date('YmdHis');
    }
}
