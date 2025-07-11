# Cost Optimization Strategies

## 💰 Cost Analysis Framework

### Total Cost of Ownership (TCO) Components
1. **Compute Costs** - CPU, memory, container/VM instances
2. **Storage Costs** - Database storage, backups, logs
3. **Network Costs** - Data transfer, CDN, load balancing
4. **Management Costs** - Monitoring, security, maintenance
5. **Hidden Costs** - Development time, support, scaling

## 📊 Resource Sizing Guidelines

### Right-Sizing Your Application

#### PHP-FPM Configuration
```ini
# File: docker/php/php-fpm.conf

[global]
; Optimize for your expected load
process_control_timeout = 10
emergency_restart_threshold = 10
emergency_restart_interval = 1m

[www]
; Dynamic management
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500

; Memory optimization
pm.process_idle_timeout = 30s
request_terminate_timeout = 60

; Status monitoring
pm.status_path = /status
ping.path = /ping
```

#### Memory and CPU Recommendations

**Starter Configuration (Low Traffic: <1000 requests/day)**
```yaml
# Resource allocation
app:
  memory: 256Mi
  cpu: 250m
  replicas: 1

database:
  memory: 512Mi
  cpu: 500m
  storage: 10Gi

redis:
  memory: 128Mi
  cpu: 100m
  storage: 1Gi

# Monthly cost estimate: $30-60
```

**Production Configuration (Medium Traffic: 10K-100K requests/day)**
```yaml
# Resource allocation
app:
  memory: 512Mi
  cpu: 500m
  replicas: 2-3

database:
  memory: 2Gi
  cpu: 1000m
  storage: 50Gi

redis:
  memory: 256Mi
  cpu: 200m
  storage: 5Gi

# Monthly cost estimate: $100-300
```

**High-Scale Configuration (High Traffic: 1M+ requests/day)**
```yaml
# Resource allocation
app:
  memory: 1Gi
  cpu: 1000m
  replicas: 5-10

database:
  memory: 8Gi
  cpu: 4000m
  storage: 200Gi

redis:
  memory: 1Gi
  cpu: 500m
  storage: 20Gi

# Monthly cost estimate: $500-2000
```

### Database Optimization

#### PostgreSQL Performance Tuning
```sql
-- File: database/performance-tuning.sql

-- Connection optimization
ALTER SYSTEM SET max_connections = 100;
ALTER SYSTEM SET shared_buffers = '256MB';
ALTER SYSTEM SET effective_cache_size = '1GB';
ALTER SYSTEM SET maintenance_work_mem = '64MB';
ALTER SYSTEM SET checkpoint_completion_target = 0.9;
ALTER SYSTEM SET wal_buffers = '16MB';
ALTER SYSTEM SET default_statistics_target = 100;

-- Query optimization
ALTER SYSTEM SET random_page_cost = 1.1;
ALTER SYSTEM SET effective_io_concurrency = 200;

-- Logging (only for debugging, disable in production)
ALTER SYSTEM SET log_min_duration_statement = 1000;
ALTER SYSTEM SET log_checkpoints = on;
ALTER SYSTEM SET log_connections = on;
ALTER SYSTEM SET log_disconnections = on;

SELECT pg_reload_conf();
```

#### Redis Optimization
```redis
# File: redis/redis-optimized.conf

# Memory optimization
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence optimization (for sessions/cache)
save 900 1
save 300 10
save 60 10000

# Network optimization
tcp-keepalive 300
timeout 300

# Disable expensive operations
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command DEBUG ""

# Enable compression
rdbcompression yes
```

## 🔄 Auto-Scaling Strategies

### Horizontal Pod Autoscaler (Kubernetes)
```yaml
# File: deployment/kubernetes/hpa.yaml
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: warehouse-hpa
  namespace: warehouse
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: warehouse-app
  minReplicas: 2
  maxReplicas: 10
  metrics:
  - type: Resource
    resource:
      name: cpu
      target:
        type: Utilization
        averageUtilization: 70
  - type: Resource
    resource:
      name: memory
      target:
        type: Utilization
        averageUtilization: 80
  behavior:
    scaleDown:
      stabilizationWindowSeconds: 300
      policies:
      - type: Percent
        value: 10
        periodSeconds: 60
    scaleUp:
      stabilizationWindowSeconds: 60
      policies:
      - type: Percent
        value: 50
        periodSeconds: 60
```

