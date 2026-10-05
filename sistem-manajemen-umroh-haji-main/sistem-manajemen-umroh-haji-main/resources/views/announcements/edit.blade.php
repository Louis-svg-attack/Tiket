<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pengumuman', 'route' => 'announcements.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Pengumuman" :subtitle="$announcement->title" />
    @include('announcements._form')
</x-app-layout>
