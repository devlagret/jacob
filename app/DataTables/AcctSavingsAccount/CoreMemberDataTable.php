<?php

namespace App\DataTables\AcctSavingsAccount;

use App\Models\CoreMember;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CoreMemberDataTable extends DataTable
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
            ->addColumn('action', 'content.AcctSavingsAccount.Add.CoreMemberModal._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctSavingsAccount/CoreMemberDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(CoreMember $model)
    {
        return $model->newQuery()
        // ->with('savingAccount')
        ->select('core_member.*','acct_savings_account.*','acct_savings.*')
        ->join('acct_savings_account','acct_savings_account.member_id','core_member.member_id')
        ->join('acct_savings','acct_savings.savings_id','acct_savings_account.savings_id')
        ->where('core_member.member_active_status', 0)
        ->where('core_member.data_state', 0)
        ->where('core_member.branch_id', auth()->user()->branch_id);
    }
    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('savings-account-modal-member-table')
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
            Column::make('core_member.member_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('core_member.member_no')->title(__('No Anggota'))->data('member_no'),
            Column::make('acct_savings.savings_name')->title(__('Jenis Simpanan'))->data('savings_name'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('core_member.member_address')->title(__('Alamat'))->data('member_address'),
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
        return 'AcctSavingsAccount/CoreMember_' . date('YmdHis');
    }
}
