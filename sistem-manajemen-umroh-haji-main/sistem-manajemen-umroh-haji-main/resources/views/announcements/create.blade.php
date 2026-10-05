<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pengumuman', 'route' => 'announcements.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Pengumuman" />
    @include('announcements._form')
</x-app-layout>
