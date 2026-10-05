<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pendaftaran', 'route' => 'registrations.index'], ['label' => $registration->code], ['label' => 'Edit']]" />
    <x-page-header title="Edit Pendaftaran" :subtitle="$registration->code" />
    @include('registrations._form')
</x-app-layout>
