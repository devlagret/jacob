<?php

namespace App\DataTables;

use App\Models\AcctCreditsAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctCreditsAccountHistoryDataTable extends DataTable
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
            ->editColumn('credits_account_date', function (AcctCreditsAccount $model) {
                return date('d-m-Y', strtotime($model->credits_account_date));
            })
            ->editColumn('credits_account_amount', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_amount, 2);
            })
            ->editColumn('credits_account_interest', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_interest, 2);
            })
            ->editColumn('credits_account_status', function (AcctCreditsAccount $model) {
                $creditsapprovestatus = Configuration::CreditsApproveStatus();
                return $creditsapprovestatus[$model->credits_account_status];
            })
            ->addColumn('action', 'content.AcctCreditsAccountHistory.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctCreditsAccount/AcctCreditsAccountDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctCreditsAccount $model)
    {
        $sessiondata = session()->get('filter_creditsaccounthistory');
        if(!$sessiondata){
            $sessiondata = array(
                'start_date'    => date('Y-m-d'),
                'end_date'      => date('Y-m-d'),
                'credits_id'    => null,
                'branch_id'     => auth()->user()->branch_id,
            );
        }
        if(!$sessiondata['branch_id'] || !$sessiondata['branch_id']==0){
            $sessiondata['branch_id'] = auth()->user()->branch_id;
        }

        $querydata = $model->newQuery()->with('member','credit','sourcefund')->where('credits_account_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
		->where('credits_account_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->whereHas('member',function (Builder $query) use($sessiondata) {
            $query->where('branch_id',  $sessiondata['branch_id']??Auth::user()->branch_id);
        });
        if($sessiondata['credits_id']){
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
                    ->setTableId('credits-account-history-table')
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
            Column::make('credits_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('credits_account_serial')->title(__('No Rekening')),
            Column::make('member.member_name')->title(__('Nama Anggota')),
            Column::make('credit.credits_name')->title(__('Jenis Pinjaman')),
            Column::make('sourcefund.source_fund_name')->title(__('Sumber Dana')),
            Column::make('credits_account_date')->title(__('Tanggal Pinjam')),
            Column::make('credits_account_amount')->title(__('Pokok')),
            Column::make('credits_account_status')->title(__('Status')),
            Column::computed('action')
                    ->title(__('Aksi'))
                    ->exportable(false)
                    ->printable(false)
                    ->width(300)
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
        return 'Master_Data_Pinjaman_' . date('YmdHis');
    }
}
