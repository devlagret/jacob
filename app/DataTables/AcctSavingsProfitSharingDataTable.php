<?php

namespace App\DataTables;

use App\Models\AcctSavingsProfitSharingTemp;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctSavingsProfitSharingDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('savings_profit_sharing_temp_period', function (AcctSavingsProfitSharingTemp $model) {
                $months = Configuration::Month();
                $month  = $months[substr($model->savings_profit_sharing_temp_period, 0, 2)];
                $year   = substr($model->savings_profit_sharing_temp_period, 2);
                $period = $month.' '.$year;

                return $period;
            })
            ->editColumn('savings_account_last_balance', function (AcctSavingsProfitSharingTemp $model) {
                return number_format($model->savings_account_last_balance, 2);
            })
            ->editColumn('savings_tax_temp_amount', function (AcctSavingsProfitSharingTemp $model) {
                return number_format($model->savings_tax_temp_amount, 2);
            })
            ->editColumn('savings_profit_sharing_temp_amount', function (AcctSavingsProfitSharingTemp $model) {
                return number_format($model->savings_profit_sharing_temp_amount, 2);
            });
    }

    public function query(AcctSavingsProfitSharingTemp $model)
    {
        return $model->newQuery()
        ->select('acct_savings_profit_sharing_temp.*', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_account_no')
        ->join('core_member', 'core_member.member_id', '=', 'acct_savings_profit_sharing_temp.member_id')
        ->join('acct_savings_account', 'acct_savings_account.savings_account_id', '=', 'acct_savings_profit_sharing_temp.savings_account_id')
        ->where('acct_savings_profit_sharing_temp.branch_id', auth()->user()->branch_id);
    }

    public function html()
    {
        return $this->builder()
                    ->setTableId('savings-profit-sharing-table')
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
            Column::make('acct_savings_profit_sharing_temp.savings_profit_sharing_temp_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('acct_savings_profit_sharing_temp.savings_profit_sharing_temp_period')->title(__('Periode Bunga'))->data('savings_profit_sharing_temp_period'),
            Column::make('acct_savings_account.savings_account_no')->title(__('No. Rekening'))->data('savings_account_no'),
            Column::make('core_member.member_name')->title(__('Nama'))->data('member_name'),
            Column::make('core_member.member_address')->title(__('Alamat'))->data('member_address'),
            Column::make('acct_savings_profit_sharing_temp.savings_account_last_balance')->title(__('Saldo'))->data('savings_account_last_balance'),
            Column::make('acct_savings_profit_sharing_temp.savings_tax_temp_amount')->title(__('Pajak'))->data('savings_tax_temp_amount'),
            Column::make('acct_savings_profit_sharing_temp.savings_profit_sharing_temp_amount')->title(__('Bunga'))->data('savings_profit_sharing_temp_amount'),
        ];
    }

    protected function filename()
    {
        return 'SourceFund_' . date('YmdHis');
    }
}