### Application-Level Auto-Scaling
```php
<?php
// File: src/Service/ResourceMonitor.php

namespace App\Service;

use Psr\Log\LoggerInterface;

class ResourceMonitor
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function checkResourceUsage(): array
    {
        $metrics = [
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_load' => $this->getCpuLoad(),
            'active_connections' => $this->getActiveConnections(),
            'queue_size' => $this->getQueueSize()
        ];

        $this->logMetrics($metrics);

        return $metrics;
    }

    private function getMemoryUsage(): float
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        return ($memoryUsage / $memoryLimit) * 100;
    }

    private function getCpuLoad(): float
    {
        $load = sys_getloadavg();
        return $load[0]; // 1-minute load average
    }

    private function getActiveConnections(): int
    {
        // Implement based on your connection pooling
        return 0;
    }

    private function getQueueSize(): int
    {
        // Implement based on your message queue
        return 0;
    }

    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit)-1]);
        
        switch($last) {
            case 'g': $limit *= 1024;
            case 'm': $limit *= 1024;
            case 'k': $limit *= 1024;
        }
        
        return (int) $limit;
    }

    private function logMetrics(array $metrics): void
    {
        $this->logger->info('Resource usage', $metrics);
    }
}
```

## 💲 Platform-Specific Cost Optimization

### AWS Cost Optimization
```yaml
# File: deployment/aws/cost-optimized.yaml

# Use Spot Instances for non-critical workloads
apiVersion: apps/v1
kind: Deployment
metadata:
  name: warehouse-worker
spec:
  template:
    spec:
      nodeSelector:
        node.kubernetes.io/instance-type: t3.medium
        lifecycle: Ec2Spot
      tolerations:
      - key: spotInstance
        operator: Equal
        value: "true"
        effect: NoSchedule

---
# Use ARM-based instances (Graviton2)
apiVersion: apps/v1
kind: Deployment
metadata:
  name: warehouse-app
spec:
  template:
    spec:
      nodeSelector:
        kubernetes.io/arch: arm64
        node.kubernetes.io/instance-type: t4g.medium

---
# Cost-optimized storage classes
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: warehouse-data
spec:
  storageClassName: gp3  # More cost-effective than gp2
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 20Gi
```

### Google Cloud Cost Optimization
```yaml
# File: deployment/gcp/cost-optimized.yaml

# Use preemptible instances
apiVersion: apps/v1
kind: Deployment
metadata:
  name: warehouse-worker
spec:
  template:
    spec:
      nodeSelector:
        cloud.google.com/gke-preemptible: "true"
      tolerations:
      - key: cloud.google.com/gke-preemptible
        operator: Equal
        value: "true"
        effect: NoSchedule

---
# Cloud Run cost optimization
apiVersion: serving.knative.dev/v1
kind: Service
metadata:
  name: warehouse-app
  annotations:
    # Scale to zero when not in use
    autoscaling.knative.dev/minScale: "0"
    autoscaling.knative.dev/maxScale: "100"
    # Use minimum CPU allocation
    run.googleapis.com/cpu-throttling: "true"
spec:
  template:
    metadata:
      annotations:
        # Reduce cold start time
        run.googleapis.com/execution-environment: gen2
    spec:
      containerConcurrency: 100
      containers:
      - image: gcr.io/project/warehouse-app
        resources:
          limits:
            # Optimize for cost
            memory: 512Mi
            cpu: 1000m
```

