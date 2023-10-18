<?php

namespace App\DataTables\AcctSavingsAccount;

use App\Models\AcctSavingsAccount;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AcctSavingsAccountDataTable extends DataTable
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
            ->editColumn('savings_account_date', function (AcctSavingsAccount $model) {
                return date('d-m-Y', strtotime($model->savings_account_date));
            })
            ->editColumn('savings_account_first_deposit_amount', function (AcctSavingsAccount $model) {
                return number_format($model->savings_account_first_deposit_amount, 2);
            })
            ->editColumn('savings_account_last_balance', function (AcctSavingsAccount $model) {
                return number_format($model->savings_account_last_balance, 2);
            })
            ->addColumn('action', 'content.AcctSavingsAccount.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctSavingsAccount/AcctSavingsAccountDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctSavingsAccount $model)
    {
        $sessiondata = session()->get('filter_savingsaccount');
        if(!$sessiondata){
            $sessiondata = array(
                'savings_id' => null,
                'branch_id' => auth()->user()->branch_id,
            );
        }
        if(!$sessiondata['branch_id'] || !$sessiondata['branch_id']==0){
            $sessiondata['branch_id'] = auth()->user()->branch_id;
        }

        $querydata = $model->newQuery()
        ->select('acct_savings_account.savings_account_id', 'core_member.member_name', 'acct_savings.savings_name','acct_savings.savings_id', 'acct_savings_account.savings_account_no','acct_savings_account.unblock_state', 'acct_savings_account.savings_account_date', 'acct_savings_account.savings_account_first_deposit_amount', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.validation')
        ->join('core_member', 'acct_savings_account.member_id', '=', 'core_member.member_id')
        ->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
        ->where('acct_savings_account.data_state', 0)
        ->where('acct_savings.savings_status', 0)
        ->where('core_member.branch_id', $sessiondata['branch_id']);
        if($sessiondata['savings_id']){
            $querydata = $querydata->where('acct_savings_account.savings_id', $sessiondata['savings_id']);
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
                    ->setTableId('savings-account-table')
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
            Column::make('acct_savings_account.savings_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('acct_savings_account.savings_account_no')->title(__('No Rekening'))->data('savings_account_no'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('acct_savings.savings_name')->title(__('Jenis Tabungan'))->data('savings_name'),
            Column::make('acct_savings_account.savings_account_date')->title(__('Tanggal Buka'))->data('savings_account_date'),
            Column::make('acct_savings_account.savings_account_first_deposit_amount')->title(__('Setoran Awal'))->data('savings_account_first_deposit_amount'),
            Column::make('acct_savings_account.savings_account_last_balance')->title(__('Saldo'))->data('savings_account_last_balance'),
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
        return 'Tabungan_' . date('YmdHis');
    }
}
