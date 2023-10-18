<?php

namespace App\DataTables;

use App\Models\AcctCredits;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;
use Illuminate\Support\Facades\DB;

class AcctCreditsDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', 'content.AcctCredits.List._action-menu');
    }

    public function query(AcctCredits $model)
    {
        return $model->newQuery()
        ->select('acct_credits.*', DB::Raw('CONCAT(first_account.account_code, " - ", first_account.account_name) as first_full_account'), DB::Raw('CONCAT(second_account.account_code, " - ", second_account.account_name) as second_full_account'))
        ->join('acct_account as first_account', 'first_account.account_id', 'acct_credits.receivable_account_id')
        ->join('acct_account as second_account', 'second_account.account_id', 'acct_credits.income_account_id')
        ->where('acct_credits.data_state', 0);
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('credits-table')
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
            Column::make('acct_credits.credits_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('credits_code')->title(__('Kode')),
            Column::make('credits_name')->title(__('Nama')),
            Column::make('first_account.account_code', 'first_account.account_name')->title(__('Nomor Perkiraan'))->data('first_full_account'),
            Column::make('second_account.account_code', 'second_account.account_name')->title(__('Nomor Perkiraan Bunga'))->data('second_full_account'),
            Column::make('credits_fine')->title(__('Presentase Denda')),
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