### DigitalOcean Cost Optimization
```yaml
# File: deployment/digitalocean/cost-optimized.yaml

name: warehouse-app
services:
- name: web
  instance_count: 1  # Start small
  instance_size_slug: basic-xxs  # $12/month
  
  # Auto-scaling configuration
  autoscaling:
    min_instance_count: 1
    max_instance_count: 5
    metrics:
      cpu:
        percent: 80
      memory:
        percent: 80

databases:
# Use development-tier databases initially
- engine: PG
  name: warehouse-db
  size: db-s-dev-database  # $15/month
  num_nodes: 1

- engine: REDIS
  name: warehouse-redis
  size: db-s-dev-database  # $15/month
  num_nodes: 1

# Total starting cost: ~$42/month
```

## 🔧 Application-Level Optimizations

### Caching Strategy
```php
<?php
// File: src/Service/OptimizedCacheService.php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class OptimizedCacheService
{
    public function __construct(
        private TagAwareCacheInterface $cache
    ) {}

    public function getCachedData(string $key, callable $callback, int $ttl = 3600, array $tags = []): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($callback, $ttl, $tags) {
            $item->expiresAfter($ttl);
            
            if (!empty($tags)) {
                $item->tag($tags);
            }
            
            return $callback();
        });
    }

    public function invalidateByTags(array $tags): void
    {
        $this->cache->invalidateTags($tags);
    }

    // Implement cache warming for expensive operations
    public function warmCache(): void
    {
        $expensiveOperations = [
            'user_stats' => fn() => $this->calculateUserStats(),
            'popular_items' => fn() => $this->getPopularItems(),
            'dashboard_metrics' => fn() => $this->getDashboardMetrics(),
        ];

        foreach ($expensiveOperations as $key => $operation) {
            $this->getCachedData($key, $operation, 3600, ['warm_cache']);
        }
    }

    private function calculateUserStats(): array
    {
        // Expensive database operation
        return [];
    }

    private function getPopularItems(): array
    {
        // Expensive database operation
        return [];
    }

    private function getDashboardMetrics(): array
    {
        // Expensive calculation
        return [];
    }
}
```

### Database Query Optimization
```php
<?php
// File: src/Repository/OptimizedUserRepository.php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class OptimizedUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // Use pagination to avoid memory issues
    public function findPaginated(int $page = 1, int $limit = 20): Paginator
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb->getQuery());
    }

    // Use specific selects to reduce data transfer
    public function findUserSummaries(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.id, u.email, u.createdAt')
            ->getQuery()
            ->getArrayResult();
    }

    // Use joins efficiently
    public function findUsersWithProfiles(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.profile', 'p')
            ->addSelect('p')
            ->where('u.active = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    // Batch processing for large datasets
    public function processBatch(callable $processor, int $batchSize = 100): void
    {
        $qb = $this->createQueryBuilder('u');
        $totalUsers = $qb->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();
        
        for ($offset = 0; $offset < $totalUsers; $offset += $batchSize) {
            $users = $this->createQueryBuilder('u')
                ->setFirstResult($offset)
                ->setMaxResults($batchSize)
                ->getQuery()
                ->getResult();
            
            $processor($users);
            
            // Clear entity manager to free memory
            $this->getEntityManager()->clear();
        }
    }
}
```

### Asset Optimization
```yaml
# File: config/packages/webpack_encore.yaml
webpack_encore:
    output_path: '%kernel.project_dir%/public/build'
    builds:
        app: '%kernel.project_dir%/public/build'
    
    # Enable built-in optimizations
    cache: true
    
when@prod:
    webpack_encore:
        # Production optimizations
        builds:
            app:
                # Enable production mode optimizations
                integrity: true
                # Preload critical resources
                preload: ['/build/app.css', '/build/app.js']
                # DNS prefetch for external resources
                dns_prefetch: ['//fonts.googleapis.com']
```

## 📈 Cost Monitoring and Alerting

