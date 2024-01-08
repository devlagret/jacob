<?php

namespace App\DataTables\AcctSavingsCashMutation;

use App\Models\AcctSavingsCashMutation;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctSavingsCashMutationDataTable extends DataTable
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
            ->editColumn('savings_cash_mutation_date', function (AcctSavingsCashMutation $model) {
                return date('d-m-Y', strtotime($model->savings_cash_mutation_date));
            })
            ->editColumn('savings_cash_mutation_amount', function (AcctSavingsCashMutation $model) {
                return number_format($model->savings_cash_mutation_amount, 2);
            })
            ->editColumn('savings_cash_mutation_status', function (AcctSavingsCashMutation $model) {
                $savingscashmutationstatus = Configuration::SavingsCashMutationStatus();

                return $savingscashmutationstatus[$model->savings_cash_mutation_status];
            })
            ->addColumn('action', 'content.AcctSavingsCashMutation.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctSavingsCashMutation/AcctSavingsCashMutationDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctSavingsCashMutation $model)
    {
        $sessiondata = session()->get('filter_savingscashmutation');
        if(!$sessiondata){
            $sessiondata = array(
                'start_date'    => date('Y-m-d'),
                'end_date'      => date('Y-m-d'),
            );
        }

        $querydata = $model->newQuery()
        ->withoutGlobalScopes()
        ->select('acct_savings_cash_mutation.*', 'acct_mutation.*', 'acct_savings_account.*', 'core_member.*', 'acct_savings.*', 'acct_savings_cash_mutation.validation')
        ->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id', '=', 'acct_mutation.mutation_id')
        ->join('acct_savings_account', 'acct_savings_cash_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('core_member', 'acct_savings_cash_mutation.member_id', '=', 'core_member.member_id')
        ->join('acct_savings', 'acct_savings_cash_mutation.savings_id', '=', 'acct_savings.savings_id')
        ->where('acct_savings_cash_mutation.savings_cash_mutation_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_savings_cash_mutation.savings_cash_mutation_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('core_member.branch_id', auth()->user()->branch_id)
        ->where('acct_savings_cash_mutation.pickup_state', 1);

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
                    ->setTableId('savings-cash-mutation-table')
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
            Column::make('acct_savings_cash_mutation.savings_cash_mutation_date')->title(__('Tanggal Mutasi'))->data('savings_cash_mutation_date'),
            Column::make('acct_mutation.mutation_name')->title(__('Jenis Mutasi'))->data('mutation_name'),
            Column::make('acct_savings_cash_mutation.savings_cash_mutation_amount')->title(__('Jumlah'))->data('savings_cash_mutation_amount'),
            Column::make('acct_savings_cash_mutation.savings_cash_mutation_status')->title(__('Diinput Dari'))->data('savings_cash_mutation_status'),
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
        return 'Mutasi_Tunai' . date('YmdHis');
    }
}
