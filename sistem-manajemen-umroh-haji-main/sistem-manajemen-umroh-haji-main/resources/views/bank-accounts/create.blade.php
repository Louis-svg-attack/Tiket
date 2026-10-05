<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Rekening Bank', 'route' => 'bank-accounts.index'], ['label' => 'Tambah']]" />
    <x-page-header title="Tambah Rekening Bank" />
    @include('bank-accounts._form')
</x-app-layout>
