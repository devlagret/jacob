<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Hasil Perhitungan Bunga Tabungan') }}</h3>
            </div>
        </div>

        <div id="kt_savings_profit_sharing_data_view">
            <div class="card-body border-top p-9">
                <div class="row mb-6">
                    <div class="col-lg-12 fv-row">
                        @include('content.AcctSavingsProfitSharing.List._table')
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <a href="{{ route('savings-profit-sharing.index') }}" class="btn btn-danger me-2" id="kt_savings_profit_sharing_data_recalculate" name="kt_savings_profit_sharing_data_recalculate">
                    <i class="bi bi-arrow-repeat fs-2x"></i> {{__('Hitung Ulang')}}
                </a>
                <button type="submit" data-bs-toggle="modal" data-bs-target="#kt_modal_confirm" class="btn btn-primary" id="kt_savings_profit_sharing_data_submit" name="kt_savings_profit_sharing_data_submit" >
                    <i class="bi bi-check fs-2x"></i> {{__('Proses')}}
                </button>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" id="kt_modal_confirm">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Proses Bunga</h3>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <span class="svg-icon svg-icon-1"></span>
                        </div>
                    </div>
                    <div class="modal-body">
                        <p>Apakah anda yakin akan memproses bunga ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tidak</button>
                        <a href="{{ route('savings-profit-sharing.process-update') }}" class="btn btn-primary">Iya</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>