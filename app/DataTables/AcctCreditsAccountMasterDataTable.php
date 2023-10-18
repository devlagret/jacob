<?php

namespace App\DataTables;

use App\Models\AcctCreditsAccount;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Configuration;

class AcctCreditsAccountMasterDataTable extends DataTable
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
            ->editColumn('credits_account_due_date', function (AcctCreditsAccount $model) {
                return date('d-m-Y', strtotime($model->credits_account_due_date));
            })
            ->editColumn('credits_account_amount', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_amount, 2);
            })
            ->editColumn('credits_account_interest', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_interest, 2);
            })
            ->editColumn('credits_account_principal_amount', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_principal_amount, 2);
            })
            ->editColumn('credits_account_interest_amount', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_interest_amount, 2);
            })
            ->editColumn('credits_account_last_balance', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_last_balance, 2);
            })
            ->editColumn('credits_account_provisi', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_provisi, 2);
            })
            ->editColumn('credits_account_komisi', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_komisi, 2);
            })
            ->editColumn('credits_account_insurance', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_insurance, 2);
            })
            ->editColumn('credits_account_adm_cost', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_adm_cost, 2);
            })
            ->editColumn('credits_account_materai', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_materai, 2);
            })
            ->editColumn('credits_account_risk_reserve', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_risk_reserve, 2);
            })
            ->editColumn('credits_account_stash', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_stash, 2);
            })
            ->editColumn('credits_account_principal', function (AcctCreditsAccount $model) {
                return number_format($model->credits_account_principal, 2);
            })
            ->editColumn('member_gender', function (AcctCreditsAccount $model) {
                return ($model->member_gender == 0 ?'Perempuan':'Laki-laki');
            })
            ->editColumn('member_working_type', function (AcctCreditsAccount $model) {
                return ($model->member_working_type == 0 ?'': ($model->member_working_type == 1?'Karyawan': ($model->member_working_type == 2 ? 'Profesional':'Non Karyawan')));
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AcctCreditsAccount/AcctCreditsAccountDataTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AcctCreditsAccount $model)
    {
        $sessiondata = session()->get('filter_creditsaccountmaster');
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

        $querydata = $model->newQuery()
        ->select('acct_credits_account.credits_account_serial', 'acct_credits_account.savings_account_id',  'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_address', 
        'core_member.member_gender', 'core_member.member_date_of_birth', 'core_member.member_job', 'core_member.member_identity', 'core_member_working.member_working_type', 'core_member_working.member_company_name' ,'core_member.member_identity_no', 
        'acct_credits_account.credits_account_period', 'acct_credits_account.credits_account_date', 'acct_credits_account.credits_account_due_date', 'acct_credits_account.credits_account_principal_amount', 
        'acct_credits_account.credits_account_interest_amount', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_interest', 'acct_credits_account.credits_account_last_balance',
        'acct_credits_account.credits_id', 'acct_credits.credits_name', 'acct_credits_account.credits_account_provisi', 'acct_credits_account.credits_account_komisi', 'acct_credits_account.credits_account_insurance',
         'acct_credits_account.credits_account_stash', 'acct_credits_account.credits_account_adm_cost', 'acct_credits_account.credits_account_materai', 'acct_credits_account.credits_account_risk_reserve', 
         'acct_savings_account.savings_account_no','acct_credits_account.credits_account_principal')
		->join('core_member', 'acct_credits_account.member_id', '=', 'core_member.member_id')
		->leftjoin('acct_savings_account', 'acct_credits_account.savings_account_id', '=', 'acct_savings_account.savings_account_id')
		->join('core_member_working', 'acct_credits_account.member_id', '=', 'core_member_working.member_id')
		->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
		->where('acct_credits_account.data_state', 0)
		->where('acct_credits_account.credits_account_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
		->where('acct_credits_account.credits_account_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('core_member.branch_id', $sessiondata['branch_id']);
        if($sessiondata['credits_id']){
            $querydata = $querydata->where('acct_credits_account.credits_id', $sessiondata['credits_id']);
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
                    ->setTableId('credits-account-master-table')
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
    $membergender = Configuration::MemberGender();
        return [
            Column::make('acct_credits_account.credits_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('acct_credits_account.credits_account_serial')->title(__('Nomor Akad'))->data('credits_account_serial'),
            Column::make('acct_savings_account.savings_account_no')->title(__('No Rekening'))->data('savings_account_no'),
            Column::make('core_member.member_name')->title(__('Nama Anggota'))->data('member_name'),
            Column::make('core_member.member_gender')->title(__('JNS Kel'))->data('member_gender'),
            Column::make('core_member.member_address')->title(__('Alamat'))->data('member_address'),
            Column::make('core_member.member_working_type')->title(__('Pekerjaan'))->data('member_working_type'),
            Column::make('core_member.member_company_name')->title(__('Perusahaan'))->data('member_company_name'),
            Column::make('core_member.member_identity_no')->title(__('No Identitas'))->data('member_identity_no'),
            Column::make('acct_credits.credits_name')->title(__('Jenis Pinjaman'))->data('credits_name'),
            Column::make('acct_credits_account.credits_account_period')->title(__('Jangka Waktu'))->data('credits_account_period'),
            Column::make('acct_credits_account.credits_account_date')->title(__('Tanggal Pinjam'))->data('credits_account_date'),
            Column::make('acct_credits_account.credits_account_due_date')->title(__('Tanggal Jatuh Tempo'))->data('credits_account_due_date'),
            Column::make('acct_credits_account.credits_account_due_date')->title(__('JML Plasfon'))->data('credits_account_due_date'),
            Column::make('acct_credits_account.credits_account_amount')->title(__('Pokok'))->data('credits_account_amount'),
            Column::make('acct_credits_account.credits_account_interest')->title(__('Bunga'))->data('credits_account_interest'),
            Column::make('acct_credits_account.credits_account_principal_amount')->title(__('Angsuran Pokok'))->data('credits_account_principal_amount'),
            Column::make('acct_credits_account.credits_account_interest_amount')->title(__('Angsuran Bunga'))->data('credits_account_interest_amount'),
            Column::make('acct_credits_account.credits_account_last_balance')->title(__('Saldo Pokok'))->data('credits_account_last_balance'),
            
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
