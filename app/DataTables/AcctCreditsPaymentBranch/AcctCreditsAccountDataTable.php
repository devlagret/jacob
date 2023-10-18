<?php

namespace App\DataTables\AcctCreditsPaymentBranch;

use App\Models\AcctCreditsAccount;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

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
            ->addIndexColumn()
            ->editColumn('credits_account_date', function (AcctCreditsAccount $model) {
                return date('d-m-Y', strtotime($model->credits_account_date));
            })
            ->editColumn('credits_account_due_date', function (AcctCreditsAccount $model) {
                return date('d-m-Y', strtotime($model->credits_account_due_date));
            })
            ->addColumn('action', 'content.AcctCreditsPaymentBranch.Add.AcctCreditsAccountModal._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctCreditsPaymentBranch/AcctCreditsAccountDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctCreditsAccount $model)
    {
        return $model->newQuery()
        ->select('acct_credits_account.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.credits_account_date', 'acct_credits_account.credits_account_due_date', 'core_member.member_name', 'core_member.member_no')
        ->join('core_member', 'core_member.member_id', '=', 'acct_credits_account.member_id')
        ->where('acct_credits_account.credits_account_status', 0)
        ->where('acct_credits_account.credits_approve_status', 1)
        ->where('acct_credits_account.data_state', 0)
        ->where('acct_credits_account.branch_id', auth()->user()->branch_id);
    }
    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('payment-branch-modal-credits-account-table')
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
            Column::make('acct_credits_account.credits_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('acct_credits_account.credits_account_serial')->title(__('No Akad Pinjaman'))->data('credits_account_serial'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('core_member.member_no')->title(__('No Anggota'))->data('member_no'),
            Column::make('acct_credits_account.credits_account_date')->title(__('Tanggal Pinjam'))->data('credits_account_date'),
            Column::make('acct_credits_account.credits_account_due_date')->title(__('Tanggal Jatuh Tempo'))->data('credits_account_due_date'),
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
        return 'AcctCreditsPaymentBranch/AcctCreditsAccount_' . date('YmdHis');
    }
}
