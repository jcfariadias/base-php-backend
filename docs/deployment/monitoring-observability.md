# Monitoring and Observability

## 📊 Monitoring Stack Overview

### Core Components
1. **Application Metrics** - Performance, errors, business metrics
2. **Infrastructure Metrics** - CPU, memory, disk, network
3. **Log Aggregation** - Centralized logging and analysis
4. **Health Checks** - Service availability monitoring
5. **Alerting** - Proactive issue notification
6. **Distributed Tracing** - Request flow analysis

## 🔍 Application Monitoring

### Health Check Endpoint Implementation
```php
<?php
// File: src/Controller/HealthController.php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends AbstractController
{
    public function __construct(
        private Connection $connection,
        private CacheItemPoolInterface $cache,
        private \Redis $redis
    ) {}

    #[Route('/health', name: 'health_check', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        $checks = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'version' => $_ENV['APP_VERSION'] ?? 'unknown',
            'environment' => $_ENV['APP_ENV'] ?? 'unknown',
            'checks' => []
        ];

        // Database check
        try {
            $this->connection->executeQuery('SELECT 1');
            $checks['checks']['database'] = ['status' => 'healthy'];
        } catch (\Exception $e) {
            $checks['checks']['database'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
            $checks['status'] = 'unhealthy';
        }

        // Redis check
        try {
            $this->redis->ping();
            $checks['checks']['redis'] = ['status' => 'healthy'];
        } catch (\Exception $e) {
            $checks['checks']['redis'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
            $checks['status'] = 'unhealthy';
        }

        // Cache check
        try {
            $item = $this->cache->getItem('health_check');
            $item->set('ok');
            $this->cache->save($item);
            $checks['checks']['cache'] = ['status' => 'healthy'];
        } catch (\Exception $e) {
            $checks['checks']['cache'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }

        $httpStatus = $checks['status'] === 'healthy' ? 
            Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return new JsonResponse($checks, $httpStatus);
    }

    #[Route('/health/deep', name: 'health_deep_check', methods: ['GET'])]
    public function deepHealthCheck(): JsonResponse
    {
        $checks = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'detailed_checks' => []
        ];

        // Database detailed check
        try {
            $result = $this->connection->executeQuery('SELECT COUNT(*) as count FROM users')->fetchAssociative();
            $checks['detailed_checks']['database'] = [
                'status' => 'healthy',
                'response_time' => $this->measureResponseTime(fn() => $this->connection->executeQuery('SELECT 1')),
                'user_count' => $result['count']
            ];
        } catch (\Exception $e) {
            $checks['detailed_checks']['database'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
            $checks['status'] = 'unhealthy';
        }

        // Redis detailed check
        try {
            $info = $this->redis->info();
            $checks['detailed_checks']['redis'] = [
                'status' => 'healthy',
                'response_time' => $this->measureResponseTime(fn() => $this->redis->ping()),
                'memory_usage' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 'unknown'
            ];
        } catch (\Exception $e) {
            $checks['detailed_checks']['redis'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
            $checks['status'] = 'unhealthy';
        }

        // Disk space check
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsagePercent = (($diskTotal - $diskFree) / $diskTotal) * 100;

        $checks['detailed_checks']['disk'] = [
            'status' => $diskUsagePercent > 90 ? 'warning' : 'healthy',
            'usage_percent' => round($diskUsagePercent, 2),
            'free_space' => $this->formatBytes($diskFree),
            'total_space' => $this->formatBytes($diskTotal)
        ];

        $httpStatus = $checks['status'] === 'healthy' ? 
            Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return new JsonResponse($checks, $httpStatus);
    }

    private function measureResponseTime(callable $callback): float
    {
        $start = microtime(true);
        $callback();
        return round((microtime(true) - $start) * 1000, 2); // milliseconds
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
```

