<?php

namespace App\DataTables;

use App\Models\AcctSavings;
use App\Models\AcctSavingsCashMutation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\DB;

class NominativeSavingsPickupDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            // ->editColumn('savings_profit_sharing', function (AcctSavings $model) {
            //     $savingsprofitsharing = Configuration::SavingsProfitSharing();

            //     return $savingsprofitsharing[$model->savings_profit_sharing];
            // })
            ->addColumn('action', 'content.NominativeSavings.Pickup.List._action-menu');
    }

    public function query(AcctSavingsCashMutation $model)
    {
        $sessiondata = Session::get('pickup-data');
        return $model->newQuery()->with('member','mutation')
        ->where('data_state', 0)
        ->where('savings_cash_mutation_status', 1)
        ->where('savings_cash_mutation_date','>=',Carbon::parse($sessiondata['start_date']??Carbon::now())->format('Y-m-d'))
        ->where('savings_cash_mutation_date','<=',Carbon::parse($sessiondata['end_date']??Carbon::now())->format('Y-m-d'))
        ;
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('pickup-table')
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
            Column::make('savings_cash_mutation_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('savings_cash_mutation_date')->title(__('Tanggal')),
            Column::make('operated_name')->title(__('Nama Operator')),
            Column::make('member.member_name')->title(__('Nama Anggota')),
            Column::make('mutation.mutation_name')->title(__('Transaksi')),
            Column::make('savings_cash_mutation_amount')->title(__('Jumlah')),
            Column::make('savings_cash_mutation_remark')->title(__('Keterngan')),
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
