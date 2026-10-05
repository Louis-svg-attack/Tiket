<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Agen', 'route' => 'agents.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Agen" />
    @include('agents._form')
</x-app-layout>
