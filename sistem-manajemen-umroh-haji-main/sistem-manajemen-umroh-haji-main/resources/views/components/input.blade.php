@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'hint' => null,
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    @if($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}"
            {{ $attributes->except('class')->merge(['class' => 'w-full rounded-lg border-gray-300 focus:border-brand focus:ring-brand text-sm', 'rows' => 3]) }}
            @if($required) required @endif>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            value="{{ $type === 'file' ? '' : old($name, $value) }}"
            {{ $attributes->except('class')->merge(['class' => 'w-full rounded-lg border-gray-300 focus:border-brand focus:ring-brand text-sm']) }}
            @if($required) required @endif>
    @endif

    @if($hint)<p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>@endif
    @error($name)<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
</div>