### Application Metrics Collection
```php
<?php
// File: src/Service/MetricsCollector.php

namespace App\Service;

use Psr\Log\LoggerInterface;

class MetricsCollector
{
    private array $metrics = [];

    public function __construct(
        private LoggerInterface $metricsLogger
    ) {}

    public function increment(string $metric, array $tags = []): void
    {
        $this->metrics[] = [
            'type' => 'counter',
            'name' => $metric,
            'value' => 1,
            'tags' => $tags,
            'timestamp' => time()
        ];

        $this->flush();
    }

    public function gauge(string $metric, float $value, array $tags = []): void
    {
        $this->metrics[] = [
            'type' => 'gauge',
            'name' => $metric,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => time()
        ];

        $this->flush();
    }

    public function histogram(string $metric, float $value, array $tags = []): void
    {
        $this->metrics[] = [
            'type' => 'histogram',
            'name' => $metric,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => time()
        ];

        $this->flush();
    }

    private function flush(): void
    {
        foreach ($this->metrics as $metric) {
            $this->metricsLogger->info('metric', $metric);
        }
        $this->metrics = [];
    }
}

// File: src/EventListener/MetricsListener.php

namespace App\EventListener;

use App\Service\MetricsCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MetricsListener implements EventSubscriberInterface
{
    private array $requestStartTimes = [];

    public function __construct(
        private MetricsCollector $metrics
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 1000],
            KernelEvents::RESPONSE => ['onResponse', -1000],
            KernelEvents::EXCEPTION => ['onException', -1000],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $this->requestStartTimes[$request] = microtime(true);

        $this->metrics->increment('http.requests.total', [
            'method' => $request->getMethod(),
            'endpoint' => $request->getPathInfo()
        ]);
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if (isset($this->requestStartTimes[$request])) {
            $duration = (microtime(true) - $this->requestStartTimes[$request]) * 1000;
            
            $this->metrics->histogram('http.request.duration', $duration, [
                'method' => $request->getMethod(),
                'status_code' => (string) $response->getStatusCode(),
                'endpoint' => $request->getPathInfo()
            ]);

            unset($this->requestStartTimes[$request]);
        }

        $this->metrics->increment('http.responses.total', [
            'method' => $request->getMethod(),
            'status_code' => (string) $response->getStatusCode(),
            'endpoint' => $request->getPathInfo()
        ]);
    }

    public function onException(ExceptionEvent $event): void
    {
        $this->metrics->increment('http.errors.total', [
            'exception_class' => get_class($event->getThrowable()),
            'endpoint' => $event->getRequest()->getPathInfo()
        ]);
    }
}
```

## 📝 Logging Configuration

### Structured Logging Setup
```yaml
# File: config/packages/monolog.yaml
monolog:
    channels:
        - security
        - metrics
        - business

when@prod:
    monolog:
        handlers:
            main:
                type: rotating_file
                path: /var/log/symfony/app.log
                level: info
                max_files: 30
                formatter: json

            security:
                type: rotating_file
                path: /var/log/symfony/security.log
                level: info
                max_files: 30
                channels: [security]
                formatter: json

            metrics:
                type: rotating_file
                path: /var/log/symfony/metrics.log
                level: info
                max_files: 30
                channels: [metrics]
                formatter: json

            business:
                type: rotating_file
                path: /var/log/symfony/business.log
                level: info
                max_files: 30
                channels: [business]
                formatter: json

            # Send errors to external service
            sentry:
                type: sentry
                level: error
                hub_id: Sentry\State\HubInterface

services:
    monolog.formatter.json:
        class: Monolog\Formatter\JsonFormatter
```

### Custom Log Processor
```php
<?php
// File: src/Logger/RequestProcessor.php

namespace App\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestProcessor implements ProcessorInterface
{
    public function __construct(
        private RequestStack $requestStack
    ) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if ($request) {
            $record->extra['request'] = [
                'id' => $request->headers->get('X-Request-ID') ?? uniqid(),
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ];
        }

        $record->extra['environment'] = $_ENV['APP_ENV'] ?? 'unknown';
        $record->extra['hostname'] = gethostname();
        
        return $record;
    }
}
```

