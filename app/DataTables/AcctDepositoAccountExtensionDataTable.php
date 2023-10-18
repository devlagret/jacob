<?php

namespace App\DataTables;

use App\Models\AcctDepositoAccount;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AcctDepositoAccountExtensionDataTable extends DataTable
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
            ->editColumn('deposito_account_due_date', function(AcctDepositoAccount $model){

                return date('d-m-Y', strtotime($model->deposito_account_due_date));

            })
            ->editColumn('deposito_account_date', function(AcctDepositoAccount $model){

                return date('d-m-Y', strtotime($model->deposito_account_date));

            })
            ->editColumn('deposito_account_amount', function(AcctDepositoAccount $model){

                return number_format($model->deposito_account_amount,2);

            })
            ->editColumn('deposito_account_interest_amount', function(AcctDepositoAccount $model){

                return number_format($model->deposito_account_interest_amount,2);

            })
            ->editColumn('deposito_account_extra_type', function(AcctDepositoAccount $model){

                if ($model->deposito_account_extra_type == 1) {
                    return 'ARO';
                } else {
                    return 'Manual';
                }

            })
            ->addIndexColumn()
            ->addColumn('action', 'content.AcctDepositoAccountExtension.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\DepositoAccountExtensionDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctDepositoAccount $model)
    {
        $sesion_filter =  session()->get('filter_depositoaccountextension');
        $model = $model->newQuery()
        ->select('acct_deposito_account.deposito_account_extra_type','acct_deposito_account.deposito_account_id', 'core_member.member_name', 'acct_deposito.deposito_name', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_serial_no', 'acct_deposito_account.deposito_account_interest_amount')
        ->join('core_member', 'acct_deposito_account.member_id','=','core_member.member_id')
        ->join('acct_deposito', 'acct_deposito_account.deposito_id','=','acct_deposito.deposito_id')
        ->where('acct_deposito_account.deposito_account_status', 0)
        ->where('acct_deposito_account.data_state', 0)
        ->orderBy('acct_deposito_account.deposito_account_no', 'ASC');
        if (!empty($sesion_filter['branch_id'])) {
            $model = $model->where('acct_deposito_account.branch_id', $sesion_filter['branch_id']);
        }
        if (!empty($sesion_filter['deposito_id'])) {
            $model = $model->where('acct_deposito_account.deposito_id', $sesion_filter['deposito_id']);
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('depositoaccountextensiondatatable-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    ->orderBy(0, 'asc')
                    ->responsive()
                    ->autoWidth(false)
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
            Column::make('deposito_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('acct_deposito.deposito_name')->title(__('Jenis Simpanan Berjangka'))->data('deposito_name'),
            Column::make('deposito_account_extra_type')->title(__('Jenis Perpanjangan')),
            Column::make('deposito_account_no')->title(__('Nomor SimpKa')),
            Column::make('deposito_account_serial_no')->title(__('Nomor Seri')),
            Column::make('deposito_account_date')->title(__('Tanggal Buka')),
            Column::make('deposito_account_due_date')->title(__('Tanggal Jatuh Tempo')),
            Column::make('deposito_account_amount')->title(__('Nominal')),
            Column::make('deposito_account_interest_amount')->title(__('Bagi Hasil')),
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
        return 'DepositoAccountExtension_' . date('YmdHis');
    }
}
