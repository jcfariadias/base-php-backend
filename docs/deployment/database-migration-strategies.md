# Database Migration Strategies

## 🗄️ PostgreSQL Migration Strategies

### Development to Production Migration

#### Initial Database Setup
```bash
# Create production database
CREATE DATABASE warehouse_prod;
CREATE USER warehouse_prod WITH PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE warehouse_prod TO warehouse_prod;

# Grant schema permissions
GRANT ALL ON SCHEMA public TO warehouse_prod;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO warehouse_prod;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO warehouse_prod;
```

### Migration Execution Strategies

#### 1. Zero-Downtime Migration (Recommended for Production)
```bash
#!/bin/bash
# File: scripts/zero-downtime-migration.sh

set -e

echo "🚀 Starting zero-downtime migration"

# Step 1: Create backup
echo "📦 Creating database backup..."
pg_dump $SOURCE_DATABASE_URL > backup_$(date +%Y%m%d_%H%M%S).sql

# Step 2: Run migrations in transaction
echo "🔄 Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Step 3: Validate schema
echo "✅ Validating schema..."
php bin/console doctrine:schema:validate

# Step 4: Warm up cache
echo "🔥 Warming up cache..."
php bin/console cache:warmup --env=prod

echo "✅ Migration completed successfully"
```

#### 2. Blue-Green Deployment with Database
```bash
#!/bin/bash
# File: scripts/blue-green-migration.sh

BLUE_DB="warehouse_blue"
GREEN_DB="warehouse_green" 
CURRENT_DB=$(cat current_db.txt)

if [ "$CURRENT_DB" = "$BLUE_DB" ]; then
    NEW_DB=$GREEN_DB
    OLD_DB=$BLUE_DB
else
    NEW_DB=$BLUE_DB
    OLD_DB=$GREEN_DB
fi

echo "🔄 Migrating to $NEW_DB"

# Clone current database
echo "📋 Cloning database..."
pg_dump $OLD_DB | psql $NEW_DB

# Run migrations on new database
echo "🔄 Running migrations on $NEW_DB..."
DATABASE_URL="postgresql://user:pass@host:5432/$NEW_DB" \
php bin/console doctrine:migrations:migrate --no-interaction

# Switch application to new database
echo "🔄 Switching to $NEW_DB..."
echo $NEW_DB > current_db.txt

# Update environment variable
sed -i "s/database_name/$NEW_DB/g" .env.prod

# Restart application
docker-compose restart app

echo "✅ Migration to $NEW_DB completed"
```

#### 3. Rolling Migration for Large Datasets
```bash
#!/bin/bash
# File: scripts/rolling-migration.sh

# For migrations affecting large tables
echo "🐘 Starting rolling migration for large dataset"

# Step 1: Add new columns without constraints
php bin/console doctrine:migrations:execute --up 20231201000001 --no-interaction

# Step 2: Backfill data in batches
php bin/console app:migrate:backfill-users --batch-size=1000

# Step 3: Add constraints
php bin/console doctrine:migrations:execute --up 20231201000002 --no-interaction

# Step 4: Remove old columns
php bin/console doctrine:migrations:execute --up 20231201000003 --no-interaction

echo "✅ Rolling migration completed"
```

### Database Schema Validation

#### Pre-Migration Checks
```bash
#!/bin/bash
# File: scripts/pre-migration-checks.sh

echo "🔍 Running pre-migration checks..."

# Check current schema state
php bin/console doctrine:schema:validate --skip-sync

# Check for pending migrations
PENDING=$(php bin/console doctrine:migrations:status --show-versions | grep "not migrated" | wc -l)
echo "📊 Pending migrations: $PENDING"

# Check database connectivity
php bin/console doctrine:query:sql "SELECT 1" > /dev/null
echo "✅ Database connectivity verified"

# Check available disk space
AVAILABLE_SPACE=$(df -h | awk '$NF=="/"{printf "%s", $4}')
echo "💾 Available disk space: $AVAILABLE_SPACE"

if [ $PENDING -eq 0 ]; then
    echo "✅ No pending migrations"
    exit 0
else
    echo "⚠️ Found $PENDING pending migrations"
    php bin/console doctrine:migrations:list --show-versions
fi
```

