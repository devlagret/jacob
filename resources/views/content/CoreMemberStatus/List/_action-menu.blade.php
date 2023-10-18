<td class="text-end">
    <?php if($model->member_status == 0) { ?>
        <a href="{{ route('member-status.update-status', $model->member_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
            Update
        </a>
    <?php } ?>
</td>