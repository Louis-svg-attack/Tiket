<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Maskapai', 'route' => 'airlines.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Maskapai" :subtitle="$airline->name" />
    @include('airlines._form')
</x-app-layout>
