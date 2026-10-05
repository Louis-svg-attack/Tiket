<x-app-layout>
    <x-breadcrumb :items="[['label' => 'Rekening Bank', 'route' => 'bank-accounts.index'], ['label' => 'Edit']]" />
    <x-page-header title="Edit Rekening Bank" :subtitle="$account->bank_name" />
    @include('bank-accounts._form')
</x-app-layout>