## 🐳 Container Monitoring

### Docker Compose with Monitoring Stack
```yaml
# File: deployment/docker-compose/monitoring.yml
version: '3.8'

services:
  # Your application services here...

  prometheus:
    image: prom/prometheus:latest
    ports:
      - "9090:9090"
    volumes:
      - ./prometheus:/etc/prometheus
      - prometheus_data:/prometheus
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.path=/prometheus'
      - '--web.console.libraries=/etc/prometheus/console_libraries'
      - '--web.console.templates=/etc/prometheus/consoles'
      - '--storage.tsdb.retention.time=200h'
      - '--web.enable-lifecycle'

  grafana:
    image: grafana/grafana:latest
    ports:
      - "3000:3000"
    volumes:
      - grafana_data:/var/lib/grafana
      - ./grafana/provisioning:/etc/grafana/provisioning
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin

  node-exporter:
    image: prom/node-exporter:latest
    ports:
      - "9100:9100"
    volumes:
      - /proc:/host/proc:ro
      - /sys:/host/sys:ro
      - /:/rootfs:ro
    command:
      - '--path.procfs=/host/proc'
      - '--path.rootfs=/rootfs'
      - '--path.sysfs=/host/sys'
      - '--collector.filesystem.mount-points-exclude=^/(sys|proc|dev|host|etc)($$|/)'

  cadvisor:
    image: gcr.io/cadvisor/cadvisor:latest
    ports:
      - "8080:8080"
    volumes:
      - /:/rootfs:ro
      - /var/run:/var/run:rw
      - /sys:/sys:ro
      - /var/lib/docker/:/var/lib/docker:ro
      - /dev/disk/:/dev/disk:ro

  loki:
    image: grafana/loki:latest
    ports:
      - "3100:3100"
    volumes:
      - ./loki:/etc/loki
      - loki_data:/loki
    command: -config.file=/etc/loki/local-config.yaml

  promtail:
    image: grafana/promtail:latest
    volumes:
      - ./promtail:/etc/promtail
      - /var/log:/var/log:ro
      - /var/lib/docker/containers:/var/lib/docker/containers:ro
    command: -config.file=/etc/promtail/config.yml

volumes:
  prometheus_data:
  grafana_data:
  loki_data:
```

### Prometheus Configuration
```yaml
# File: prometheus/prometheus.yml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

rule_files:
  - "alert_rules.yml"

alerting:
  alertmanagers:
    - static_configs:
        - targets:
          - alertmanager:9093

scrape_configs:
  - job_name: 'warehouse-app'
    static_configs:
      - targets: ['app:8080']
    metrics_path: '/metrics'
    scrape_interval: 10s

  - job_name: 'node-exporter'
    static_configs:
      - targets: ['node-exporter:9100']

  - job_name: 'cadvisor'
    static_configs:
      - targets: ['cadvisor:8080']

  - job_name: 'postgres'
    static_configs:
      - targets: ['postgres-exporter:9187']

  - job_name: 'redis'
    static_configs:
      - targets: ['redis-exporter:9121']
```

