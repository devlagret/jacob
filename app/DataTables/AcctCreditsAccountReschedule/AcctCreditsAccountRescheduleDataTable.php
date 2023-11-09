<?php

namespace App\DataTables\AcctCreditsAccountReschedule;

use App\Models\AcctCreditsAccountReschedule;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctCreditsAccountRescheduleDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $period=Configuration::CreditsPaymentPeriod();
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            // ->editColumn('credits_account_period_new', fn($model)=>$period[$model->credits_account_period_new])
            ->editColumn('credits_account_date_new', function (AcctCreditsAccountReschedule $model) {
                return date('d-m-Y', strtotime($model->credits_account_date_new));
            })
            ->editColumn('credits_account_date_new', function (AcctCreditsAccountReschedule $model) {
                return date('d-m-Y', strtotime($model->credits_account_date_new));
            })
            ->addColumn('action', 'content.AcctCreditsAccountReschedule.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctCreditsAccountReschedule/AcctCreditsAccountRescheduleDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctCreditsAccountReschedule $model)
    {
        $sessiondata = Session::get('filter-credit-accountreschedull');

        $querydata = $model->newQuery()->with('member','account','credit');
        if(!empty($sessiondata['credits_id'])){
            $querydata = $querydata->where('credits_id', $sessiondata['credits_id']);
        }

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
                    ->setTableId('acct-credits-reschedule-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    ->orderBy(0, 'asc')
                    ->responsive(false)
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
            Column::make('credits_account_reschedule_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('account.credits_account_serial')->title(__('No Pinjaman')),
            Column::make('member.member_name')->title(__('Nama Anggota')),
            Column::make('credit.credits_name')->title(__('Jenis Pinjaman')),
            Column::make('credits_account_period_old')->title(__('Periode lama')),
            Column::make('credits_account_period_new')->title(__('Periode Baru')),
            Column::make('credits_account_date_old')->title(__('Tanggal Pinjaman Lama')),
            Column::make('credits_account_date_new')->title(__('Tanggal Pinjaman Baru')),
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
        return 'Reschedule_Pinjaman' . date('YmdHis');
    }
}
