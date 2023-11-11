<?php

namespace App\DataTables\MemberSavingsTransferMutation;

use App\Models\CoreMemberTransferMutation;
use App\Models\CoreMember;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class MemberSavingsTransferMutationDataTable extends DataTable
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
            ->editColumn('member_mandatory_savings', function (CoreMemberTransferMutation $model) {

                return number_format($model->member_mandatory_savings,2);
            })
            ->editColumn('member_id', function (CoreMemberTransferMutation $model) {
                $coremember = CoreMember::where('member_id', $model->member_id)
                ->first();
                return $coremember->member_name;
            })
            ->editColumn('member_transfer_mutation_date', function (CoreMemberTransferMutation $model) {

                if ($model->member_transfer_mutation_date == null) {
                    return '';
                } else {
                    return date('d-m-Y', strtotime($model->member_transfer_mutation_date));
                }
            })
            ->addIndexColumn()
            ->addColumn('action', 'content.MemberSavingsTransferMutation.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\MemberSavingsTransferMutation/MemberSavingsTransferMutationDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(CoreMemberTransferMutation $model)
    {
        $session = session()->get('filter_membersavingstransfermutation');
        
        if (empty($session['start_date'])) {
            $start_date = date('Y-m-d');
        } else {
            $start_date = date('Y-m-d', strtotime($session['start_date']));
        }
        if (empty($session['end_date'])) {
            $end_date = date('Y-m-d');
        } else {
            $end_date = date('Y-m-d', strtotime($session['end_date']));
        }

        if (empty($session['member_id'])) {
            return $model->newQuery()
            ->withoutGlobalScopes()
            ->select('core_member_transfer_mutation.member_transfer_mutation_id', 'core_member_transfer_mutation.member_transfer_mutation_date', 'core_member_transfer_mutation.member_mandatory_savings', 'core_member_transfer_mutation.validation', 'core_member_transfer_mutation.validation_id', 'core_member_transfer_mutation.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member_transfer_mutation.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id')
            ->join('acct_savings_account', 'core_member_transfer_mutation.savings_account_id','=','acct_savings_account.savings_account_id')
            ->join('core_member', 'core_member_transfer_mutation.member_id','=','core_member.member_id')
            ->where('core_member_transfer_mutation.member_transfer_mutation_date', '>=', $start_date)
            ->where('core_member_transfer_mutation.member_transfer_mutation_date', '<=', $end_date)
            ->where('core_member_transfer_mutation.data_state', 0);
        } else {
            return $model->newQuery()
            ->withoutGlobalScopes()
            ->select('core_member_transfer_mutation.member_transfer_mutation_id', 'core_member_transfer_mutation.member_transfer_mutation_date', 'core_member_transfer_mutation.member_mandatory_savings', 'core_member_transfer_mutation.validation', 'core_member_transfer_mutation.validation_id', 'core_member_transfer_mutation.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member_transfer_mutation.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id')
            ->join('acct_savings_account', 'core_member_transfer_mutation.savings_account_id','=','acct_savings_account.savings_account_id')
            ->join('core_member', 'core_member_transfer_mutation.member_id','=','core_member.member_id')
            ->where('core_member_transfer_mutation.member_transfer_mutation_date', '>=', $start_date)
            ->where('core_member_transfer_mutation.member_transfer_mutation_date', '<=', $end_date)
            ->where('core_member_transfer_mutation.data_state', 0)
            ->where('core_member_transfer_mutation.member_id', $session['member_id']);
        }
        
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('membersavingstransfermutation-membersavingstransfermutationdatatable-table')
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
            Column::make('member_transfer_mutation_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('core_member_transfer_mutation.member_transfer_mutation_date')->title(__('Tanggal Transfer'))->data('member_transfer_mutation_date'),
            Column::make('core_member.member_no')->title(__('No. Anggota'))->data('member_no'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('acct_savings_account.savings_account_no')->title(__('No. Rekening'))->data('savings_account_no'),
            Column::make('acct_savings_account.member_id')->title(__('Nama'))->data('member_id'),
            Column::make('core_member_transfer_mutation.member_mandatory_savings')->title(__('Simpanan Wajib'))->data('member_mandatory_savings'),
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
        return 'MemberSavingsTransferMutation/MemberSavingsTransferMutation_' . date('YmdHis');
    }
}
