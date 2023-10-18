<x-base-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Kode Business Office (BO)</h3>
            <div class="card-toolbar">
                <a type="button" href="{{ route('office.add') }}"  class="btn btn-sm btn-light-primary">
                    {!! theme()->getSvgIcon("icons/duotune/general/gen035.svg", "svg-icon-2x me-1") !!}
                    {{ __('Tambah Kode Business Office (BO) Baru') }}
                </a>
            </div>
        </div>
        <div class="card-body pt-6">
            @include('content.CoreOffice.List._table')
        </div>
    </div>
</x-base-layout>