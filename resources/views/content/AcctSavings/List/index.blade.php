<x-base-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Kode Tabungan</h3>
            <div class="card-toolbar">
                <a type="button" href="{{ route('savings.add') }}"  class="btn btn-sm btn-light-primary">
                    {!! theme()->getSvgIcon("icons/duotune/general/gen035.svg", "svg-icon-2x me-1") !!}
                    {{ __('Tambah Kode Tabungan Baru') }}
                </a>
            </div>
        </div>
        <div class="card-body pt-6">
            @include('content.AcctSavings.List._table')
        </div>
    </div>
</x-base-layout>