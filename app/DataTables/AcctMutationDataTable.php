<?php

namespace App\DataTables;

use App\Models\AcctMutation;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctMutationDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('mutation_status', function (AcctMutation $model) {
                $accountstatus = Configuration::AccountStatus();

                return $accountstatus[$model->mutation_status];
            })
            ->addColumn('action', 'content.AcctMutation.List._action-menu');
    }

    public function query(AcctMutation $model)
    {
        return $model->newQuery()
        ->where('data_state', 0);
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('mutation-table')
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
            Column::make('mutation_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('mutation_code')->title(__('Kode')),
            Column::make('mutation_name')->title(__('Nama')),
            Column::make('mutation_function')->title(__('Fungsi')),
            Column::make('mutation_status')->title(__('D/K')),
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
