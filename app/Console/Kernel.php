<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $commands = CommandRegistry::getCommands();

        foreach ($commands as $command) {
            $scheduleCommand = $schedule->command($command['class']);

            if (method_exists($scheduleCommand, $command['frequency'])) {
                $scheduleCommand->{$command['frequency']}(...$command['parameters']);
            }
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->commands = array_merge($this->commands, CommandRegistry::getClasses());
        
        $this->load(__DIR__.'/Commands');

        // Dynamically load commands from all modules
        $modules = app('modules')->all();
        foreach ($modules as $module) {
            $this->load($module->getPath() . '/App/Console/Commands');
        }

        require base_path('routes/console.php');
    }
}