#### Post-Migration Validation
```bash
#!/bin/bash
# File: scripts/post-migration-validation.sh

echo "🔍 Running post-migration validation..."

# Validate schema integrity
php bin/console doctrine:schema:validate

# Check data integrity
php bin/console app:validate:data-integrity

# Verify application functionality
curl -f http://localhost:8080/health || exit 1
echo "✅ Health check passed"

# Run critical tests
php bin/phpunit tests/Integration/DatabaseTest.php
echo "✅ Database tests passed"

echo "✅ Post-migration validation completed"
```

### Environment-Specific Migration Commands

#### DigitalOcean App Platform
```bash
# In app.yaml, add migration command to run_command
run_command: |
  php bin/console doctrine:migrations:migrate --no-interaction
  php-fpm

# Or as a separate job
jobs:
- name: migrate
  source_dir: /
  run_command: php bin/console doctrine:migrations:migrate --no-interaction
  instance_count: 1
  instance_size_slug: basic-xxs
```

#### Google Cloud Run
```yaml
# cloud-run-migration.yaml
apiVersion: run.googleapis.com/v1
kind: Job
metadata:
  name: warehouse-migrate
spec:
  spec:
    template:
      spec:
        template:
          spec:
            containers:
            - image: gcr.io/project/warehouse-app:latest
              command: ["php", "bin/console", "doctrine:migrations:migrate", "--no-interaction"]
              env:
              - name: DATABASE_URL
                valueFrom:
                  secretKeyRef:
                    name: app-secrets
                    key: database-url
```

#### Kubernetes
```yaml
# migration-job.yaml
apiVersion: batch/v1
kind: Job
metadata:
  name: warehouse-migration
  namespace: warehouse
spec:
  template:
    spec:
      restartPolicy: Never
      containers:
      - name: migrate
        image: your-registry/warehouse-app:latest
        command: ["php", "bin/console", "doctrine:migrations:migrate", "--no-interaction"]
        envFrom:
        - configMapRef:
            name: warehouse-config
        - secretRef:
            name: warehouse-secrets
```

## 📡 Redis Migration Strategies

### Redis Data Migration Approaches

#### 1. Redis Replication Setup
```bash
#!/bin/bash
# File: scripts/redis-migration.sh

OLD_REDIS="redis://old-host:6379"
NEW_REDIS="redis://new-host:6379"

echo "🔄 Starting Redis migration"

# Method 1: Using redis-cli with --rdb
redis-cli --rdb dump.rdb --host old-host --port 6379

# Import to new Redis
redis-cli --pipe --host new-host --port 6379 < dump.rdb

# Method 2: Using SYNC replication
redis-cli -h new-host -p 6379 SLAVEOF old-host 6379

# Wait for sync to complete
while [ $(redis-cli -h new-host -p 6379 LASTSAVE) -eq $(redis-cli -h new-host -p 6379 LASTSAVE) ]; do
    sleep 1
done

# Stop replication
redis-cli -h new-host -p 6379 SLAVEOF NO ONE

echo "✅ Redis migration completed"
```

#### 2. Application-Level Redis Migration
```php
<?php
// File: src/Command/RedisMigrateCommand.php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:redis:migrate')]
class RedisMigrateCommand extends Command
{
    public function __construct(
        private \Redis $oldRedis,
        private \Redis $newRedis,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Redis Migration');
        
        // Get all keys from old Redis
        $keys = $this->oldRedis->keys('*');
        $total = count($keys);
        
        $io->progressStart($total);
        
        foreach ($keys as $key) {
            $type = $this->oldRedis->type($key);
            $ttl = $this->oldRedis->ttl($key);
            
            switch ($type) {
                case \Redis::REDIS_STRING:
                    $value = $this->oldRedis->get($key);
                    $this->newRedis->set($key, $value);
                    break;
                    
                case \Redis::REDIS_HASH:
                    $hash = $this->oldRedis->hGetAll($key);
                    $this->newRedis->hMSet($key, $hash);
                    break;
                    
                case \Redis::REDIS_LIST:
                    $list = $this->oldRedis->lRange($key, 0, -1);
                    foreach ($list as $item) {
                        $this->newRedis->rPush($key, $item);
                    }
                    break;
                    
                case \Redis::REDIS_SET:
                    $set = $this->oldRedis->sMembers($key);
                    foreach ($set as $member) {
                        $this->newRedis->sAdd($key, $member);
                    }
                    break;
                    
                case \Redis::REDIS_ZSET:
                    $zset = $this->oldRedis->zRange($key, 0, -1, true);
                    foreach ($zset as $member => $score) {
                        $this->newRedis->zAdd($key, $score, $member);
                    }
                    break;
            }
            
            // Set TTL if exists
            if ($ttl > 0) {
                $this->newRedis->expire($key, $ttl);
            }
            
            $io->progressAdvance();
        }
        
        $io->progressFinish();
        $io->success(sprintf('Migrated %d keys successfully', $total));
        
        return Command::SUCCESS;
    }
}
```

