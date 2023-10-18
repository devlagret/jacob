@section('script')
<script>
	$(document).ready(function(){
        $("#process_pickup_submit").click(function(){
			var pickup_remark = $("#pickup_remark").val();
			
		  	if(pickup_remark!=''){
				return true;
			}else{
				alert('Isikan Keterangan');
				return false;
			}
		});
    });
</script>
@endsection
<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Proses Pickup') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('nomv-sv-pickup.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="process_pickup">
           
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Pickup') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="date" name="savings_cash_mutation_date" readonly class="form-control readonly form-control-lg form-control-solid" placeholder="Tgl Pickup" value="{{ $data->savings_cash_mutation_date }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Transaksi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="mutation_name" readonly class="form-control readonly form-control-lg form-control-solid" placeholder="Transaksi" value="{{  $data->mutation->mutation_name }}" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" readonly class="form-control readonly form-control-lg form-control-solid" placeholder="Nama Panggilan" value="{{  $data->member->member_name }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_cash_mutation_amount" readonly class="form-control readonly form-control-lg form-control-solid" placeholder="Nama Perusahaan" value="{{  number_format($data->savings_cash_mutation_amount,2,',','.') }}" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a type="button" href="{{route('nomv-sv-pickup.index')}}" class="btn btn-white btn-active-light-primary me-2 ">{{ __('Batal') }}</a>

                    <button type="button" class="btn btn-primary" id="process_pickup_submit" data-bs-toggle="modal" data-bs-target="#pickup-modal">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" id="pickup-modal" aria-hidden="true" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Transaksi</h3>
    
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="bi bi-x-lg"></span>
                    </div>
                    <!--end::Close-->
                </div>
    
                <div class="modal-body py-0" id="modal-body">
                    <form id="process_pickup_form" class="form" method="POST" action="{{ route('nomv-sv-pickup.process-add') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            {{-- <div class="col fv-row"> --}}
                                <label class=" fw-bold fs-6 required">{{ __('Keterangan') }}</label>
                                <input type="hidden" name="savings_cash_mutation_id" readonly class="readonly" value="{{ $data['savings_cash_mutation_id'] }}" autocomplete="off" />
                                <textarea type="text" rows="3" cols="40" name="pickup_remark" required class="required form-control form-control-lg form-control-solid" placeholder="Masukan Keterangan..." autocomplete="off" ></textarea>
                            {{-- </div> --}}
                        </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="process_pickup_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </form>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>

