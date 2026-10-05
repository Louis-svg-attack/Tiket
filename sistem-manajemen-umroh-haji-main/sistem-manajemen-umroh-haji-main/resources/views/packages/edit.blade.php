<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Paket', 'route' => 'packages.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Paket" :subtitle="$package->name" />
    @include('packages._form')
</x-app-layout>
