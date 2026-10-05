<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Muthawif', 'route' => 'guides.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Muthawif" />
    @include('guides._form')
</x-app-layout>
