<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceCacheService;
use Illuminate\Support\Facades\Cache;

class AttendanceCacheCommand extends Command
{
    protected $signature = 'attendance:cache 
                            {action : Action to perform (clear, warm, stats, flush)}
                            {--date= : Specific date for cache operations}
                            {--month= : Specific month for cache operations}
                            {--class= : Specific class for cache operations}';

    protected $description = 'Manage attendance cache operations';

    protected AttendanceCacheService $cacheService;

    public function __construct(AttendanceCacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    public function handle()
    {
        $action = $this->argument('action');
        $date = $this->option('date');
        $month = $this->option('month');
        $class = $this->option('class');

        switch ($action) {
            case 'clear':
                $this->clearCache($date, $month, $class);
                break;
            case 'warm':
                $this->warmCache();
                break;
            case 'stats':
                $this->showStats();
                break;
            case 'flush':
                $this->flushAll();
                break;
            default:
                $this->error("Invalid action: {$action}");
                $this->info('Available actions: clear, warm, stats, flush');
                return 1;
        }

        return 0;
    }

    private function clearCache($date, $month, $class)
    {
        if ($date) {
            $this->info("Clearing cache for date: {$date}");
            $this->cacheService->clearDate($date);
        } elseif ($month) {
            $this->info("Clearing cache for month: {$month}");
            $this->cacheService->clearMonth($month);
        } elseif ($class) {
            $this->info("Clearing cache for class: {$class}");
            $this->cacheService->clearClass($class);
        } else {
            $this->info('Clearing all attendance caches...');
            $this->cacheService->clearAll();
        }

        $this->info('✓ Cache cleared successfully!');
    }

    private function warmCache()
    {
        $this->info('Warming up cache for today\'s data...');
        
        $progressBar = $this->output->createProgressBar(3);
        $progressBar->start();

        $this->cacheService->warmUpToday();
        $progressBar->advance();
        $progressBar->advance();
        $progressBar->advance();

        $progressBar->finish();
        $this->newLine(2);
        $this->info('✓ Cache warmed up successfully!');
    }

    private function showStats()
    {
        $stats = $this->cacheService->getStats();

        $this->info('Cache Statistics:');
        $this->table(
            ['Property', 'Value'],
            [
                ['Driver', $stats['driver']],
                ['Connected', $stats['connected'] ? 'Yes' : 'No'],
                ['Keys', $stats['keys'] ?? 'N/A'],
                ['Memory Used', $stats['memory_used'] ?? 'N/A'],
            ]
        );

        if (isset($stats['error'])) {
            $this->error('Error: ' . $stats['error']);
        }
    }

    private function flushAll()
    {
        if ($this->confirm('Are you sure you want to flush ALL cache?')) {
            Cache::flush();
            $this->info('✓ All cache flushed!');
        } else {
            $this->info('Operation cancelled.');
        }
    }
}
