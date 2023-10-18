<?php

namespace App\DataTables;

use App\Models\AcctCreditsAgunan;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctCreditsAgunanDataTable extends DataTable
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
            ->editColumn('credits_agunan_status', function (AcctCreditsAgunan $model) {
                $agunanstatus = Configuration::AgunanStatus();
                return $agunanstatus[$model->credits_agunan_status];
            })
            ->editColumn('credits_agunan_type', function (AcctCreditsAgunan $model) {
				if($model->credits_agunan_type == 1){
					return 'BPKB';
				}else if($model->credits_agunan_type == 2) {
					return 'Sertifikat';
				}else if($model->credits_agunan_type == 3){
					return'Bilyet Simpanan Berjangka';
				}else if($model->credits_agunan_type == 4){
					return 'Elektro';
				}else if($model->credits_agunan_type == 5){
					return 'Dana Keanggotaan';
				}else if($model->credits_agunan_type == 6){
					return 'Tabungan';
				}else if($model->credits_agunan_type == 7){
					return 'ATM / Jamsostek';
				}
            })
            ->editColumn('credits_agunan_shm_taksiran', function (AcctCreditsAgunan $model) {
                if($model->credits_agunan_shm_taksiran){
                    $nominal = $model->credits_agunan_shm_taksiran;
                }else{
                    $nominal = 0;
                }
                return number_format($nominal, 2);
            })
            ->editColumn('credits_agunan_bpkb_taksiran', function (AcctCreditsAgunan $model) {
                if($model->credits_agunan_bpkb_taksiran){
                    $nominal = $model->credits_agunan_bpkb_taksiran;
                }else{
                    $nominal = 0;
                }
                return number_format($nominal, 2);
            })
            ->editColumn('credits_agunan_atmjamsostek_taksiran', function (AcctCreditsAgunan $model) {
                if($model->credits_agunan_atmjamsostek_taksiran){
                    $nominal = $model->credits_agunan_atmjamsostek_taksiran;
                }else{
                    $nominal = 0;
                }
                return number_format($nominal, 2);
            })
            ->editColumn('agunan_remark', function (AcctCreditsAgunan $model) {
				if($model->credits_agunan_type == 1){
					return $model->credits_agunan_bpkb_keterangan;
				}else if($model->credits_agunan_type == 2) {
					return $model->credits_agunan_shm_keterangan;
				}else if($model->credits_agunan_type == 3){
					return $model->credits_agunan_other_keterangan;
				}else if($model->credits_agunan_type == 4){
					return $model->credits_agunan_other_keterangan;
				}else if($model->credits_agunan_type == 5){
					return $model->credits_agunan_other_keterangan;
				}else if($model->credits_agunan_type == 6){
					return $model->credits_agunan_other_keterangan;
				}else if($model->credits_agunan_type == 7){
					return $model->credits_agunan_atmjamsostek_keterangan;
				}
            })
            ->addColumn('action', 'content.AcctCreditsAgunan.List._action-menu');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctCreditsAgunan/AcctCreditsAgunanDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctCreditsAgunan $model)
    {
        $sessiondata = session()->get('filter_creditagunanmaster');
        if(!$sessiondata){
            $sessiondata = array(
                'branch_id'     => auth()->user()->branch_id,
            );
        }
        if(!$sessiondata['branch_id'] || !$sessiondata['branch_id']==0){
            $sessiondata['branch_id'] = auth()->user()->branch_id;
        }

        $querydata = $model->newQuery()
        ->select('acct_credits_agunan.*', 'acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name')
        ->join('acct_credits_account','acct_credits_agunan.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->join('core_member','acct_credits_account.member_id', '=', 'core_member.member_id')
		->where('acct_credits_account.data_state', 0)
        ->where('acct_credits_account.branch_id', $sessiondata['branch_id']);

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
                    ->setTableId('credits-agunan-master-table')
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
            Column::make('acct_credits_agunan.credits_agunan_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('acct_credits_account.credits_account_serial')->title(__('No Akad'))->data('credits_account_serial'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('acct_credits_agunan.credits_agunan_status')->title(__('Status Agunan'))->data('credits_agunan_status'),
            Column::make('acct_credits_agunan.credits_agunan_type')->title(__('Tipe Agunan'))->data('credits_agunan_type'),
            Column::make('acct_credits_agunan.credits_agunan_shm_no_sertifikat')->title(__('Sertifikat'))->data('credits_agunan_shm_no_sertifikat'),
            Column::make('acct_credits_agunan.credits_agunan_shm_luas')->title(__('Luas'))->data('credits_agunan_shm_luas'),
            Column::make('acct_credits_agunan.credits_agunan_shm_atas_nama')->title(__('Atas Nama'))->data('credits_agunan_shm_atas_nama'),
            Column::make('acct_credits_agunan.credits_agunan_shm_kedudukan')->title(__('Kedudukan'))->data('credits_agunan_shm_kedudukan'),
            Column::make('acct_credits_agunan.credits_agunan_shm_taksiran')->title(__('Taksiran'))->data('credits_agunan_shm_taksiran'),
            Column::make('acct_credits_agunan.credits_agunan_bpkb_nomor')->title(__('BPKB'))->data('credits_agunan_bpkb_nomor'),
            // Column::make('acct_credits_agunan.credits_agunan_bpkb_type')->title(__('Jenis'))->data('credits_agunan_bpkb_type'),
            Column::make('acct_credits_agunan.credits_agunan_bpkb_nama')->title(__('Nama'))->data('credits_agunan_bpkb_nama'),
            // Column::make('acct_credits_agunan.credits_agunan_bpkb_address')->title(__('Alamat'))->data('credits_agunan_bpkb_address'),
            Column::make('acct_credits_agunan.credits_agunan_bpkb_nopol')->title(__('No Polisi'))->data('credits_agunan_bpkb_nopol'),
            Column::make('acct_credits_agunan.credits_agunan_bpkb_no_rangka')->title(__('No Rangka'))->data('credits_agunan_bpkb_no_rangka'),
            Column::make('acct_credits_agunan.credits_agunan_bpkb_no_mesin')->title(__('No Mesin'))->data('credits_agunan_bpkb_no_mesin'),
            Column::make('acct_credits_agunan.credits_agunan_bpkb_taksiran')->title(__('Taksiran'))->data('credits_agunan_bpkb_taksiran'),
            // Column::make('acct_credits_agunan.credits_agunan_bpkb_gross')->title(__('Uang Muka Gross'))->data('credits_agunan_bpkb_gross'),
            // Column::make('acct_credits_agunan.credits_agunan_atmjamsostek_nomor')->title(__('No ATM / Jamsostek'))->data('credits_agunan_atmjamsostek_nomor'),
            // Column::make('acct_credits_agunan.credits_agunan_atmjamsostek_nama')->title(__('Atas Nama'))->data('credits_agunan_atmjamsostek_nama'),
            // Column::make('acct_credits_agunan.credits_agunan_atmjamsostek_bank')->title(__('Nama Bank'))->data('credits_agunan_atmjamsostek_bank'),
            // Column::make('acct_credits_agunan.credits_agunan_atmjamsostek_taksiran')->title(__('Taksiran'))->data('credits_agunan_atmjamsostek_taksiran'),
            // Column::computed('agunan_remark')->title(__('Keterangan')),
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
        return 'Master_Data_Agunan_' . date('YmdHis');
    }
}
