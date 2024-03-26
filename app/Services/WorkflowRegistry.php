<?php

namespace App\Services;

class WorkflowRegistry
{
    /**
     * A static list of workflow classes.
     *
     * @var string[]
     */
    protected static $workflows = [];

    /**
     * Register a workflow class.
     *
     * @param string $workflowClass The workflow class to register.
     */
    public static function register(string $workflowClass): void
    {
        self::$workflows[] = $workflowClass;
    }

    /**
     * Get all registered workflow classes.
     *
     * @return string[]
     */
    public static function getAll(): array
    {
        return self::$workflows;
    }
}
