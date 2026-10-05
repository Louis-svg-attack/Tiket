<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Jamaah', 'route' => 'pilgrims.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Jamaah" />
    @include('pilgrims._form')
</x-app-layout>
