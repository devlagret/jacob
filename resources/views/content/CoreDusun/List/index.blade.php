<x-base-layout>
    @section('bladeScripts')
    <script>
        var message    =  "{{json_encode(empty(session('message')) ? '' : session('message'))}}";
        function changeKecamatan() {  }
        if (message.alert == 'success') {
            window.open("{{ url('member-savings-transfer-mutation/print-validation') }}"+"/"+message.data,'_blank');
        }
    $(document).ready(function(){
        $('select').on('change', function() {
        alert( this.value );
        });
     });
    </script>
    @endsection
    <div class="card">
        <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#kt_card_collapsible">
            <h3 class="card-title">Filter</h3>
            <div class="card-toolbar rotate-180">
                <span class="bi bi-chevron-up fs-2">
                </span>
            </div>
        </div>
        <form id="kt_filter_core_dusun_form" class="form" method="POST" action="{{ route('dusun.filter') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div id="kt_card_collapsible" class="collapse">
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-4 col-md-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kabupaten') }}</label>
                            <select name="city_id" id="city_id" aria-label="{{ __('Kabupaten') }}" data-control="select2" data-placeholder="{{ __('Pilih Kabupaten..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih Kabupaten..') }}</option>
                                @foreach($corekabupaten as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('city_id', '' ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kecamatan') }}</label>
                            <select name="kecamatan_id" id="kecamatan_id" aria-label="{{ __('Kecamatan') }}" data-control="select2" data-placeholder="{{ __('Pilih Kecamatan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kelurahan') }}</label>
                            <select name="kelurahan_id" id="kelurahan_id" aria-label="{{ __('Kelurahan') }}" data-control="select2" data-placeholder="{{ __('Pilih Kelurahan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('dusun.filter-reset') }}" class="btn btn-danger me-2" id="kt_filter_cancel">
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
            <h3 class="card-title">Daftar Pickup </h3>
            <div class="card-toolbar">
                <a type="button" href="{{ route('dusun.add') }}"  class="btn btn-sm btn-light-primary">
                    {!! theme()->getSvgIcon("icons/duotune/general/gen035.svg", "svg-icon-2x me-1") !!}
                    {{ __('Tambah Dusun Baru') }}
                </a>
            </div>
        </div>
        <div class="card-body pt-6">
            @include('content.CoreDusun.List._table')
        </div>
    </div>
</x-base-layout>