<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AttendanceCacheService
{
    // Cache TTL (Time To Live) in seconds
    const CACHE_TTL_SHORT = 300;      // 5 minutes - for frequently changing data
    const CACHE_TTL_MEDIUM = 1800;    // 30 minutes - for daily stats
    const CACHE_TTL_LONG = 3600;      // 1 hour - for monthly reports
    const CACHE_TTL_VERY_LONG = 86400; // 24 hours - for historical data

    /**
     * Get or cache dashboard statistics
     */
    public function getDashboardStats($date)
    {
        $cacheKey = "attendance:dashboard:{$date}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($date) {
            // This will be populated by the actual query
            return null;
        });
    }

    /**
     * Get or cache daily report
     */
    public function getDailyReport($date, $class = null, $section = null)
    {
        $cacheKey = "attendance:daily:{$date}:" . ($class ?? 'all') . ':' . ($section ?? 'all');
        
        return Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () {
            return null;
        });
    }

    /**
     * Get or cache weekly report
     */
    public function getWeeklyReport($startDate, $class = null, $section = null)
    {
        $cacheKey = "attendance:weekly:{$startDate}:" . ($class ?? 'all') . ':' . ($section ?? 'all');
        
        return Cache::remember($cacheKey, self::CACHE_TTL_LONG, function () {
            return null;
        });
    }

    /**
     * Get or cache monthly report
     */
    public function getMonthlyReport($month, $class = null, $section = null)
    {
        $cacheKey = "attendance:monthly:{$month}:" . ($class ?? 'all') . ':' . ($section ?? 'all');
        
        return Cache::remember($cacheKey, self::CACHE_TTL_LONG, function () {
            return null;
        });
    }

    /**
     * Get or cache class comparison data
     */
    public function getClassComparison($month)
    {
        $cacheKey = "attendance:class-comparison:{$month}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL_LONG, function () {
            return null;
        });
    }

    /**
     * Get or cache student attendance history
     */
    public function getStudentHistory($studentId, $startDate, $endDate)
    {
        $cacheKey = "attendance:student:{$studentId}:{$startDate}:{$endDate}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL_VERY_LONG, function () {
            return null;
        });
    }

    /**
     * Clear all attendance caches
     */
    public function clearAll()
    {
        Cache::flush();
    }

    /**
     * Clear cache for specific date
     */
    public function clearDate($date)
    {
        $patterns = [
            "attendance:dashboard:{$date}",
            "attendance:daily:{$date}:*",
        ];

        foreach ($patterns as $pattern) {
            $this->clearPattern($pattern);
        }
    }

    /**
     * Clear cache for specific month
     */
    public function clearMonth($month)
    {
        $patterns = [
            "attendance:monthly:{$month}:*",
            "attendance:class-comparison:{$month}",
        ];

        foreach ($patterns as $pattern) {
            $this->clearPattern($pattern);
        }
    }

    /**
     * Clear cache for specific class
     */
    public function clearClass($class)
    {
        // Clear all caches that might contain this class data
        $this->clearPattern("attendance:*:{$class}:*");
    }

    /**
     * Clear cache for specific student
     */
    public function clearStudent($studentId)
    {
        $this->clearPattern("attendance:student:{$studentId}:*");
    }

    /**
     * Clear cache by pattern (Redis specific)
     */
    private function clearPattern($pattern)
    {
        try {
            // For Redis driver
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // For file/database cache, we need to clear all
                // as pattern matching is not supported
                Cache::flush();
            }
        } catch (\Exception $e) {
            \Log::warning('Cache clear pattern failed: ' . $e->getMessage());
        }
    }

    /**
     * Warm up cache for today's data
     */
    public function warmUpToday()
    {
        $today = Carbon::today()->format('Y-m-d');
        $this->clearDate($today);
        
        // Pre-cache today's dashboard stats
        // This would trigger the actual query
        $this->getDashboardStats($today);
    }

    /**
     * Get cache statistics
     */
    public function getStats()
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $info = $redis->info();
                
                return [
                    'driver' => 'redis',
                    'keys' => $redis->dbSize(),
                    'memory_used' => $info['used_memory_human'] ?? 'N/A',
                    'connected' => true,
                ];
            }
            
            return [
                'driver' => config('cache.default'),
                'connected' => true,
            ];
        } catch (\Exception $e) {
            return [
                'driver' => config('cache.default'),
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
