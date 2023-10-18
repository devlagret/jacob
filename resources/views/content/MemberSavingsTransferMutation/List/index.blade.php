<x-base-layout>
    <script>
        var message    = <?php echo json_encode(empty(session('message')) ? '' : session('message')) ?>;

        if (message.alert == 'success') {
            window.open("{{ url('member-savings-transfer-mutation/print-validation') }}"+"/"+message.data,'_blank');
        }
    </script>
    <div class="card">
        <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#kt_card_collapsible">
            <h3 class="card-title">Filter</h3>
            <div class="card-toolbar rotate-180">
                <span class="bi bi-chevron-up fs-2">
                </span>
            </div>
        </div>
        <form id="kt_filter_savings_account_form" class="form" method="POST" action="{{ route('member-savings-transfer-mutation.filter') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div id="kt_card_collapsible" class="collapse">
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Awal') }}</label>
                            <input name="start_date" class="date form-control form-control-lg form-control-solid" placeholder="Tanggal" autocomplete="off" value="{{ old('start_date', $sessiondata['start_date'] ?? '') }}"/>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Akhir') }}</label>
                            <input name="end_date" class="date form-control form-control-lg form-control-solid" placeholder="Tanggal" autocomplete="off" value="{{ old('end_date', $sessiondata['end_date'] ?? '') }}"/>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Anggota') }}</label>
                            <select name="member_id" id="member_id" data-control="select2" data-placeholder="{{ __('Pilih Anggota') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($coremember as $key => $value)
                                    <option data-kt-flag="{{ $value['member_id'] }}" value="{{ $value['member_id'] }}" {{ $value['member_id'] == old('member_id', $sessiondata['member_id'] ?? '') ? 'selected' :'' }}>{{ $value['member_no'] }} - {{ $value['member_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('member-savings-transfer-mutation.filter-reset') }}" class="btn btn-danger me-2" id="kt_filter_cancel">
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
            <h3 class="card-title">Daftar Mutasi Debit Simpanan Wajib</h3>
            <div class="card-toolbar">
                <a type="button" href="{{ route('member-savings-transfer-mutation.add') }}"  class="btn btn-sm btn-light-primary">
                    {!! theme()->getSvgIcon("icons/duotune/general/gen035.svg", "svg-icon-2x me-1") !!}
                    {{ __('Tambah Mutasi Debit Simpanan Wajib Baru') }}
                </a>
            </div>
        </div>
        <div class="card-body pt-6">
            @include('content.MemberSavingsTransferMutation.List._table')
        </div>
    </div>
</x-base-layout>