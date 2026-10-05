<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pengguna', 'route' => 'users.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Pengguna" />
    @include('users._form')
</x-app-layout>
