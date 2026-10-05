<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Hotel', 'route' => 'hotels.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Hotel" :subtitle="$hotel->name" />
    @include('hotels._form')
</x-app-layout>
