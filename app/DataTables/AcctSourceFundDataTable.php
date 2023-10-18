<?php

namespace App\DataTables;

use App\Models\AcctSourceFund;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AcctSourceFundDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', 'content.AcctSourceFund.List._action-menu');
    }

    public function query(AcctSourceFund $model)
    {
        return $model->newQuery()
        ->where('data_state', 0);
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('source-fund-table')
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
            Column::make('source_fund_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('source_fund_code')->title(__('Kode')),
            Column::make('source_fund_name')->title(__('Nama')),
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
        return 'SourceFund_' . date('YmdHis');
    }
}
