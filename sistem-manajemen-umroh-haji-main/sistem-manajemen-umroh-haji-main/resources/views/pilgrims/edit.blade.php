<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Jamaah', 'route' => 'pilgrims.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Jamaah" :subtitle="$pilgrim->name" />
    @include('pilgrims._form')
</x-app-layout>
