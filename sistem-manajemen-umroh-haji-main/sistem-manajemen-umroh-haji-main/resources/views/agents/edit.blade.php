<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Agen', 'route' => 'agents.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Agen" :subtitle="$agent->name" />
    @include('agents._form')
</x-app-layout>
