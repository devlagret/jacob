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
        <div class="card-header">
            <h3 class="card-title">Restore Data '{{session('restore-table')}}' </h3>
            <div class="card-toolbar">
                <a href="{{ route('restore.index') }}" class="btn btn-light align-self-center">
                    {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                    {{ __('Kembali') }}</a>
            </div>
        </div>
        <div class="card-body pt-6">
            <div class="table-responsive">
            @include('content.RestoreData.Table._table')
        </div>
        </div>
    </div>
</x-base-layout>