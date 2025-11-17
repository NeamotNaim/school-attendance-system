# Redis Cache - Command Reference

## Essential Commands

### Cache Management
```bash
# View cache statistics
php artisan attendance:cache stats

# Clear all attendance cache
php artisan attendance:cache clear

# Clear specific date
php artisan attendance:cache clear --date=2025-11-17

# Clear specific month
php artisan attendance:cache clear --month=2025-11

# Warm up cache (pre-load today's data)
php artisan attendance:cache warm

# Flush ALL cache (use with caution!)
php artisan attendance:cache flush
```

### Redis Server
```bash
# Start Redis
brew services start redis

# Stop Redis
brew services stop redis

# Restart Redis
brew services restart redis

# Check Redis status
brew services list | grep redis

# Test connection
redis-cli ping
```

### Redis CLI
```bash
# Connect to Redis
redis-cli -n 1

# View all keys
redis-cli -n 1 KEYS "*"

# View attendance keys only
redis-cli -n 1 KEYS "*attendance*"

# Count keys
redis-cli -n 1 DBSIZE

# Get a key value
redis-cli -n 1 GET "key_name"

# Delete a key
redis-cli -n 1 DEL "key_name"

# Clear database
redis-cli -n 1 FLUSHDB

# Monitor in real-time
redis-cli -n 1 MONITOR

# Check memory
redis-cli -n 1 INFO memory

# Check stats
redis-cli -n 1 INFO stats
```

### Laravel Cache
```bash
# Clear config cache
php artisan config:clear

# Clear all cache
php artisan cache:clear

# Test in Tinker
php artisan tinker
>>> Cache::put('test', 'value', 60)
>>> Cache::get('test')
>>> Cache::has('test')
>>> Cache::forget('test')
```

## Cache Keys

```
attendance:dashboard:{date}
attendance:weekly:{start_date}:{class}:{section}
attendance:monthly:{month}:{class}:{section}
attendance:class-comparison:{month}
attendance:low:{month}:{threshold}
```

## Troubleshooting

```bash
# Redis not responding?
brew services restart redis
redis-cli ping

# Cache not working?
php artisan config:clear
php artisan cache:clear

# Check Laravel cache driver
php artisan tinker
>>> config('cache.default')

# View logs
tail -f storage/logs/laravel.log
```

## Quick Tests

```bash
# Test 1: Redis connection
redis-cli ping
# Expected: PONG

# Test 2: Cache statistics
php artisan attendance:cache stats
# Expected: Shows Redis connected

# Test 3: Store and retrieve
php artisan tinker
>>> Cache::put('test', 'working', 60)
>>> Cache::get('test')
# Expected: "working"

# Test 4: View keys
redis-cli -n 1 KEYS "*"
# Expected: Shows cached keys
```

## Performance Monitoring

```bash
# Watch memory usage
watch -n 1 'redis-cli -n 1 INFO memory | grep used_memory_human'

# Watch key count
watch -n 1 'redis-cli -n 1 DBSIZE'

# Monitor cache hits/misses
redis-cli -n 1 INFO stats | grep keyspace
```

## Production Commands

```bash
# Set Redis password
redis-cli CONFIG SET requirepass "your_password"

# Set memory limit
redis-cli CONFIG SET maxmemory 512mb
redis-cli CONFIG SET maxmemory-policy allkeys-lru

# Enable persistence
redis-cli CONFIG SET save "900 1 300 10 60 10000"

# Check configuration
redis-cli CONFIG GET maxmemory
redis-cli CONFIG GET save
```

## Emergency Commands

```bash
# If Redis is using too much memory
redis-cli -n 1 FLUSHDB

# If cache is causing issues
php artisan attendance:cache clear
php artisan config:clear

# If Redis won't start
brew services stop redis
brew services start redis

# Nuclear option (restart everything)
brew services restart redis
php artisan config:clear
php artisan cache:clear
php artisan attendance:cache warm
```

---

**Quick Reference**: Keep this file handy for daily cache management!
