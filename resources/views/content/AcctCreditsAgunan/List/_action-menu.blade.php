<td class="text-end">
    @if($model->credits_agunan_status == 0)
    <a href="{{ route('credits-agunan.update-status', $model->credits_agunan_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
        Update
    </a>
    @endif
    <a href="{{ route('credits-agunan.print-receipt', $model->credits_agunan_id) }}" class="btn btn-sm btn-info btn-active-light-info">
        Tanda Terima
    </a>
</td>