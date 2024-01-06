@if (!$model->pickup_status)
    <td class="text-center">
        <a type="button" href="{{ route('nomv-sv-pickup.add',['type' => $model->type,'id' => $model->id ]) }}" class="btn btn-sm btn-success btn-active-light-success">
            Proses
        </a>
    </td>
@else
    <td class="text-center">
        Telah Disetorkan
    </td>
@endif