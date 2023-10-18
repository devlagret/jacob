<?php 
if (empty($sessiondata)){
    $sessiondata['start_date']  = date('d-m-Y');
    $sessiondata['end_date']    = date('d-m-Y');
    $sessiondata['branch_id']   = auth()->user()->branch_id;
}
?>
<x-base-layout>
    <div class="card">
        <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#kt_card_collapsible">
            <h3 class="card-title">Filter</h3>
            <div class="card-toolbar rotate-180">
                <span class="bi bi-chevron-up fs-2">
                </span>
            </div>
        </div>
        <form id="kt_filter_deposito_profit_sharing_form" class="form" method="POST" action="{{ route('deposito-profit-sharing.filter') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div id="kt_card_collapsible" class="collapse">
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Mulai') }}</label>
                            <input name="start_date" id="start_date" class="date form-control form-control-solid form-select-lg" value="{{ $sessiondata['start_date'] }}" placeholder="Pilih tanggal"/>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Akhir') }}</label>
                            <input name="end_date" id="end_date" class="date form-control form-control-solid form-select-lg" value="{{ $sessiondata['end_date'] }}" placeholder="Pilih tanggal"/>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Cabang') }}</label>
                            <select name="branch_id" id="branch_id" aria-label="{{ __('Cabang') }}" data-control="select2" data-placeholder="{{ __('Pilih cabang..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih cabang..') }}</option>
                                @foreach($corebranch as $key => $value)
                                    <option data-kt-flag="{{ $value['branch_id'] }}" value="{{ $value['branch_id'] }}" {{ $value['branch_id'] === old('branch_id', $sessiondata['branch_id'] ?? '') ? 'selected' :'' }}>{{ $value['branch_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('deposito-profit-sharing.filter-reset') }}" class="btn btn-danger me-2" id="kt_filter_cancel">
                        {{__('Batal')}}
                    </a>
                    <button type="submit" class="btn btn-success" id="kt_filter_search">
                        {{__('Cari')}}
                    </button>
                </div>
            </div>
        </form>
    </div>
    <br>
    <br>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Bunga Simpanan Berjangka</h3>
            <div class="card-toolbar">
            </div>
        </div>
        <div class="card-body pt-6">
            @include('content.AcctDepositoProfitSharing.List._table')
        </div>
    </div>
</x-base-layout>