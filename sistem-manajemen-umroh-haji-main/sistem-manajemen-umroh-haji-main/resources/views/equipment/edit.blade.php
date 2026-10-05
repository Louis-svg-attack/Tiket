<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Perlengkapan', 'route' => 'equipment.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Perlengkapan" :subtitle="$equipment->item" />
    @include('equipment._form')
</x-app-layout>
