<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Manasik', 'route' => 'manasik.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Jadwal Manasik" />
    @include('manasik._form')
</x-app-layout>
