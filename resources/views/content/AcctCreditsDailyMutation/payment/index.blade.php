@section('bladeScripts')
<script>
function changeAo() { 
    branch_id = $('#branch_id').val();
    $.ajax({
        type: "get",
        url: "{{route('crd-daily-mutation.get-ao')}}",
        data: {
            'branch_id':branch_id,
        },
        dataType: "html",
        success: function (response) {
            $('#office_id').html(response);
        }
    });
 }
$(document).ready(function () {
    if($('#branch_id').val()!=''){
        changeAo();
    }
});
</script>
@stop
<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Mutasi Harian Angsuran Pembiayaan') }}</h3>
            </div>
        </div>

        <div id="kt_deposito_report_view">
            <form id="kt_deposito_report_view_form" class="form" method="POST" action="{{ route('crd-daily-mutation.p-viewport') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                       <div class="row">
                        <div class="col-lg-6 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Mulai Tanggal') }}</label>
                            <input name="start_date" id="start_date" class="date form-control form-control-solid form-select-lg" value="{{old('start_date')}}" placeholder="Pilih tanggal"/>
                        </div>
                        <div class="col-lg-6 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Sampai Tanggal') }}</label>
                            <input name="end_date" id="end_date" class="date form-control form-control-solid form-select-lg" value="{{old('end_date')}}" placeholder="Pilih tanggal"/>
                        </div>
                       </div>
                       <div class="row">
                        <div class="col-lg-6 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Cabang') }}</label>
                            <select name="branch_id" id="branch_id" aria-label="{{ __('Cabang') }}" data-control="select2" data-placeholder="{{ __('Pilih cabang..') }}" onchange="changeAo()" data-allow-clear="true"  class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih cabang..') }}</option>
                                @foreach($corebranch as $key => $value)
                                    <option data-kt-flag="{{ $value['branch_id'] }}" value="{{ $value['branch_id'] }}" {{ $corebranch->count() == 1 ? 'selected' :'' }}>{{ $value['branch_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('AO') }}</label>
                            <select name="office_id" id="office_id" aria-label="{{ __('AO') }}" data-control="select2" data-placeholder="{{ __('Pilih AO..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih AO..') }}</option>
                            </select>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="submit" class="btn btn-primary me-2" id="kt_nominative_deposito_submit" id="view" name="view" value="excel">
                        <i class="bi bi-file-earmark-excel"></i> {{__('Export Excel')}}
                    </button>
                    <button type="submit" class="btn btn-primary" id="kt_deposito_report_submit" id="view" name="view" value="pdf">
                        <i class="bi bi-file-earmark-pdf"></i> {{__('Export PDF')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>