<x-base-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Rekening Diblockir</h3>
            <div class="card-toolbar">
                <a type="button" href="{{ route('savings-account-blockir.add') }}"  class="btn btn-sm btn-light-primary">
                    {!! theme()->getSvgIcon("icons/duotune/general/gen035.svg", "svg-icon-2x me-1") !!}
                    {{ __('Tambah Blockir Rekening Baru') }}
                </a>
            </div>
        </div>
        <div class="card-body pt-6">
            @include('content.AcctSavingsAccountBlockir.List._table')
        </div>
    </div>
</x-base-layout>