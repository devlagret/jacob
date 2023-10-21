    <td class="text-center">
        <a type="button" href="{{ route('restore.data',['table'=>session('restore-table'),'col'=>$pk,'id'=>$model->$pk]) }}" class="btn btn-sm btn-success btn-active-light-success">
            Restore
        </a>
    </td>