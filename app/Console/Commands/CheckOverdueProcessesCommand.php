<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NpcPartProcess;
use App\Services\NotificationRecipientService;
use App\Mail\PoProcessOverdueNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckOverdueProcessesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'npc:check-overdue-processes 
                            {--dry-run : Preview overdue processes without sending emails} 
                            {--force : Send notification even if already notified previously}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan active production processes exceeding target completion date and send escalation email to Department Manager & Assman.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        $today = now()->toDateString();

        $this->info("Scanning active production parts for overdue processes as of {$today}...");

        // Hanya cek part yang saat ini sedang aktif dalam tahap produksi (WAITING_DEPT_CONFIRM)
        $parts = \App\Models\NpcPart::where('status', 'WAITING_DEPT_CONFIRM')
            ->with(['processes.department', 'processes.process', 'event.customerCategory.customer', 'product'])
            ->get();

        $overdueProcesses = [];

        foreach ($parts as $part) {
            // Ambil proses yang AKTIF saat ini (tahapan pertama yang berstatus WAITING atau HOLD)
            $activeProc = $part->processes->sortBy('sequence_order')->first(function ($p) {
                return in_array($p->status, ['WAITING', 'HOLD']);
            });

            if ($activeProc && $activeProc->target_completion_date) {
                $targetDate = $activeProc->target_completion_date->format('Y-m-d');
                if ($targetDate < $today) {
                    // Cek proteksi anti-spam: Jangan kirim berulang jika baru saja dikirimi email dalam 5 jam terakhir
                    // (Mengakomodasi jadwal 2x sehari: jam 08:00 pagi dan 15:00 sore)
                    if (!$force && $activeProc->overdue_notified_at && Carbon::parse($activeProc->overdue_notified_at)->diffInHours(now()) < 5) {
                        continue;
                    }

                    $overdueProcesses[] = $activeProc;
                }
            }
        }

        if (empty($overdueProcesses)) {
            $this->info('No active overdue processes require notification today.');
            return 0;
        }

        $this->warn("Found " . count($overdueProcesses) . " active overdue process(es) holding the production line.");

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($overdueProcesses as $proc) {
            $dept = $proc->department;
            $deptName = $dept->name ?? 'Unknown Dept';
            $procName = $proc->process->process_name ?? "Process {$proc->sequence_order}";
            $poNo = $proc->part->event->po_no ?? 'N/A';
            $partNo = $proc->part->product->part_no ?? 'N/A';
            $targetDate = $proc->target_completion_date ? $proc->target_completion_date->format('Y-m-d') : '-';
            $daysOverdue = $proc->target_completion_date ? max(1, (int) Carbon::parse($proc->target_completion_date)->diffInDays(now()->startOfDay())) : 1;

            $recipients = NotificationRecipientService::getManagerRecipientsForDepartment($dept, true);

            if (empty($recipients)) {
                $this->error(" -> [NO RECIPIENT] Process ID {$proc->id} (PO: {$poNo}, Dept: {$deptName}). Please set Manager/Assman in Master Department.");
                Log::warning("Overdue check: No Manager/Assman email configured for department {$deptName} (Process ID: {$proc->id})");
                $skippedCount++;
                continue;
            }

            $recipientStr = implode(', ', $recipients);

            if ($isDryRun) {
                $this->line(" -> [DRY-RUN] Would send to: {$recipientStr} | PO: {$poNo} | Part: {$partNo} | Active Step: {$procName} ({$deptName}) | Overdue {$daysOverdue}d (Target: {$targetDate})");
                $sentCount++;
            } else {
                try {
                    Mail::to($recipients)->queue(new PoProcessOverdueNotificationMail($proc));
                    $proc->update(['overdue_notified_at' => now()]);
                    $this->info(" -> [SENT] Notified: {$recipientStr} | PO: {$poNo} | {$procName} ({$deptName})");
                    Log::info("Sent overdue notification for process {$proc->id} to: {$recipientStr}");
                    $sentCount++;
                } catch (\Throwable $e) {
                    $this->error(" -> [FAILED] Could not send to {$recipientStr}: " . $e->getMessage());
                    Log::error("Failed sending overdue notification for process {$proc->id}: " . $e->getMessage());
                    $skippedCount++;
                }
            }
        }

        $this->info("Scan completed: {$sentCount} notified, {$skippedCount} skipped.");
        return 0;
    }
}

