<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Manasik', 'route' => 'manasik.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Jadwal Manasik" :subtitle="$schedule->title" />
    @include('manasik._form')
</x-app-layout>
