<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Cabang', 'route' => 'branches.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Cabang" />
    @include('branches._form')
</x-app-layout>
