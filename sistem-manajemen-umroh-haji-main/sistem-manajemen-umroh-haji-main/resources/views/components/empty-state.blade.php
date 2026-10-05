@props([
    'title' => 'Belum ada data',
    'message' => 'Data akan tampil di sini setelah ditambahkan.',
    'icon' => 'list',
])

<div class="flex flex-col items-center justify-center text-center py-12 px-4">
    <div class="h-14 w-14 rounded-full bg-gray-100 grid place-items-center text-gray-400 mb-3">
        <x-icon :name="$icon" class="w-7 h-7" />
    </div>
    <p class="font-medium text-gray-600">{{ $title }}</p>
    <p class="text-sm text-gray-400 mt-1 max-w-sm">{{ $message }}</p>
    @isset($action)
        <div class="mt-4">{{ $action }}</div>
    @endisset
</div>
