<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckDevMode extends Command
{
    protected $signature = 'dev:check';
    protected $description = 'Check if dev mode features are enabled';

    public function handle()
    {
        $this->info('🔍 Checking Development Mode Configuration...');
        $this->newLine();

        // Check environment
        $env = app()->environment();
        $isLocal = $env === 'local';
        $this->line("Environment: <fg={$this->getColor($isLocal)}>{$env}</>");
        
        // Check if routes are registered
        $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())
            ->filter(fn($route) => str_starts_with($route->uri(), 'dev/'))
            ->count();
        
        $hasRoutes = $routes > 0;
        $this->line("Dev Routes Registered: <fg={$this->getColor($hasRoutes)}>{$routes} routes</>");
        
        // Check DateHelper
        $dateHelperExists = class_exists(\App\Helpers\DateHelper::class);
        $this->line("DateHelper Class: <fg={$this->getColor($dateHelperExists)}>" . 
            ($dateHelperExists ? 'Found' : 'Not Found') . "</>");
        
        // Check if test mode is active
        if ($dateHelperExists) {
            $isTestMode = \App\Helpers\DateHelper::isTestMode();
            $testDate = $isTestMode ? \App\Helpers\DateHelper::today() : 'N/A';
            $this->line("Test Mode Active: <fg={$this->getColor($isTestMode)}>" . 
                ($isTestMode ? "Yes ({$testDate})" : 'No') . "</>");
        }
        
        // Check frontend component
        $componentPath = resource_path('js/components/dev-date-panel.tsx');
        $componentExists = file_exists($componentPath);
        $this->line("Dev Panel Component: <fg={$this->getColor($componentExists)}>" . 
            ($componentExists ? 'Found' : 'Not Found') . "</>");
        
        $this->newLine();
        
        // Summary
        if ($isLocal && $hasRoutes && $dateHelperExists && $componentExists) {
            $this->info('✅ All dev mode features are properly configured!');
            $this->newLine();
            $this->line('Available routes:');
            $this->line('  • /dev/set-date/{date}');
            $this->line('  • /dev/clear-date');
            $this->line('  • /dev/date-info');
        } else {
            $this->error('❌ Some dev mode features are not configured properly.');
            $this->newLine();
            
            if (!$isLocal) {
                $this->warn('⚠️  APP_ENV is not set to "local"');
                $this->line('   Set APP_ENV=local in your .env file');
            }
            
            if (!$hasRoutes) {
                $this->warn('⚠️  Dev routes are not registered');
                $this->line('   Run: php artisan route:clear');
            }
            
            if (!$dateHelperExists) {
                $this->warn('⚠️  DateHelper class not found');
                $this->line('   Make sure app/Helpers/DateHelper.php exists');
            }
            
            if (!$componentExists) {
                $this->warn('⚠️  Dev panel component not found');
                $this->line('   Make sure resources/js/components/dev-date-panel.tsx exists');
            }
        }
        
        $this->newLine();
        $this->line('💡 Tip: Run "php artisan optimize:clear" to clear all caches');
        
        return 0;
    }
    
    private function getColor(bool $condition): string
    {
        return $condition ? 'green' : 'red';
    }
}
