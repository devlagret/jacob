<?php

namespace App\DataTables;

use App\Models\AcctCreditsAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
            ->editColumn('member.member_gender', function (AcctCreditsAccount $model) {

                if($model->member_gender == 0)
                {
                    return 'Perempuan';
                }else{
                    return 'Laki-laki';
                }               
            })
            ->editColumn('member_working_type', function (AcctCreditsAccount $model) {
                return ($model->member_working_type == 0 ?'': ($model->member_working_type == 1?'Karyawan': ($model->member_working_type == 2 ? 'Profesional':'Non Karyawan')));
            });
            // ->editColumn('savingacc.savings_account_no', function (AcctCreditsAccount $model) {
            //     if($model->savingacc->savings_account_no == null)
            //     {
            //         return ' - ' ;
            //     }else{
            //         return $model->savingacc->savings_account_no;
            //     }
            // });
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

        $querydata = $model->newQuery()->with('member.working','savingacc','credit')
		->where('credits_account_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
		->where('credits_account_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->whereHas('member',function (Builder $query) use($sessiondata) {
            $query->where('branch_id',  $sessiondata['branch_id']??Auth::user()->branch_id);
        });
        if($sessiondata['credits_id']){
            $querydata = $querydata
            ->with('member.working','savingacc','credit')
            ->where('credits_id', $sessiondata['credits_id']);
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
            Column::make('credits_account_id')->title(__('No'))->data('DT_RowIndex'),
            Column::make('credits_account_serial')->title(__('Nomor Akad')),
            // Column::make('savingacc.savings_account_no')->title(__('No Rekening')),
            Column::make('member.member_name')->title(__('Nama Anggota')),
            Column::make('member.member_gender')->title(__('JNS Kel')),
            Column::make('member.member_address')->title(__('Alamat')),
            Column::make('member.working.member_working_type')->title(__('Pekerjaan')),
            Column::make('member.working.member_company_name')->title(__('Perusahaan')),
            Column::make('member.member_identity_no')->title(__('No Identitas')),
            Column::make('credit.credits_name')->title(__('Jenis Pinjaman')),
            Column::make('credits_account_period')->title(__('Jangka Waktu')),
            Column::make('credits_account_date')->title(__('Tanggal Pinjam')),
            Column::make('credits_account_due_date')->title(__('Tanggal Jatuh Tempo')),
            Column::make('credits_account_due_date')->title(__('JML Plasfon')),
            Column::make('credits_account_amount')->title(__('Pokok')),
            Column::make('credits_account_interest')->title(__('Bunga')),
            Column::make('credits_account_principal_amount')->title(__('Angsuran Pokok')),
            Column::make('credits_account_interest_amount')->title(__('Angsuran Bunga')),
            Column::make('credits_account_last_balance')->title(__('Saldo Pokok')),
            
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
