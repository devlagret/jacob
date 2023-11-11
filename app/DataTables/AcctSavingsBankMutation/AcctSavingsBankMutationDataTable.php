<?php

namespace App\DataTables\AcctSavingsBankMutation;

use App\Models\AcctSavingsBankMutation;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;
use Illuminate\Support\Facades\DB;

class AcctSavingsBankMutationDataTable extends DataTable
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
            ->editColumn('savings_bank_mutation_date', function (AcctSavingsBankMutation $model) {
                return date('d-m-Y', strtotime($model->savings_bank_mutation_date));
            })
            ->editColumn('savings_bank_mutation_amount', function (AcctSavingsBankMutation $model) {
                return number_format($model->savings_bank_mutation_amount, 2);
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctSavingsBankMutation/AcctSavingsBankMutationDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctSavingsBankMutation $model)
    {
        $sessiondata = session()->get('filter_savingsbankmutation');
        if(!$sessiondata){
            $sessiondata = array(
                'start_date'    => date('Y-m-d'),
                'end_date'      => date('Y-m-d'),
            );
        }

        $querydata = $model->newQuery()
        ->withoutGlobalScopes()
        ->select(DB::raw("CONCAT(acct_account.account_code,' - ',acct_bank_account.bank_account_name) AS full_account"), 'acct_savings_bank_mutation.savings_bank_mutation_id', 'acct_savings_bank_mutation.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'acct_savings_account.savings_id', 'acct_savings.savings_name', 'acct_savings_bank_mutation.savings_bank_mutation_date', 'acct_savings_bank_mutation.savings_bank_mutation_amount', 'acct_savings_bank_mutation.bank_account_id', 'acct_bank_account.bank_account_name', 'acct_bank_account.account_id', 'acct_account.account_code')
        ->join('acct_bank_account', 'acct_savings_bank_mutation.bank_account_id', '=', 'acct_bank_account.bank_account_id')
        ->join('acct_account', 'acct_bank_account.account_id', '=', 'acct_account.account_id')
        ->join('acct_savings_account', 'acct_savings_bank_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('core_member', 'acct_savings_bank_mutation.member_id', '=', 'core_member.member_id')
        ->join('acct_savings', 'acct_savings_bank_mutation.savings_id', '=', 'acct_savings.savings_id')
        ->where('acct_savings_bank_mutation.savings_bank_mutation_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_savings_bank_mutation.savings_bank_mutation_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('core_member.branch_id', auth()->user()->branch_id);

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
                    ->setTableId('savings-bank-mutation-table')
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
            Column::make('acct_savings_bank_mutation.savings_bank_mutation_date')->title(__('Tanggal Transfer'))->data('savings_bank_mutation_date'),
            Column::make('acct_mutation.full_account')->title(__('Transfer Bank'))->data('full_account'),
            Column::make('acct_savings_bank_mutation.savings_bank_mutation_amount')->title(__('Jumlah'))->data('savings_bank_mutation_amount'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Mutasi_Bank' . date('YmdHis');
    }
}
