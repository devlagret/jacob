@section('script')
    <script>
        $(document).ready(function() {
            $("#process_pickup_submit").click(function() {
                var pickup_remark = $("#pickup_remark").val();

                if (pickup_remark != '') {
                    return true;
                } else {
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
                {!! theme()->getSvgIcon('icons/duotune/arrows/arr079.svg', 'svg-icon-4 me-1') !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="process_pickup">
            <div class="card-body border-top p-9">
                <form id="process_pickup_form" class="form" method="POST"
                action="{{ route('nomv-sv-pickup.process-add') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-6">
                    <input class="form-control" type="text" hidden name="type" value="{{ $type }}">
                    <input class="form-control" type="text" hidden name="id" value="{{ $data->id }}">

                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Transaksi') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="date" name="tanggal" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Tgl Pickup" value="{{ $data->tanggal }}" autocomplete="off" />
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('NO Transaksi') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="no_transaksi" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Transaksi" value="{{ $data->no_transaksi }}" autocomplete="off" />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="anggota" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Panggilan" value="{{ $data->anggota }}" autocomplete="off" />
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="jumlah_view" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ number_format($data->jumlah, 2, ',', '.') }}"
                                    autocomplete="off" />
                                    <input type="text" name="jumlah" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ $data->jumlah }}"
                                    autocomplete="off" />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Keterangan') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text"  readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Panggilan" value="{{ $data->keterangan }}" autocomplete="off" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-6">
                    <div class="col-lg-6">
                        @if ($type == 2 || $type == 3)
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Adm') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="jumlah_2_view" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ number_format($data->jumlah2, 2, ',', '.') }}"
                                    autocomplete="off" />
                                    <input type="text" name="jumlah_2" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ $data->jumlah2 }}"
                                    autocomplete="off" hidden/>
                            </div>
                        </div>
                        @endif
                        @if ($type == 1)
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Pokok') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="jumlah_2_view" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ number_format($data->jumlah_2, 2, ',', '.') }}"
                                    autocomplete="off" />
                                    <input type="text" name="jumlah_2" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ $data->jumlah_2 }}"
                                    autocomplete="off" />
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Bunga ') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="jumlah_3_view" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ number_format($data->jumlah_3, 2, ',', '.') }}"
                                    autocomplete="off" />
                                    <input type="text" name="jumlah_3" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ $data->jumlah_3 }}"
                                    autocomplete="off" />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <label
                                class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Pendapatan Lain Lain') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="jumlah_3_view" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ number_format($data->jumlah_4, 2, ',', '.') }}"
                                    autocomplete="off" />
                                    <input type="text" name="jumlah_3" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ $data->jumlah_4 }}"
                                    autocomplete="off" />
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Denda') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="jumlah_4_view" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ number_format($data->jumlah_5, 2, ',', '.') }}"
                                    autocomplete="off" />
                                    <input type="text" name="jumlah_4" readonly
                                    class="form-control readonly form-control-lg form-control-solid"
                                    placeholder="Nama Perusahaan" value="{{ $data->jumlah_5 }}"
                                    autocomplete="off" />
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <a type="button" href="{{ route('nomv-sv-pickup.index') }}"
                    class="btn btn-white btn-active-light-primary me-2 ">{{ __('Batal') }}</a>

                <button class="btn btn-primary" type="submit">
                    @include('partials.general._button-indicator', ['label' => __('Simpan')])
                </button>
            </div>
        </form>
        </div>
    </div>


    <div class="modal fade" tabindex="-1" id="pickup-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Transaksi</h3>

                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                        aria-label="Close">
                        <span class="bi bi-x-lg"></span>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body py-0" id="modal-body">
                   
                        <div class="row">
                            {{-- <div class="col fv-row"> --}}
                            <label class=" fw-bold fs-6 required">{{ __('Keterangan') }}</label>
                            <input type="hidden" name="savings_cash_mutation_id" readonly class="readonly"
                                value="{{ $data['savings_cash_mutation_id'] }}" autocomplete="off" />
                            <textarea type="text" rows="3" cols="40" name="pickup_remark" required
                                class="required form-control form-control-lg form-control-solid" placeholder="Masukan Keterangan..."
                                autocomplete="off"></textarea>
                            {{-- </div> --}}
                        </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="process_pickup_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>
