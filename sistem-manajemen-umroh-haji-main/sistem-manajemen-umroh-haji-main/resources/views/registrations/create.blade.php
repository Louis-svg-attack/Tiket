<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Pendaftaran', 'route' => 'registrations.index'], ['label' => 'Daftarkan']]" />
    <x-page-header title="Daftarkan Jamaah" />
    @include('registrations._form')
</x-app-layout>
