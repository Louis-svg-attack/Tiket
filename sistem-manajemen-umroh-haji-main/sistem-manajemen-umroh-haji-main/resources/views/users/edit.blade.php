<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pengguna', 'route' => 'users.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Pengguna" :subtitle="$user->name" />
    @include('users._form')
</x-app-layout>
