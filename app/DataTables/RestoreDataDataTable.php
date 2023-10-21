<?php

namespace App\DataTables;

use App\Models\RestoreDatum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class RestoreDataDataTable extends DataTable
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
            ->query($query)
             ->addColumn('action', function($model){
                return view('content.RestoreData.Table._action-menu',['pk'=>collect(DB::select("SHOW KEYS FROM ".Session::get('restore-table')." WHERE Key_name = 'PRIMARY'"))->pluck('Column_name')[0]
                ,'model'=>$model ]);
            });
    }

    /**
     * Get query source of dataTable.
     *l
     */
    public function query()
    {
        return DB::table(Session::get('restore-table'))->where('data_state','1');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('restoredata-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    // ->dom('Bfrtip')
                    ->parameters(['scrollX' => true])
                    ->orderBy(1)
                    ->responsive()
                    ->autoWidth(true)
                    ->addTableClass('align-middle table-row-dashed fs-6 gy-5');
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $header = collect(DB::select('DESCRIBE '.Session::get('restore-table')))->pluck('Field');
        $compute = collect();
        $compute->push(Column::computed('action')
        ->exportable(false)
        ->printable(false)
        ->width(60)
        ->addClass('text-center'));
        foreach ($header as $key => $value) {
            $compute->push(Column::make($value));
        }
        return $compute->toArray();
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'RestoreData_' . date('YmdHis');
    }
}
