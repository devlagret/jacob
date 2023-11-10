@php
    $approve = App\Http\Controllers\AcctCreditsAccountController::getApproveStatus($model->credits_approve_status);
@endphp

@if ($model->credits_approve_status == 0 && auth()->user()->user_group_id == 5)
    <td class="text-end">
        <a type="button" href="{{ route('credits-account.approving', $model->credits_account_id) }}" class="btn btn-sm btn-success btn-active-light-success mb-1">
            <span class="bi bi-check"></span>
            Proses
        </a>
        <a type="button" href="{{ route('credits-account.reject', $model->credits_account_id) }}" class="btn btn-sm btn-danger btn-active-light-danger">
            <span class="bi bi-x"></span>
            Reject
        </a>
    </td>
@elseif ($model->credits_approve_status == 0 && auth()->user()->user_group_id == 6)
    <td class="text-end">
        <a type="button" href="{{ route('credits-account.approving', $model->credits_account_id) }}" class="btn btn-sm btn-success btn-active-light-success mb-1">
            <span class="bi bi-check"></span>
            Proses
        </a>
        <a type="button" href="{{ route('credits-account.reject', $model->credits_account_id) }}" class="btn btn-sm btn-danger btn-active-light-danger">
            <span class="bi bi-x"></span>
            Reject
        </a>
    </td>
@else
    <div>{{ $approve }}</div>
@endif
