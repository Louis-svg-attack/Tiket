<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Hotel', 'route' => 'hotels.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Hotel" />
    @include('hotels._form')
</x-app-layout>
