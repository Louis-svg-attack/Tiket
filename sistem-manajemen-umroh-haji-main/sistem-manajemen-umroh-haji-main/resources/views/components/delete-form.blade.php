@props([
    'action',
    'confirm' => 'Data yang dihapus tidak dapat dikembalikan.',
    'label' => 'Hapus',
    'icon' => true,
])

<form method="POST" action="{{ $action }}" data-confirm="{{ $confirm }}" class="inline">
    @csrf
    @method('DELETE')
    <button type="submit"
            {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-sm text-red-600 hover:text-red-700 font-medium']) }}>
        @if($icon)
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        @endif
        {{ $label }}
    </button>
</form>
