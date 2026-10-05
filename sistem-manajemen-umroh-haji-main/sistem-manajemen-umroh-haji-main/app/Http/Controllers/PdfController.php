<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function __construct(private readonly ActivityLogService $activity) {}

    /** Kwitansi pembayaran (payment receipt). */
    public function receipt(Payment $payment): Response
    {
        $this->authorize('view', $payment);

        $payment->load('registration.pilgrim', 'registration.package', 'bankAccount', 'branch');
        $this->activity->log('printed', "Cetak kwitansi {$payment->code}", $payment);

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'branding' => $this->branding(),
        ])->setPaper('a5', 'landscape');

        return $pdf->stream("kwitansi-{$payment->code}.pdf");
    }

    /** Manifest jamaah per paket (rooming/bus). */
    public function manifest(Package $package): Response
    {
        $this->authorize('view', $package);

        $package->load(['hotelMakkah', 'hotelMadinah', 'airline', 'branch']);
        $registrations = $package->registrations()
            ->where('status', '!=', RegistrationStatus::Cancelled->value)
            ->with(['pilgrim', 'manifestEntry', 'guide'])
            ->get()
            ->sortBy(fn ($r) => $r->pilgrim?->name);

        $this->activity->log('printed', "Cetak manifest paket {$package->code}", $package);

        $pdf = Pdf::loadView('pdf.manifest', [
            'package' => $package,
            'registrations' => $registrations,
            'branding' => $this->branding(),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("manifest-{$package->code}.pdf");
    }

    /** Kartu jamaah (pilgrim card). */
    public function pilgrimCard(Registration $registration): Response
    {
        $this->authorize('view', $registration);

        $registration->load(['pilgrim', 'package.branch', 'package.airline']);

        $pdf = Pdf::loadView('pdf.card', [
            'registration' => $registration,
            'pilgrim' => $registration->pilgrim,
            'package' => $registration->package,
            'branding' => $this->branding(),
        ])->setPaper('a6', 'landscape');

        return $pdf->stream("kartu-{$registration->code}.pdf");
    }

    private function branding(): array
    {
        return [
            'name' => setting('app_name'),
            'subtitle' => setting('app_subtitle'),
            'address' => setting('company_address'),
            'phone' => setting('company_phone'),
            'email' => setting('company_email'),
            'color' => setting('primary_color', '#0d9488'),
        ];
    }
}
