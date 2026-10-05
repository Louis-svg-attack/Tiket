@props(['headers' => []])

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        @if(count($headers))
            <thead class="bg-gray-50">
                <tr>
                    @foreach($headers as $h)
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                            {{ $h }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-gray-100">
            {{ $slot }}
        </tbody>
    </table>
</div>
