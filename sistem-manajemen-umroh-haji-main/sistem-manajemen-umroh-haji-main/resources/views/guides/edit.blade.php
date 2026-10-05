<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Muthawif', 'route' => 'guides.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Muthawif" :subtitle="$guide->name" />
    @include('guides._form')
</x-app-layout>
