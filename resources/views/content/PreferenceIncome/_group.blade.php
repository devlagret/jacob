<td class="text-center">
    <select name="data[{{$model->income_id}}][income_group]" id="income_group_table_{{$model->income_id}}" aria-label="{{ __('Kelompok') }}" data-control="select2" data-placeholder="{{ __('Pilih Kelompok..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select-group" onchange="function_elements_add(this.name, this.value)">
        <option value="">{{ __('Pilih Kelompok...') }}</option>
        @foreach($kp as $key => $value)
        <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('income_group', $sessiondata['income_group'] ?? $model->income_group) ? 'selected' :'' }}>{{ $value }}</option>
        @endforeach
    </select>
</td>