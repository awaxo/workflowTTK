<?php

namespace Modules\EmployeeRecruitment\App\Providers;

use App\Console\CommandRegistry;
use App\Services\WorkflowRegistry;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Modules\EmployeeRecruitment\App\Console\Commands\CheckStateDeadlines;
use Modules\EmployeeRecruitment\App\Console\Commands\CheckSuspendedDeadline;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\Database\Seeders\RecruitmentPermissionSeeder;
use Modules\EmployeeRecruitment\Database\Seeders\RecruitmentRolePermissionSeeder;
use Modules\EmployeeRecruitment\Database\Seeders\RecruitmentWorkflowTypeSeeder;

class EmployeeRecruitmentServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'EmployeeRecruitment';
    protected string $moduleNameLower = 'employeerecruitment';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerSeeders();
        $this->registerWorkflow();
        $this->registerEventServciceProvider();
        $this->registerClassAliases();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        CommandRegistry::registerCommand(CheckSuspendedDeadline::class, 'daily');
        CommandRegistry::registerCommand(CheckStateDeadlines::class, 'daily');
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        //
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
        
        $path = module_path($this->moduleName, 'config/workflow.php');
        $workflowConfig = require $path;
        foreach ($workflowConfig as $workflowName => $configuration) {
            Config::set("workflow.$workflowName", $configuration);
        }
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.config('modules.paths.generator.component-class.path'));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);

        View::addNamespace($this->moduleName, $sourcePath);
    }

    public function registerSeeders(): void
    {
        DatabaseSeeder::addSeeder(RecruitmentPermissionSeeder::class);
        DatabaseSeeder::addSeeder(RecruitmentWorkflowTypeSeeder::class);
        DatabaseSeeder::addSeeder(RecruitmentRolePermissionSeeder::class);
    }

    public function registerWorkflow(): void
    {
        WorkflowRegistry::register(RecruitmentWorkflow::class);
    }

    public function registerEventServciceProvider(): void
    {
        $this->app->register(EventServiceProvider::class);
    }

    public function registerClassAliases(): void
    {
        if (!class_exists('Carbon')) {
            class_alias(\Carbon\Carbon::class, 'Carbon');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
