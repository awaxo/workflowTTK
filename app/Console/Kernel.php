<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

/**
 * Console Kernel
 *
 * This class is responsible for registering and scheduling console commands.
 */
class Kernel extends ConsoleKernel
{
    protected $commands = [];

    /**
     * Register the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule): void
    {
        $commands = CommandRegistry::getCommands();

        foreach ($commands as $command) {
            try{
                $scheduleCommand = $schedule->command($command['class']);

                if (method_exists($scheduleCommand, $command['frequency'])) {
                    $scheduleCommand->{$command['frequency']}(...$command['parameters']);
                }
            } catch (\Exception $e) {
                Log::error("Failed to schedule command '{$command['class']}': " . $e->getMessage());
            }
        }
    }

    /**
     * Register the commands for the application.
     *
     * This method loads commands from the CommandRegistry and dynamically from all modules.
     */
    protected function commands(): void
    {
        $this->commands = array_merge($this->commands, CommandRegistry::getClasses());
        $this->load(__DIR__ . '/Commands');

        // Dynamically load commands from all modules
        $modules = app('modules')->all();
        foreach ($modules as $module) {
            $this->load($module->getPath() . '/App/Console/Commands');
        }

        require base_path('routes/console.php');
    }
}