### Cost Tracking Script
```bash
#!/bin/bash
# File: scripts/cost-monitor.sh

ENVIRONMENT=${1:-production}
ALERT_THRESHOLD=${2:-100} # Alert if monthly cost exceeds $100

echo "💰 Monitoring costs for $ENVIRONMENT environment"

# AWS cost monitoring
if command -v aws &> /dev/null; then
    COST=$(aws ce get-cost-and-usage \
        --time-period Start=2023-01-01,End=2023-12-31 \
        --granularity MONTHLY \
        --metrics BlendedCost \
        --query 'ResultsByTime[0].Total.BlendedCost.Amount' \
        --output text)
    
    echo "📊 AWS Monthly Cost: $COST USD"
    
    if (( $(echo "$COST > $ALERT_THRESHOLD" | bc -l) )); then
        echo "⚠️ Cost alert: Monthly cost ($COST) exceeds threshold ($ALERT_THRESHOLD)"
        # Send alert
        ./scripts/alert-to-slack.sh "warning" "Monthly AWS cost ($COST USD) exceeds threshold ($ALERT_THRESHOLD USD)"
    fi
fi

# DigitalOcean cost monitoring (via API)
if [ ! -z "$DO_API_TOKEN" ]; then
    COST=$(curl -s -H "Authorization: Bearer $DO_API_TOKEN" \
        "https://api.digitalocean.com/v2/customers/my/billing_history" | \
        jq -r '.billing_history[0].amount')
    
    echo "📊 DigitalOcean Monthly Cost: $COST USD"
fi

# Resource utilization check
echo "📊 Current resource utilization:"
docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.MemPerc}}"
```

### Cost Optimization Recommendations
```php
<?php
// File: src/Command/CostOptimizationCommand.php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:cost:analyze')]
class CostOptimizationCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Cost Optimization Analysis');
        
        $recommendations = $this->analyzeCosts();
        
        foreach ($recommendations as $category => $items) {
            $io->section($category);
            $io->listing($items);
        }
        
        return Command::SUCCESS;
    }

    private function analyzeCosts(): array
    {
        $recommendations = [];
        
        // Memory usage analysis
        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // MB
        if ($memoryUsage > 200) {
            $recommendations['Memory Optimization'][] = 
                "High memory usage detected ({$memoryUsage}MB). Consider optimizing queries or implementing pagination.";
        }

        // Database query analysis
        $slowQueries = $this->getSlowQueries();
        if (count($slowQueries) > 0) {
            $recommendations['Database Optimization'][] = 
                sprintf("Found %d slow queries. Consider adding indexes or optimizing SQL.", count($slowQueries));
        }

        // Cache hit ratio analysis
        $cacheHitRatio = $this->getCacheHitRatio();
        if ($cacheHitRatio < 0.8) {
            $recommendations['Caching'][] = 
                sprintf("Low cache hit ratio (%.2f%%). Consider implementing more aggressive caching.", $cacheHitRatio * 100);
        }

        // Unused resources
        $unusedResources = $this->findUnusedResources();
        if (!empty($unusedResources)) {
            $recommendations['Resource Cleanup'][] = 
                "Found unused resources that can be removed to save costs.";
        }

        return $recommendations;
    }

    private function getSlowQueries(): array
    {
        // Implement slow query detection
        return [];
    }

    private function getCacheHitRatio(): float
    {
        // Implement cache hit ratio calculation
        return 0.85;
    }

    private function findUnusedResources(): array
    {
        // Implement unused resource detection
        return [];
    }
}
```

## 💡 Best Practices Summary

### 1. Start Small, Scale Smart
- Begin with minimal resources and scale based on actual usage
- Use monitoring to identify bottlenecks before scaling
- Implement auto-scaling to handle traffic spikes efficiently

### 2. Optimize Database Usage
- Use connection pooling to reduce database overhead
- Implement query optimization and proper indexing
- Use read replicas for read-heavy workloads

### 3. Leverage Caching
- Implement multi-layer caching (application, database, HTTP)
- Use cache warming for predictable loads
- Set appropriate TTL values to balance freshness and performance

### 4. Monitor and Alert
- Set up cost alerts to prevent surprise bills
- Monitor resource utilization continuously
- Regular cost reviews and optimization cycles

### 5. Platform-Specific Optimizations
- Use platform-native features (auto-scaling, managed services)
- Take advantage of cost-saving options (spot instances, preemptible VMs)
- Choose the right instance types for your workload

This cost optimization guide helps you achieve the best performance-to-cost ratio for your Symfony application across all deployment platforms.