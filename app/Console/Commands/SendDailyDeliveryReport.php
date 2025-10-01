<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\DeliveryNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDailyDeliveryReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delivery:daily-report {--date= : Generate report for specific date (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily delivery report to administrators';

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
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();
        
        $this->info("Generating daily delivery report for {$date->format('Y-m-d')}...");
        
        $deliveries = Invoice::withDeliveryScheduled()
            ->byDeliveryDate($date)
            ->with(['details'])
            ->get();
        
        $stats = $this->notificationService->getDeliveryStats($date);
        
        // Display report in console
        $this->displayReport($date, $stats, $deliveries);
        
        // Send notification to administrators
        if ($stats['total'] > 0) {
            $this->notificationService->sendDailyDeliveryList($date, $deliveries);
            $this->info('Daily delivery report sent to administrators.');
        } else {
            $this->info('No deliveries scheduled for this date.');
        }
        
        return 0;
    }
    
    private function displayReport(Carbon $date, array $stats, $deliveries): void
    {
        $this->line('');
        $this->line('=== REPORTE DIARIO DE ENTREGAS ===');
        $this->line("Fecha: {$date->format('d/m/Y (l)')}");
        $this->line('');
        
        // Statistics
        $this->line('📊 ESTADÍSTICAS:');
        $this->line("   Total de entregas: {$stats['total']}");
        $this->line("   🟡 Programadas: {$stats['programado']}");
        $this->line("   🟠 En ruta: {$stats['en_ruta']}");
        $this->line("   🟢 Entregadas: {$stats['entregado']}");
        $this->line("   🔴 Reprogramadas: {$stats['reprogramado']}");
        $this->line('');
        
        if ($deliveries->isNotEmpty()) {
            $this->line('📋 DETALLE DE ENTREGAS:');
            
            // Group by time slot
            $byTimeSlot = $deliveries->groupBy('delivery_time_slot');
            
            foreach ($byTimeSlot as $timeSlot => $slotDeliveries) {
                $timeSlotLabel = $timeSlot ? \App\Enums\DeliveryTimeSlot::from($timeSlot)->label() : 'Sin horario';
                $this->line("\n🕐 {$timeSlotLabel}:");
                
                foreach ($slotDeliveries as $delivery) {
                    $statusIcon = match($delivery->delivery_status?->value) {
                        'programado' => '🟡',
                        'en_ruta' => '🟠',
                        'entregado' => '🟢',
                        'reprogramado' => '🔴',
                        default => '⚪'
                    };
                    
                    $this->line("   {$statusIcon} {$delivery->full_number} - {$delivery->client_business_name}");
                    $this->line("      📍 {$delivery->client_address}");
                    if ($delivery->delivery_notes) {
                        $this->line("      📝 {$delivery->delivery_notes}");
                    }
                }
            }
        }
        
        $this->line('');
        $this->line('='.str_repeat('=', 35));
    }
}
