<?php

namespace App\Mail;

use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayslipPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payslip;
    public $ptName;
    public $thrOnly;

    /**
     * Create a new message instance.
     */
    public function __construct(Payslip $payslip, ?string $ptName = null)
    {
        $this->payslip = $payslip;
        $this->ptName = $ptName;
        $this->thrOnly = false;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $monthName = \Carbon\Carbon::create()->month((int)$this->payslip->period_month)->locale('id')->translatedFormat('F');
        return new Envelope(
            subject: 'Slip Gaji ' . $monthName . ' ' . $this->payslip->period_year,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payslip_published',
            with: [
                'ptName' => $this->ptName,
                'thrOnly' => $this->thrOnly,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('hr.payroll.pdf_payslip', [
            'payslip' => $this->payslip,
            'ptName' => $this->ptName,
            'thrOnly' => $this->thrOnly,
        ])->setPaper('a5', 'landscape');
        $monthName = \Carbon\Carbon::create()->month((int)$this->payslip->period_month)->locale('id')->translatedFormat('F');

        return [
            Attachment::fromData(fn() => $pdf->output(), 'Slip Gaji ' . $monthName . ' ' . $this->payslip->period_year . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
