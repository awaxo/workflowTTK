<?php

namespace App\Console;

use Exception;

/**
 * CommandRegistry is responsible for registering and managing console commands.
 * It allows commands to be registered with a specific frequency and parameters.
 */
class CommandRegistry
{
    protected static $commands = [];

    /**
     * Register a command with the given signature and frequency.
     *
     * @param string $commandClass
     * @param string $frequencyMethod
     * @param array $parameters (optional)
     */
    public static function registerCommand($commandClass, $frequencyMethod, $parameters = [])
    {
        if (!$commandClass) {
            throw new Exception('Invalid command class.');
        }
    
        if (empty($frequencyMethod)) {
            throw new Exception('Invalid frequency method. The method cannot be null or empty.');
        }

        self::$commands[] = [
            'class' => $commandClass,
            'frequency' => $frequencyMethod,
            'parameters' => $parameters,
        ];
    }

    /**
     * Get the registered commands.
     *
     * @return array
     */
    public static function getCommands()
    {
        return self::$commands;
    }

    /**
     * Get the classes of all registered commands.
     *
     * @return array
     */
    public static function getClasses()
    {
        $classes = [];
        foreach (self::$commands as $command) {
            $classes[] = $command['class'];
        }
        return $classes;
    }
}
