<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManageSensorPartitions extends Command
{
    protected $signature = 'partition:manage-sensor';
    protected $description = 'Create next month partition and drop old partitions';

    public function handle()
    {
        $this->createNextPartition();
        $this->dropOldPartitions();
    }

    private function createNextPartition()
    {
        $nextMonth = Carbon::now()->addMonth();

        $partitionName = 'p'.$nextMonth->format('Ym');
        $partitionValue = $nextMonth->copy()->addMonth()->format('Ym');

        DB::statement("
            ALTER TABLE sensor_log_values
            ADD PARTITION (
                PARTITION $partitionName VALUES LESS THAN ($partitionValue)
            )
        ");
    }

    private function dropOldPartitions()
    {
        $oldMonth = Carbon::now()->subMonths(6);
        $partitionName = 'p'.$oldMonth->format('Ym');

        try {
            DB::statement("
                ALTER TABLE sensor_log_values
                DROP PARTITION $partitionName
            ");
        } catch (\Exception $e) {
            // partition might not exist
        }
    }
}
