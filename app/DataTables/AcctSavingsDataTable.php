<?php

namespace App\DataTables;

use App\Models\AcctSavings;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;
use Illuminate\Support\Facades\DB;

class AcctSavingsDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('savings_profit_sharing', function (AcctSavings $model) {
                $savingsprofitsharing = Configuration::SavingsProfitSharing();

                return $savingsprofitsharing[$model->savings_profit_sharing];
            })
            ->addColumn('action', 'content.AcctSavings.List._action-menu');
    }

    public function query(AcctSavings $model)
    {
        return $model->newQuery()
        ->select('acct_savings.*', DB::Raw('CONCAT(acct_account.account_code, " - ", acct_account.account_name) as full_account'))
        ->join('acct_account', 'acct_account.account_id', 'acct_savings.account_id')
        ->where('acct_savings.savings_status', 0)
        ->where('acct_savings.data_state', 0);
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('savings-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    ->orderBy(0, 'asc')
                    ->responsive()
                    ->autoWidth(true)
                    ->parameters(['scrollX' => true])
                    ->addTableClass('align-middle table-row-dashed fs-6 gy-5');
    }

    protected function getColumns()
    {
        return [
            Column::make('acct_savings.savings_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('savings_code')->title(__('Kode')),
            Column::make('savings_name')->title(__('Nama')),
            Column::make('acct_account.account_code', 'acct_account.account_name')->title(__('Nomor Perkiraan'))->data('full_account'),
            Column::make('savings_profit_sharing')->title(__('Status Bunga')),
            Column::make('savings_interest_rate')->title(__('Nilai Bunga')),
            Column::computed('action')
                    ->title(__('Aksi'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(300)
                    ->addClass('text-center'),
        ];
    }

    protected function filename()
    {
        return 'Mutation_' . date('YmdHis');
    }
}
