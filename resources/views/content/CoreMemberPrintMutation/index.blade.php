@section('scripts')
<script>
$(document).ready(function(){
    $('#open_modal_button').click(function(){
        $.ajax({
            type: "GET",
            url : "{{route('member-print-mutation.modal-member')}}",
            success: function(msg){
                $('#kt_modal_core_member').modal('show');
                $('#modal-body').html(msg);
            }
        });
    });
});

function function_elements_add(name, value){
    $.ajax({
        type: "POST",
        url : "{{route('member-print-mutation.elements-add')}}",
        data : {
            'name'      : name, 
            'value'     : value,
            '_token'    : '{{csrf_token()}}'
        },
        success: function(msg){
        }
    });
}

function searchDate(){
    var start_date  = document.getElementById("start_date").value;
    var end_date    = document.getElementById("end_date").value;

    $.ajax({
        type: "POST",
        url : "{{route('member-print-mutation.change-date')}}",
        data : {
            'start_date'    : start_date, 
            'end_date'      : end_date,
            '_token'        : '{{csrf_token()}}'
        },
        success: function(msg){
            location.reload();
        }
    });
}
</script>
@endsection
<?php 
if (empty($sessiondata)){
    $sessiondata['coremember']  = null;
    $sessiondata['start_date']  = date('d-m-Y');
    $sessiondata['end_date']    = date('d-m-Y');
}
?>
<x-base-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cetak Mutasi Anggota</h3>
            <div class="card-toolbar">
            </div>
        </div>

        <div id="kt_member_mutation_view">
            <form id="kt_member_mutation_view_form" class="form" method="POST" action="{{ route('member-print-mutation.print') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Anggota') }}</label>
                                <div class="col-lg-5 fv-row">
                                    <input type="hidden" name="member_id" class="form-control form-control-lg form-control-solid" placeholder="No Anggota" value="{{ old('member_id', $coremember['member_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="text" name="member_no" class="form-control form-control-lg form-control-solid" placeholder="No Anggota" value="{{ old('member_no', $coremember['member_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                                <div class="col-lg-3 fv-row">
                                    <button type="button" id="open_modal_button" class="btn btn-primary">
                                        {{ __('Cari Anggota') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_name', $coremember['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" value="{{ old('member_address', $coremember['member_address'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Mulai') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="start_date" id="start_date" class="date form-control form-control-solid form-select-lg" placeholder="Pilih tanggal" value="{{ old('start_date', $sessiondata['start_date'] ?? '') }}" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Akhir') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="end_date" id="end_date" class="date form-control form-control-solid form-select-lg" placeholder="Pilih tanggal" value="{{ old('end_date', $sessiondata['end_date'] ?? '') }}" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <div class="col-lg-4">
                                </div>
                                <div class="col-lg-8">
                                    <a href="{{ route('member-print-mutation.reset') }}" class="btn btn-danger" id="kt_member_mutation_reset"  name="kt_member_mutation_reset" value="batal">
                                        <i class="bi bi-x fs-2"></i> {{__('Batal')}}
                                    </a>
                                    <a class="btn btn-success" id="kt_member_mutation_date" name="kt_member_mutation_date" value="cari" onclick="searchDate()">
                                        <i class="bi bi-search"></i> {{__('Cari')}}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <br>
                    <br>
                    <div class="row mb-6">
                        <div class="table-responsive">
                            <table class="table table-rounded border gy-7 gs-7 show-border">
                                <thead>
                                    <tr align="center">
                                        <th><b>No</b></th>
                                        <th><b>No Anggota</b></th>
                                        <th><b>Tgl Transaksi</b></th>
                                        <th><b>Sandi</b></th>
                                        <th><b>Simp Pokok</b></th>
                                        <th><b>Simp Wajib</b></th>
                                        <th><b>Simp Khusus</b></th>
                                        <th><b>Saldo</b></th>
                                        <th><b>Operator</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    if(count($acctsavingsmemberdetail)>0){
                                    ?> 
                                    @foreach($acctsavingsmemberdetail as $key => $val)
                                        <tr>
                                            <td style="text-align: center">{{ $no }}</td>
                                            <td>{{ $val['member_no'] }}</td>
                                            <td>{{ $val['transaction_date'] }}</td>
                                            <td>{{ $val['mutation_code'] }}</td>
                                            <td style="text-align: right">{{ number_format($val['principal_savings_amount'], 2) }}</td>
                                            <td style="text-align: right">{{ number_format($val['special_savings_amount'], 2) }}</td>
                                            <td style="text-align: right">{{ number_format($val['mandatory_savings_amount'], 2) }}</td>
                                            <td style="text-align: right">{{ number_format($val['last_balance'], 2) }}</td>
                                            <td>{{ $val['operated_name'] }}</td>
                                        </tr>
                                    <?php $no++ ?>
                                    @endforeach
                                    <?php }else{?>
                                        <tr>
                                            <td colspan="9" style="text-align: center">Data Kosong</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="submit" class="btn btn-primary me-2" id="kt_member_mutation_submit" id="view" name="view" value="preview">
                        {{__('Preview')}}
                    </button>
                    <button type="submit" class="btn btn-primary" id="kt_member_mutation_submit" id="view" name="view" value="print">
                        {{__('Cetak')}}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_modal_core_member">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Daftar Anggota</h3>
    
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="bi bi-x-lg"></span>
                    </div>
                </div>
    
                <div class="modal-body" id="modal-body">
                </div>
    
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>