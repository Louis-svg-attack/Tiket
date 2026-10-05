<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Perlengkapan', 'route' => 'equipment.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Perlengkapan" />
    @include('equipment._form')
</x-app-layout>
