<?php

namespace App\DataTables;

use App\Helpers\Configuration;
use App\Models\AcctAccount;
use App\Models\CoreDusun;
use App\Models\PreferenceIncome;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PreferenceIncomeDataTable extends DataTable
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
            ->addColumn('percentage', 'content.PreferenceIncome._percent')
            ->addColumn('group', function(PreferenceIncome $p,Configuration $c){
                return view('content.PreferenceIncome._group',['kp'=>$c->KelompokPerkiraan(),'model'=>$p ]);
            })
            ->addColumn('account', function(PreferenceIncome $p,AcctAccount $acc){
                return view('content.PreferenceIncome._account',['akun'=>$acc->
                select(DB::raw("account_id, CONCAT(account_code,' - ', account_name) as account_code "))->where('data_state',0)->get()->pluck('account_code','account_id')
                ,'model'=>$p ]);
            })
            ->addColumn('action', 'content.PreferenceIncome._action-menu')
            ->rawColumns(['percentage','action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\CoreDusun $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(PreferenceIncome $model)
    {
        return $model->newQuery()->with('account')->where('data_state', 0);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('income-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    ->orderBy(0, 'asc')
                    ->responsive()
                    ->autoWidth(true)
                    ->parameters(['scrollX' => true])
                    ->addTableClass('align-middle table-row-dashed fs-6 gy-5')
                    ->parameters([
                        'drawCallback' => "function() {
                            $('.select-acc').select2({theme: 'bootstrap5',color:'resolve'} )
                            $('.select-group').select2({theme: 'bootstrap5'} )
                        }",
                    ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('income_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('income_name')->title(__('Nama')),
            Column::computed('percentage')->title(__('Persen'))->exportable(false),
            Column::computed('group')->title(__('Kelompok')),
            Column::computed('account')->title(__('Perkiraan')),
            Column::computed('action') 
                    ->title(__('Aksi'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(50)
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
        return 'preference_income_' . date('YmdHis');
    }
}
