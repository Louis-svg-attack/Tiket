<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Maskapai', 'route' => 'airlines.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Maskapai" />
    @include('airlines._form')
</x-app-layout>
