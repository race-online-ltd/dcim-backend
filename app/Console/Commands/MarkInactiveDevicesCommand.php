<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DeviceList;

/**
 * Separate scheduled command to auto-mark devices as offline (is_active = 0)
 * if their device_lists.updated_at has not been touched in the last 10 minutes.
 *
 * REGISTER IN: app/Console/Kernel.php
 * -----------------------------------------------
 * protected function schedule(Schedule $schedule)
 * {
 *     $schedule->command('mqtt:mark-inactive-devices')->everyMinute();
 * }
 * -----------------------------------------------
 *
 * RUN MANUALLY:
 *   php artisan mqtt:mark-inactive-devices
 */
class MarkInactiveDevicesCommand extends Command
{
    protected $signature   = 'mqtt:mark-inactive-devices';
    protected $description = 'Mark devices as inactive (is_active = 0) if no MQTT update received in last 10 minutes';

    public function handle()
    {
        // Threshold: devices not updated in the last 10 minutes
        $cutoff = now()->subMinutes(10);

        // Find all devices that are currently active but haven't been updated recently
        $staleDevices = DeviceList::where('is_active', 1)
            ->where('updated_at', '<', $cutoff)
            ->get();

        if ($staleDevices->isEmpty()) {
            $this->info("✅ All active devices are up to date. Nothing to mark offline.");
            Log::info("MarkInactiveDevices: No stale devices found.");
            return;
        }

        foreach ($staleDevices as $device) {
            $minutesAgo = now()->diffInMinutes($device->updated_at);

            $device->is_active = 0;
            $device->save();

            $msg = "📴 Device ID {$device->id} ({$device->name}) marked inactive — last update was {$minutesAgo} minute(s) ago";
            $this->warn($msg);
            Log::warning($msg);
        }

        $this->info("Done. Marked {$staleDevices->count()} device(s) as inactive.");
        Log::info("MarkInactiveDevices: Marked {$staleDevices->count()} device(s) as inactive.");
    }
}