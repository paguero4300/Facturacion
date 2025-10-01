<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\DeliveryNotificationService;
use App\Enums\DeliveryStatus;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDeliveryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delivery:send-reminders {--date= : Send reminders for specific date (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send delivery reminders to customers for tomorrow\'s deliveries';

    private DeliveryNotificationService $notificationService;

    public function __construct(DeliveryNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::tomorrow();
        
        $this->info("Sending delivery reminders for {$date->format('Y-m-d')}...");
        
        $deliveries = Invoice::withDeliveryScheduled()
            ->byDeliveryDate($date)
            ->byDeliveryStatus(DeliveryStatus::PROGRAMADO)
            ->whereNotNull('client_email')
            ->get();
        
        if ($deliveries->isEmpty()) {
            $this->info('No deliveries found for the specified date.');
            return 0;
        }
        
        $sent = 0;
        
        foreach ($deliveries as $delivery) {
            try {
                $this->notificationService->sendDeliveryReminder($delivery);
                $sent++;
                $this->line("Reminder sent to {$delivery->client_email} for order {$delivery->full_number}");
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for order {$delivery->full_number}: {$e->getMessage()}");
            }
        }
        
        $this->info("Delivery reminders process completed. Sent: {$sent} of {$deliveries->count()}");
        
        return 0;
    }
}
