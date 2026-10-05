<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Cabang', 'route' => 'branches.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Cabang" :subtitle="$branch->name" />
    @include('branches._form')
</x-app-layout>