### Session Migration Strategy

#### Symfony Session Migration
```yaml
# config/packages/framework.yaml
framework:
    session:
        handler_id: Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler
        cookie_secure: auto
        cookie_samesite: lax
        storage_factory_id: session.storage.factory.native
        
# config/services.yaml
services:
    Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler:
        arguments:
            - '@Redis'
            - prefix: 'warehouse_session:'
            - ttl: 3600
```

#### Gradual Session Migration
```bash
#!/bin/bash
# File: scripts/session-migration.sh

echo "🔄 Starting session migration"

# Step 1: Configure dual Redis writers (temporary)
# Update application to write to both old and new Redis

# Step 2: Migrate existing sessions
php bin/console app:redis:migrate --keys-pattern="warehouse_session:*"

# Step 3: Switch to new Redis only
# Update configuration to use only new Redis

# Step 4: Cleanup old sessions
redis-cli -h old-host DEL warehouse_session:*

echo "✅ Session migration completed"
```

## 🔄 Migration Rollback Strategies

### Database Rollback
```bash
#!/bin/bash
# File: scripts/rollback-database.sh

BACKUP_FILE=$1

if [ -z "$BACKUP_FILE" ]; then
    echo "❌ Please provide backup file"
    echo "Usage: $0 backup_file.sql"
    exit 1
fi

echo "⚠️ Starting database rollback"
echo "📦 Backup file: $BACKUP_FILE"

# Confirm rollback
read -p "Are you sure you want to rollback? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "❌ Rollback cancelled"
    exit 1
fi

# Stop application
docker-compose stop app

# Restore database
echo "🔄 Restoring database from backup..."
psql $DATABASE_URL < $BACKUP_FILE

# Start application
docker-compose start app

echo "✅ Database rollback completed"
```

### Redis Rollback
```bash
#!/bin/bash
# File: scripts/rollback-redis.sh

BACKUP_FILE=$1

echo "⚠️ Starting Redis rollback"

# Stop application to prevent writes
docker-compose stop app

# Flush current Redis
redis-cli FLUSHALL

# Restore from backup
redis-cli --pipe < $BACKUP_FILE

# Start application
docker-compose start app

echo "✅ Redis rollback completed"
```

## 📊 Migration Monitoring

### Database Migration Monitoring
```bash
#!/bin/bash
# File: scripts/monitor-migration.sh

START_TIME=$(date +%s)

echo "📊 Starting migration monitoring"

# Monitor database connections
watch -n 5 'psql -c "SELECT count(*) as connections FROM pg_stat_activity;"'

# Monitor migration progress
tail -f var/log/migration.log &

# Monitor application health
while true; do
    RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/health)
    if [ "$RESPONSE" -eq 200 ]; then
        echo "✅ Application healthy"
    else
        echo "❌ Application unhealthy: $RESPONSE"
    fi
    sleep 30
done
```

### Migration Metrics
```php
<?php
// File: src/EventListener/MigrationMetricsListener.php

namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Event\PostConnectEventArgs;
use Doctrine\Migrations\Event\MigrationsEvent;

class MigrationMetricsListener implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            'postConnect',
            'onMigrationExecuted',
        ];
    }

    public function postConnect(PostConnectEventArgs $args): void
    {
        // Log database connection
        error_log('Database connected: ' . date('Y-m-d H:i:s'));
    }

    public function onMigrationExecuted(MigrationsEvent $event): void
    {
        $migration = $event->getMigration();
        $direction = $event->getDirection();
        
        error_log(sprintf(
            'Migration %s executed %s in %s seconds',
            $migration->getVersion(),
            $direction,
            $event->getTime()
        ));
    }
}
```

This comprehensive database migration strategy covers all aspects of safely migrating PostgreSQL databases and Redis data across different deployment environments while maintaining data integrity and minimizing downtime.