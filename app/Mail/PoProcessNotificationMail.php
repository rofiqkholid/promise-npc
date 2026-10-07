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

class PoProcessNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public NpcPartProcess $partProcess;
    public ?NpcPartProcess $nextProcess;
    public string $poNo;
    public string $partName;
    public string $partNo;
    public string $customerName;
    public string $completedProcessName;
    public string $nextProcessName;
    public string $nextDepartmentName;
    public string $completedBy;
    public string $actionUrl;
    public string $messageText;
    public string $boxHeaderTitle;
    public string $targetDateFormatted;

    /**
     * Create a new message instance.
     */
    public function __construct(NpcPartProcess $partProcess, ?NpcPartProcess $nextProcess = null)
    {
        $this->partProcess = $partProcess;
        $this->nextProcess = $nextProcess;

        $part = $partProcess->part;
        $event = $part ? $part->event : null;
        $customerCategory = $event ? $event->customerCategory : null;
        $customer = $customerCategory ? $customerCategory->customer : null;

        $this->poNo = $event->po_no ?? 'N/A';
        $this->partName = $part->product->part_name ?? $part->part_name ?? 'N/A';
        $this->partNo = $part->product->part_no ?? $part->part_no ?? 'N/A';
        $this->customerName = $customer->name ?? $customer->code ?? 'N/A';

        $this->completedBy = auth()->user()->name ?? 'Operator';
        $isInitialSetup = ($partProcess && $nextProcess && $partProcess->id === $nextProcess->id);

        if ($nextProcess) {
            $this->nextProcessName = $nextProcess->process->process_name ?? 'Next Step';
            $this->nextDepartmentName = $nextProcess->department->name ?? $nextProcess->department->full_name ?? '-';
        } else {
            $this->nextProcessName = 'Final QC / Completed';
            $this->nextDepartmentName = 'Quality Assurance';
        }

        $this->boxHeaderTitle = 'PROCESS TO BE ACTIONED';

        if ($isInitialSetup) {
            $this->completedProcessName = 'PO Setup & Routing';
        } else {
            $this->completedProcessName = $partProcess->process->process_name ?? 'Process';
        }

        $rawTargetDate = null;
        if ($nextProcess && $nextProcess->target_completion_date) {
            $rawTargetDate = $nextProcess->target_completion_date;
        } elseif ($part) {
            $rawTargetDate = $part->qc_target_date ?? $part->delivery_date;
        }

        if ($rawTargetDate) {
            try {
                $this->targetDateFormatted = \Carbon\Carbon::parse($rawTargetDate)->locale('en')->translatedFormat('d F Y');
            } catch (\Throwable $e) {
                $this->targetDateFormatted = (string) $rawTargetDate;
            }
        } else {
            $this->targetDateFormatted = '-';
        }

        $this->messageText = "Please immediately action and process the <strong>{$this->nextProcessName}</strong> stage for the part below (updated by <strong>{$this->completedBy}</strong>).";

        $this->actionUrl = route('tracking.production');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[PROMISE-NPC] PO Progress #{$this->poNo} - {$this->partName}",
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
            view: 'emails.po_process_notification',
        );
    }
}
