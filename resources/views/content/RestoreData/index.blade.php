<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Restore Data') }}</h3>
            </div>
        </div>

        <div id="kt_restore_data_view" class="card-body">
            <div class="row ">
            @foreach ($table as $key => $val)
            <div class="col-md-3 col-sm-6 col-lg-2 shadow shadow-lg m-2 py-1 rounded border ">
                    <a class="text-body"href="{{route('restore.table',$key)}}">
                    <div class="text-black row bg-body">
                        <span class="col-auto fs-1"><i class="fa fa-light fa-table fa-lg"></i></span>
                        <div class="info-box-content col">
                            <span class="text-dark row">Tabel '{{$key}}'</span>
                            <span class="info-box-text row d-inline">Data Dihapus : <b class="d-inline"> {{$val}} </b></span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        </div>
    </div>
</x-base-layout>