### Alert Rules
```yaml
# File: prometheus/alert_rules.yml
groups:
- name: warehouse.rules
  rules:
  - alert: HighMemoryUsage
    expr: container_memory_usage_bytes / container_spec_memory_limit_bytes > 0.8
    for: 5m
    labels:
      severity: warning
    annotations:
      summary: "High memory usage detected"
      description: "Container {{ $labels.name }} memory usage is above 80%"

  - alert: HighCPUUsage
    expr: rate(container_cpu_usage_seconds_total[5m]) > 0.8
    for: 5m
    labels:
      severity: warning
    annotations:
      summary: "High CPU usage detected"
      description: "Container {{ $labels.name }} CPU usage is above 80%"

  - alert: DatabaseDown
    expr: up{job="postgres"} == 0
    for: 1m
    labels:
      severity: critical
    annotations:
      summary: "Database is down"
      description: "PostgreSQL database is not responding"

  - alert: HighErrorRate
    expr: rate(http_requests_total{status=~"5.."}[5m]) / rate(http_requests_total[5m]) > 0.05
    for: 5m
    labels:
      severity: critical
    annotations:
      summary: "High error rate detected"
      description: "Error rate is above 5% for the last 5 minutes"

  - alert: ResponseTimeHigh
    expr: histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m])) > 1
    for: 5m
    labels:
      severity: warning
    annotations:
      summary: "High response time"
      description: "95th percentile response time is above 1 second"
```

## ☁️ Cloud Platform Monitoring

### AWS CloudWatch Configuration
```yaml
# File: deployment/aws/cloudwatch-config.yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: cloudwatch-config
data:
  cwagentconfig.json: |
    {
      "agent": {
        "metrics_collection_interval": 60,
        "run_as_user": "cwagent"
      },
      "metrics": {
        "namespace": "Warehouse/Application",
        "metrics_collected": {
          "cpu": {
            "measurement": ["cpu_usage_idle", "cpu_usage_iowait", "cpu_usage_user", "cpu_usage_system"],
            "metrics_collection_interval": 60
          },
          "disk": {
            "measurement": ["used_percent"],
            "metrics_collection_interval": 60,
            "resources": ["*"]
          },
          "mem": {
            "measurement": ["mem_used_percent"],
            "metrics_collection_interval": 60
          }
        }
      },
      "logs": {
        "logs_collected": {
          "files": {
            "collect_list": [
              {
                "file_path": "/var/log/symfony/app.log",
                "log_group_name": "/warehouse/application",
                "log_stream_name": "{instance_id}/app.log"
              },
              {
                "file_path": "/var/log/symfony/security.log",
                "log_group_name": "/warehouse/security",
                "log_stream_name": "{instance_id}/security.log"
              }
            ]
          }
        }
      }
    }
```

### Google Cloud Monitoring
```yaml
# File: deployment/gcp/monitoring.yaml
apiVersion: apps/v1
kind: DaemonSet
metadata:
  name: google-cloud-ops-agent
spec:
  selector:
    matchLabels:
      app: google-cloud-ops-agent
  template:
    metadata:
      labels:
        app: google-cloud-ops-agent
    spec:
      containers:
      - name: ops-agent
        image: gcr.io/google-cloud-ops-agents-artifacts/ops-agent:latest
        env:
        - name: GOOGLE_CLOUD_PROJECT
          value: "your-project-id"
        volumeMounts:
        - name: varlog
          mountPath: /var/log
          readOnly: true
        - name: config
          mountPath: /etc/google-cloud-ops-agent
      volumes:
      - name: varlog
        hostPath:
          path: /var/log
      - name: config
        configMap:
          name: ops-agent-config
```

## 📱 Alerting Configuration

### Slack Notifications
```bash
#!/bin/bash
# File: scripts/alert-to-slack.sh

SLACK_WEBHOOK_URL="your-slack-webhook-url"
ALERT_TYPE=$1
MESSAGE=$2
ENVIRONMENT=${3:-production}

send_slack_alert() {
    local color="danger"
    local icon=":rotating_light:"
    
    case $ALERT_TYPE in
        "warning") color="warning"; icon=":warning:" ;;
        "info") color="good"; icon=":information_source:" ;;
        "critical") color="danger"; icon=":fire:" ;;
    esac

    curl -X POST -H 'Content-type: application/json' \
        --data "{
            \"attachments\": [
                {
                    \"color\": \"$color\",
                    \"title\": \"Warehouse Alert - $ENVIRONMENT\",
                    \"text\": \"$MESSAGE\",
                    \"footer\": \"Warehouse Monitoring\",
                    \"ts\": $(date +%s)
                }
            ],
            \"icon_emoji\": \"$icon\",
            \"username\": \"Warehouse Bot\"
        }" \
        $SLACK_WEBHOOK_URL
}

send_slack_alert
```

