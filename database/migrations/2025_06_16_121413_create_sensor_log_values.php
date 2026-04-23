<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    // public function up()
    // {
    //     // Drop the table if it already exists to allow for re-running migrations
    //     Schema::dropIfExists('sensor_log_values');

    //     // Use DB::statement to create the table with partitioning syntax
    //     // Primary key MUST include the partitioning column (created_at)
    //     // UNIX_TIMESTAMP(created_at) is used because RANGE partitioning requires an integer expression.
    //     // We're creating partitions based on the start of each week.
    //     DB::statement("
    //         CREATE TABLE sensor_log_values (
    //             id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    //             sensor_id BIGINT UNSIGNED NOT NULL,
    //             value VARCHAR(255) NOT NULL,
    //             created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    //             updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    //             PRIMARY KEY (id, created_at) -- Primary key MUST include 'created_at' for partitioning
    //         )
    //         PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
    //             " . $this->generateInitialPartitions(5) . " -- Generate initial partitions for current + next 4 weeks
    //         );
    //     ");

    //     // The foreign key constraint has been removed here due to MySQL/MariaDB limitation:
    //     // "Partitioned tables do not support FOREIGN KEY".
    //     // You will need to enforce referential integrity at the application level.

    //     Schema::table('sensor_log_values', function (Blueprint $table) {
    //         // Add the composite index as originally requested, outside the PK
    //         // This can still be beneficial for queries filtering by sensor_id first.
    //         $table->index(['sensor_id', 'created_at']);
    //     });
    // }


    public function up()
    {
        Schema::dropIfExists('sensor_log_values');

        DB::statement("
            CREATE TABLE sensor_log_values (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                sensor_id BIGINT UNSIGNED NOT NULL,
                value VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NULL,
                PRIMARY KEY (id, created_at),
                INDEX idx_sensor_created (sensor_id, created_at)
            )
            PARTITION BY RANGE (TO_DAYS(created_at)) (
                PARTITION p202603 VALUES LESS THAN (TO_DAYS('2026-04-01')),
                PARTITION p202604 VALUES LESS THAN (TO_DAYS('2026-05-01')),
                PARTITION p202605 VALUES LESS THAN (TO_DAYS('2026-06-01'))
            );
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the table, which will also remove all its partitions
        Schema::dropIfExists('sensor_log_values');

        // Optional: Drop the MySQL Event if it was created by this migration or related setup.
        // DB::statement("DROP EVENT IF EXISTS manage_sensor_log_partitions;");
    }

    /**
     * Helper function to generate initial partitions dynamically.
     * Generates partitions for the current week and the next 'numWeeks' weeks.
     * Each partition's 'VALUES LESS THAN' will be the UNIX timestamp of the start of the following week.
     *
     * @param int $numWeeks The number of future weeks to generate partitions for.
     * @return string The SQL string for initial partitions.
     */
    protected function generateInitialPartitions(int $numWeeks = 5): string
    {
        $partitions = [];
        $currentDate = new \DateTime();

        // Move the date to the beginning of the *current* ISO week (Monday)
        // This ensures consistent weekly partitioning starting on Monday.
        $currentDate->setISODate($currentDate->format('Y'), $currentDate->format('W'), 1);

        for ($i = 0; $i < $numWeeks; $i++) {
            $partitionEndDate = clone $currentDate;
            $partitionEndDate->modify('+1 week'); // End of current partition (start of next week)

            $partitionName = 'p' . $currentDate->format('Ymd'); // e.g., p20240617
            $partitionValuesLessThan = $partitionEndDate->getTimestamp();

            $partitions[] = "PARTITION {$partitionName} VALUES LESS THAN ({$partitionValuesLessThan})";

            // Move to the start of the next week for the next partition
            $currentDate->modify('+1 week');
        }

        return implode(",\n                ", $partitions);
    }
};
