<?php

namespace App\DataTables;

use App\Models\AcctBankAccount;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;

class AcctBankAccountDataTable extends DataTable
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
            ->editColumn('account_status', function (AcctBankAccount $model) {
                $data = Configuration::AccountStatus();

                return $data[$model->account_status];
            })
            ->addIndexColumn()
            ->addColumn('action', 'content.AcctBankAccount.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctBankAccountDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctBankAccount $model)
    {
        return $model->newQuery()
        ->join('acct_account','acct_account.account_id','=','acct_bank_account.account_id')
        ->select(DB::raw("CONCAT(acct_account.account_code,' - ',acct_account.account_name) AS full_account"), 'acct_bank_account.bank_account_id', 'acct_bank_account.bank_account_code', 'acct_bank_account.bank_account_name', 'acct_bank_account.bank_account_no','acct_account.account_name','acct_account.account_code','acct_account.account_status')
        ->where('acct_bank_account.data_state', 0);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('bank-account-table')
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
            Column::make('bank_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('bank_account_code')->title(__('Kode Bank')),
            Column::make('bank_account_name')->title(__('Nama Bank')),
            Column::make('bank_account_no')->title(__('No. Rekening')),
            Column::make('acct_account.account_name','acct_account.account_code')->title(__('No. Perkiraan'))->data('full_account'),
            Column::make('acct_account.account_status')->title(__('D/K'))->data('account_status'),
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
        return 'AcctBankAccount_' . date('YmdHis');
    }
}
