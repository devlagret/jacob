<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Laporan Nominatif Simpanan Anggota') }}</h3>
            </div>
        </div>

        <div id="kt_nominative_member_view">
            <form id="kt_nominative_member_view_form" class="form" method="POST" action="{{ route('nominative-member-report.viewport') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <div class="col-lg-6 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Anggota') }}</label>
                            <select name="member_character" id="member_character" aria-label="{{ __('Jenis Anggota') }}" data-control="select2" data-placeholder="{{ __('Pilih jenis anggota..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih jenis anggota..') }}</option>
                                @foreach($membercharacter as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_character', '' ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Cabang') }}</label>
                            <select name="branch_id" id="branch_id" aria-label="{{ __('Cabang') }}" data-control="select2" data-placeholder="{{ __('Pilih cabang..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih cabang..') }}</option>
                                @foreach($corebranch as $key => $value)
                                    <option data-kt-flag="{{ $value['branch_id'] }}" value="{{ $value['branch_id'] }}" {{ $value['branch_id'] === old('branch_id', '' ?? '') ? 'selected' :'' }}>{{ $value['branch_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="submit" class="btn btn-primary me-2" id="kt_nominative_member_submit" id="view" name="view" value="excel">
                        <i class="bi bi-file-earmark-excel"></i> {{__('Export Excel')}}
                    </button>
                    <button type="submit" class="btn btn-primary" id="kt_nominative_member_submit" id="view" name="view" value="pdf">
                        <i class="bi bi-file-earmark-pdf"></i> {{__('Export PDF')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>