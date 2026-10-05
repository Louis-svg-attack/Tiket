<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Paket', 'route' => 'packages.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Paket" />
    @include('packages._form')
</x-app-layout>
