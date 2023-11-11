<?php

namespace App\DataTables\SavingsTransferMutation;

use App\Models\AcctSavingsTransferMutation;
use App\Models\AcctSavingsAccount;
use App\Models\CoreMember;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SavingsTransferMutationDataTable extends DataTable
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
            ->editColumn('savings_transfer_mutation_amount', function(AcctSavingsTransferMutation $model){

                return number_format($model->savings_transfer_mutation_amount,2);

            })
            ->editColumn('savings_transfer_mutation_date', function(AcctSavingsTransferMutation $model){

                return date('d-m-Y', strtotime($model->savings_transfer_mutation_date));

            })
            ->editColumn('savings_account_id_from', function(AcctSavingsTransferMutation $model) {

                $savingsaccount = AcctSavingsAccount::where('savings_account_id', $model->savings_account_id_from)
                ->first();
                $coremember = CoreMember::where('member_id', $savingsaccount->member_id)
                ->first();

                return $savingsaccount->savings_account_no.' - '.$coremember->member_name;

            })
            ->editColumn('savings_account_id_to', function(AcctSavingsTransferMutation $model) {

                $savingsaccount = AcctSavingsAccount::where('savings_account_id', $model->savings_account_id_to)
                ->first();
                $coremember = CoreMember::where('member_id', $savingsaccount->member_id)
                ->first();

                return $savingsaccount->savings_account_no.' - '.$coremember->member_name;

            })
            ->addIndexColumn()
            ->addColumn('action', 'content.SavingsTransferMutation.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctSavingsTransferMutation $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctSavingsTransferMutation $model)
    {
        $session = session()->get('filter_savingstransfermutation');
        
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

        return $model->newQuery()
        ->withoutGlobalScopes()
        ->select('acct_savings_transfer_mutation.savings_transfer_mutation_date','acct_savings_transfer_mutation.savings_transfer_mutation_amount','acct_savings_transfer_mutation.validation','acct_savings_transfer_mutation.savings_transfer_mutation_id','acct_savings_transfer_mutation_from.savings_account_id as savings_account_id_from','acct_savings_transfer_mutation_to.savings_account_id as savings_account_id_to')
        ->join('acct_savings_transfer_mutation_from', 'acct_savings_transfer_mutation.savings_transfer_mutation_id', '=', 'acct_savings_transfer_mutation_from.savings_transfer_mutation_id')
        ->join('acct_savings_transfer_mutation_to', 'acct_savings_transfer_mutation.savings_transfer_mutation_id','=', 'acct_savings_transfer_mutation_to.savings_transfer_mutation_id')
        ->where('acct_savings_transfer_mutation.savings_transfer_mutation_date','>=', $start_date)
        ->where('acct_savings_transfer_mutation.savings_transfer_mutation_date','<=', $end_date)
        ->where('acct_savings_transfer_mutation.data_state', 0);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('savingstransfermutation-savingstransfermutationdatatable-table')
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
            Column::make('acct_savings_transfer_mutation.savings_transfer_mutation_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('savings_transfer_mutation_date')->title(__('Tanggal Transfer')),
            Column::make('acct_savings_transfer_mutation_from.savings_account_id')->title(__('No. Rekening Asal'))->data('savings_account_id_from'),
            Column::make('acct_savings_transfer_mutation_to.savings_account_id')->title(__('No. Rekening Tujuan'))->data('savings_account_id_to'),
            Column::make('acct_savings_transfer_mutation.savings_transfer_mutation_amount')->title(__('Jumlah Transfer'))->data('savings_transfer_mutation_amount'),
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
        return 'SavingsTransferMutation/SavingsTransferMutation_' . date('YmdHis');
    }
}