### Email Alerts
```php
<?php
// File: src/Service/AlertService.php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class AlertService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $alertEmail
    ) {}

    public function sendCriticalAlert(string $subject, string $message, array $context = []): void
    {
        $email = (new Email())
            ->from('alerts@warehouse.com')
            ->to($this->alertEmail)
            ->priority(Email::PRIORITY_HIGH)
            ->subject("[CRITICAL] $subject")
            ->html($this->buildAlertEmail($message, $context, 'critical'));

        $this->mailer->send($email);
    }

    public function sendWarningAlert(string $subject, string $message, array $context = []): void
    {
        $email = (new Email())
            ->from('alerts@warehouse.com')
            ->to($this->alertEmail)
            ->subject("[WARNING] $subject")
            ->html($this->buildAlertEmail($message, $context, 'warning'));

        $this->mailer->send($email);
    }

    private function buildAlertEmail(string $message, array $context, string $severity): string
    {
        $color = $severity === 'critical' ? '#ff0000' : '#ffa500';
        
        return sprintf('
            <html>
            <body style="font-family: Arial, sans-serif;">
                <div style="background-color: %s; color: white; padding: 20px; border-radius: 5px;">
                    <h2>%s Alert</h2>
                    <p><strong>Message:</strong> %s</p>
                    <p><strong>Time:</strong> %s</p>
                    <p><strong>Environment:</strong> %s</p>
                    %s
                </div>
            </body>
            </html>
        ', 
            $color,
            strtoupper($severity),
            htmlspecialchars($message),
            date('Y-m-d H:i:s'),
            $_ENV['APP_ENV'] ?? 'unknown',
            $this->formatContext($context)
        );
    }

    private function formatContext(array $context): string
    {
        if (empty($context)) {
            return '';
        }

        $html = '<p><strong>Context:</strong></p><ul>';
        foreach ($context as $key => $value) {
            $html .= sprintf('<li><strong>%s:</strong> %s</li>', 
                htmlspecialchars($key), 
                htmlspecialchars(is_array($value) ? json_encode($value) : $value)
            );
        }
        $html .= '</ul>';

        return $html;
    }
}
```

## 📊 Grafana Dashboards

### Application Dashboard JSON
```json
{
  "dashboard": {
    "id": null,
    "title": "Warehouse Application Dashboard",
    "tags": ["warehouse", "symfony"],
    "timezone": "browser",
    "panels": [
      {
        "id": 1,
        "title": "Request Rate",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(http_requests_total[5m])",
            "legendFormat": "{{method}} {{endpoint}}"
          }
        ],
        "yAxes": [
          {
            "label": "Requests/sec"
          }
        ]
      },
      {
        "id": 2,
        "title": "Response Time",
        "type": "graph",
        "targets": [
          {
            "expr": "histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m]))",
            "legendFormat": "95th percentile"
          },
          {
            "expr": "histogram_quantile(0.50, rate(http_request_duration_seconds_bucket[5m]))",
            "legendFormat": "50th percentile"
          }
        ]
      },
      {
        "id": 3,
        "title": "Error Rate",
        "type": "singlestat",
        "targets": [
          {
            "expr": "rate(http_requests_total{status=~\"5..\"}[5m]) / rate(http_requests_total[5m]) * 100"
          }
        ],
        "format": "percent"
      }
    ],
    "time": {
      "from": "now-1h",
      "to": "now"
    },
    "refresh": "30s"
  }
}
```

This comprehensive monitoring and observability setup provides full visibility into your Symfony application's performance, health, and security across all deployment environments.