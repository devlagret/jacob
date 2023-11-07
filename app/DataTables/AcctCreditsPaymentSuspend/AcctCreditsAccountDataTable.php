<?php

namespace App\DataTables\AcctCreditsAcquittance;

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
            ->addColumn('action', 'content.AcctCreditsAcquittance.Add.AcctCreditsAccountModal._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctCreditsAcquittance/AcctCreditsAccountDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctCreditsAccount $model)
    {
        return $model->newQuery()->with('member')->where('acct_credits_account.credits_account_status', 0)
        ->where('acct_credits_account.credits_approve_status', 1)
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
                    ->setTableId('deposito-account-modal-credits-account-table')
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
            Column::make('credits_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('credits_account_serial')->title(__('No Akad Pinjaman')),
            Column::make('member.member_name')->title(__('Nama Anggota')),
            Column::make('member.member_no')->title(__('No Anggota')),
            Column::make('credits_account_date')->title(__('Tanggal Pinjam')),
            Column::make('credits_account_due_date')->title(__('Tanggal Jatuh Tempo')),
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
        return 'AcctCreditsAcquittance/AcctCreditsAccount_' . date('YmdHis');
    }
}
