@inject('CoreMember','App\Http\Controllers\CoreMemberController')
@inject('AcctSavings','App\Http\Controllers\AcctSavingsController')
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript">
</script>
<script>
     $(window).on('load', function() {
        $('#exampleModal').modal('show');
    });
</script>
@php
    use Carbon\Carbon;
    use App\Models\AcctDepositoAccount;

    //tgl hari ini
    $today = Carbon::today()->format('m-d-Y');
    // $today = ' 2023-11-28';

    //jatuh tempo simp berjangka 
    $depositoAccount = AcctDepositoAccount::select('*')
        ->where('deposito_account_due_date', '<', $today)
        ->get();

    $depositoAccountCount = count($depositoAccount);
@endphp
<x-base-layout>
    <!-- Modal Notifikasi -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Notifikasi</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold">Tanggal Hari ini 
                         <span class="badge bg-primary">{{ $today }}</span>
                    </p>
                    <?php if($depositoAccountCount == 0){ ?>
                        <p class="fw-bold">Hari Ini Tidak Ada Basil Simpanan Berjangka yang Jatuh Tempo</p> 
                    <?php }else{ ?>
                        Daftar Jatuh Tempo Simpanan Berjangka  
                        <table class="table table-border g-3 show-border">
                            <thead>
                            <tr class="text-dark fw-bold">
                                <th>No.Simpanan</th>
                                <th>Anggota</th>
                                <th>Jenis</th>
                                <th>No.Seri</th>
                                <th>Jatuh Tempo</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($depositoAccount as $item)
                                <tr>
                                    <td>{{ $item->deposito_account_no }}</td>
                                    <td>{{ $CoreMember->getMemberName($item->member_id) }}</td>
                                    <td>{{ $AcctSavings->getSavingsName($item->savings_account_id) }}</td>
                                    <td>{{ $item->deposito_account_serial_no }}</td>
                                    <td>{{ $item->deposito_account_due_date }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--begin::Row-->
    <div class="row gy-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xxl-4">
            {{ theme()->getView('partials/widgets/mixed/_widget-2', ['class' => 'card-xxl-stretch', 'chartColor' => 'danger', 'chartHeight' => '200px']) }}
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        {{-- <div class="col-xxl-4">
            {{ theme()->getView('partials/widgets/lists/_widget-5', array('class' => 'card-xxl-stretch')) }}
        </div> --}}
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-8">
            {{ theme()->getView('partials/widgets/charts/_widget-1', ['class' => 'card-xxl-stretch-50 mb-5 mb-xl-8', 'chartColor' => 'primary', 'chartHeight' => '175px']) }}

            {{ theme()->getView('partials/widgets/charts/_widget-2', ['class' => 'card-xxl-stretch-50 mb-5 mb-xl-8', 'chartColor' => 'primary', 'chartHeight' => '175px']) }}
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    {{-- <!--begin::Row-->
    <div class="row gy-5 gx-xl-8">
        <!--begin::Col-->
        <div class="col-xxl-4">
            {{ theme()->getView('partials/widgets/lists/_widget-3', array('class' => 'card-xxl-stretch mb-xl-3')) }}
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xl-8">
            {{ theme()->getView('partials/widgets/tables/_widget-9', array('class' => 'card-xxl-stretch mb-5 mb-xl-8')) }}
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row--> --}}

    <!--begin::Row-->
    <div class="row gy-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xl-6">
            {{ theme()->getView('partials/widgets/lists/_widget-2', ['class' => 'card-xl-stretch mb-xl-8']) }}
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xl-6">
            {{ theme()->getView('partials/widgets/lists/_widget-6', ['class' => 'card-xl-stretch mb-xl-8']) }}
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        {{-- <div class="col-xl-4">
            {{ theme()->getView('partials/widgets/lists/_widget-4', array('class' => 'card-xl-stretch mb-5 mb-xl-8', 'items' => '5')) }}
        </div> --}}
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    {{-- <div class="row g-5 gx-xxl-8">
        <!--begin::Col-->
        <div class="col-xxl-4">
            {{ theme()->getView('partials/widgets/mixed/_widget-5', array('class' => 'card-xxl-stretch mb-xl-3', 'chartColor' => 'success', 'chartHeight' => '150px')) }}
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xxl-8">
            {{ theme()->getView('partials/widgets/tables/_widget-5', array('class' => 'card-xxl-stretch mb-5 mb-xxl-8')) }}
        </div>
        <!--end::Col-->
    </div> --}}
    <!--end::Row-->

</x-base-layout>
