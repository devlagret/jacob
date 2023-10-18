<?php

namespace App\DataTables;

use App\Models\CoreMember;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class CoreMemberPrintBookDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('member_status', function (CoreMember $model) {
                $memberstatus = Configuration::MemberStatus();

                return $memberstatus[$model->member_status];
            })
            ->addColumn('action', 'content.CoreMemberPrintBook.List._action-menu');
    }

    public function query(CoreMember $model)
    {
        return $model->newQuery()
        ->where('data_state', 0);
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('member-print-book-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->stateSave(true)
                    ->orderBy(0, 'asc')
                    ->responsive()
                    ->autoWidth(false)
                    ->parameters(['scrollX' => true])
                    ->addTableClass('align-middle table-row-dashed fs-6 gy-5');
    }

    protected function getColumns()
    {
        return [
            Column::make('member_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('member_no')->title(__('No Anggota')),
            Column::make('member_name')->title(__('Nama')),
            Column::make('member_address')->title(__('Alamat')),
            Column::make('member_status')->title(__('Status')),
            Column::make('member_phone')->title(__('No Telp')),
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
        return 'Cetak_Buku_Anggota_' . date('YmdHis');
    }
}
