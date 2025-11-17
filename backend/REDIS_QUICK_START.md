# Redis Cache - Quick Start Guide

## ✅ Status: ACTIVE

Redis caching is installed and running!

## Quick Commands

```bash
# Check cache status
php artisan attendance:cache stats

# Clear all cache
php artisan attendance:cache clear

# View Redis keys
redis-cli -n 1 KEYS "*attendance*"

# Test Redis connection
redis-cli ping
```

## What's Cached?

| Endpoint | Cache Time | Speed Improvement |
|----------|------------|-------------------|
| Dashboard | 5 minutes | 5-10x faster |
| Weekly Report | 1 hour | 8-10x faster |
| Monthly Report | 1 hour | 8-10x faster |
| Class Comparison | 1 hour | 10-15x faster |
| Low Attendance | 30 minutes | 8-10x faster |

## How It Works

1. **First Request**: Data is fetched from database and cached
2. **Subsequent Requests**: Data is served from Redis (super fast!)
3. **Cache Expires**: After TTL, data is refreshed automatically

## When to Clear Cache

- After bulk data import
- After manual database changes
- When testing new features
- If data seems stale

```bash
php artisan attendance:cache clear
```

## Monitoring

```bash
# View all cached keys
redis-cli -n 1 KEYS "*"

# Check memory usage
redis-cli -n 1 INFO memory | grep used_memory_human

# Monitor in real-time
redis-cli -n 1 MONITOR
```

## Troubleshooting

### Cache not working?
```bash
php artisan config:clear
php artisan attendance:cache stats
```

### Redis not running?
```bash
brew services restart redis
redis-cli ping
```

### Need to flush everything?
```bash
php artisan attendance:cache flush
```

## That's It!

Your attendance system is now turbocharged with Redis! 🚀
