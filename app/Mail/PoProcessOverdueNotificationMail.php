<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use App\Models\NpcPartProcess;
use Carbon\Carbon;

class PoProcessOverdueNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public NpcPartProcess $process;
    public string $poNo;
    public string $partName;
    public string $partNo;
    public string $customerName;
    public string $processName;
    public string $departmentName;
    public string $targetDateFormatted;
    public int $daysOverdue;
    public string $currentStatus;
    public string $actionUrl;
    public string $recipientRole;

    /**
     * Create a new message instance.
     */
    public function __construct(NpcPartProcess $process, string $recipientRole = 'Manager / Assman')
    {
        $this->process = $process;
        $this->recipientRole = $recipientRole;

        $part = $process->part;
        $event = $part ? $part->event : null;
        $customerCategory = $event ? $event->customerCategory : null;
        $customer = $customerCategory ? $customerCategory->customer : null;

        $this->poNo = $event->po_no ?? 'N/A';
        $this->partName = $part->product->part_name ?? $part->part_name ?? 'N/A';
        $this->partNo = $part->product->part_no ?? $part->part_no ?? 'N/A';
        $this->customerName = $customer->name ?? $customer->code ?? 'N/A';

        $this->processName = $process->process->process_name ?? 'Process ' . $process->sequence_order;
        $this->departmentName = $process->department->name ?? $process->department->full_name ?? 'Responsible Dept';
        $this->currentStatus = $process->status ?? 'IN PROCESS';

        $rawTarget = $process->target_completion_date;
        if ($rawTarget) {
            $targetCarbon = Carbon::parse($rawTarget)->startOfDay();
            $this->targetDateFormatted = $targetCarbon->locale('en')->translatedFormat('d F Y');
            $this->daysOverdue = max(1, (int) $targetCarbon->diffInDays(now()->startOfDay()));
        } else {
            $this->targetDateFormatted = '-';
            $this->daysOverdue = 1;
        }

        $this->actionUrl = route('tracking.production');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[OVERDUE ALERT] {$this->processName} ({$this->departmentName}) Overdue by {$this->daysOverdue}d - PO #{$this->poNo}",
        );
    }

    /**
     * Custom Headers for Email Threading based on PO Number
     */
    public function headers(): Headers
    {
        $cleanPo = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($this->poNo));
        $threadId = "po-{$cleanPo}@promise-npc.com";

        return new Headers(
            references: [$threadId],
            text: [
                'In-Reply-To' => "<{$threadId}>",
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.po_process_overdue_notification',
        );
    }
}

