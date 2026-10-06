<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use App\Models\NpcChecksheet;

class PoApprovalNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public NpcChecksheet $checksheet;
    public string $poNo;
    public string $partName;
    public string $partNo;
    public string $customerName;
    public string $approvalStage;
    public string $actionType; // 'REQUESTED', 'APPROVED', 'REJECTED'
    public string $actionBy;
    public ?string $notes;
    public string $actionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(NpcChecksheet $checksheet, string $actionType = 'REQUESTED', ?string $notes = null)
    {
        $this->checksheet = $checksheet;
        $this->actionType = strtoupper($actionType);
        $this->notes = $notes;

        $part = $checksheet->npcPart;
        $event = $part ? $part->event : null;
        $customerCategory = $event ? $event->customerCategory : null;
        $customer = $customerCategory ? $customerCategory->customer : null;

        $this->poNo = $event->po_no ?? 'N/A';
        $this->partName = $part->product->part_name ?? $part->part_name ?? 'N/A';
        $this->partNo = $part->product->part_no ?? $part->part_no ?? 'N/A';
        $this->customerName = $customer->name ?? $customer->code ?? 'N/A';

        $this->approvalStage = $checksheet->approval_status ?? 'APPROVAL_STAGE';
        $this->actionBy = auth()->user()->name ?? 'User';
        $this->actionUrl = route('checksheet-approvals.index');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusText = match($this->actionType) {
            'APPROVED' => 'Approved Stage ' . $this->approvalStage,
            'REJECTED' => 'REJECTED at Stage ' . $this->approvalStage,
            default => 'Need Approval: Stage ' . $this->approvalStage,
        };

        return new Envelope(
            subject: "[PROMISE-NPC] Approval {$statusText} - PO #{$this->poNo} ({$this->partName})",
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
            view: 'emails.po_approval_notification',
        );
    }
}
