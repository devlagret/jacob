<?php 
    use \App\Http\Controllers\CoreMemberController;

    $detail_menu_status 	= CoreMemberController::getMenuMapping('member/detail');
    $edit_menu_status 		= CoreMemberController::getMenuMapping('member/edit');
    $delete_menu_status 	= CoreMemberController::getMenuMapping('member/delete');
    $activate_status 		= CoreMemberController::getMenuMapping('member/activate');
?>

<td class="text-end">
    <?php if($detail_menu_status == 1) { ?>
        <a href="{{ route('member.detail', $model->member_id) }}" class="btn btn-sm btn-warning btn-active-light-warning">
            Detail
        </a>
    <?php } if($edit_menu_status == 1) { ?>
        <a href="{{ route('member.edit', $model->member_id) }}" class="btn btn-sm btn-info btn-active-light-info">
            Ubah
        </a>
    <?php } if($delete_menu_status == 1) { ?>
        <a type="button" data-bs-toggle="modal" data-bs-target="#kt_modal_delete_{{$model->member_id}}" class="btn btn-sm btn-danger btn-active-light-danger">
            Hapus
        </a>
    <?php } if($activate_status == 1) {
        if($model->member_active_status == 1){
    ?>
            <a href="{{ route('member.activate', $model->member_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
                Aktifkan
            </a>
    <?php }else{ ?>
            <a href="{{ route('member.non-activate', $model->member_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
                Non Aktifkan
            </a>
    <?php } 
    }?>
</td>


<div class="modal fade" tabindex="-1" id="kt_modal_delete_{{$model->member_id}}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Hapus Anggota</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="bi bi-x-lg"></span>
                </div>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin ingin menghapus Anggota?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tidak</button>
                <a href="{{ route('member.delete', $model->member_id) }}" class="btn btn-primary">Iya</a>
            </div>
        </div>
    </div>
</div>