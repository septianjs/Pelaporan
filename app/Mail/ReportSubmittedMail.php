<?php

namespace App\Mail;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Report $report;
    public ?string $notes;
    public ?string $customSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report, ?string $notes = null, ?string $customSubject = null)
    {
        $this->report = $report;
        $this->notes = $notes;
        $this->customSubject = $customSubject;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->customSubject ?: "[BPSDM] Laporan Pengawasan Lingkungan Kantor - {$this->report->report_number} ({$this->report->officer_name})";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.report_submitted',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->report->pdf_path) {
            $pdfFullPath = storage_path("app/public/{$this->report->pdf_path}");
            if (file_exists($pdfFullPath)) {
                $cleanNum = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->report->report_number);
                $attachments[] = Attachment::fromPath($pdfFullPath)
                    ->as("Laporan_{$cleanNum}.pdf")
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}

