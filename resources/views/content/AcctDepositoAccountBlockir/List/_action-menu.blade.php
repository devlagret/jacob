<td class="text-end">
    <a type="button" data-bs-toggle="modal" data-bs-target="#kt_modal_delete_{{$model->deposito_account_blockir_id}}" class="btn btn-sm btn-danger btn-active-light-danger">
        UnBlockir
    </a>
</td>

<div class="modal fade" tabindex="-1" id="kt_modal_delete_{{$model->deposito_account_blockir_id}}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">UnBlockir Rekening</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="bi bi-x-lg"></span>
                </div>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tidak</button>
                <a href="{{ route('deposito-account-blockir.add-unblockir',$model->deposito_account_blockir_id) }}" class="btn btn-primary">Iya</a>
            </div>
        </div>
    </div>
</div>