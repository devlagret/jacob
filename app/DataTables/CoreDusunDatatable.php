<?php

namespace App\DataTables;

use App\Models\CoreDusun;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CoreDusunDatatable extends DataTable
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
            ->addColumn('action', 'content.CoreDusun.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\CoreDusun $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(CoreDusun $model)
    {
        $filter = Session::get('dusun-fiter');
        $query = $model->newQuery()->with('kelurahan')->where('data_state', 0);
        if(isset($filter)){
           $query = $query->where('kelurahan_id',$filter['kelurahan_id']);
        }
        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('dusun-table')
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
            Column::make('dusun_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('kelurahan.kelurahan_name')->title(__('Kelurahan')),
            Column::make('dusun_name')->title(__('Nama Dusun')),
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
        return 'CoreDusun_' . date('YmdHis');
    }
}
