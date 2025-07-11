# CI/CD Pipeline Examples

## 🚀 CI/CD Pipeline Overview

### Pipeline Stages
1. **Source** - Code checkout and dependency caching
2. **Build** - Container image building and artifact creation
3. **Test** - Unit tests, integration tests, security scans
4. **Quality** - Code quality checks, static analysis
5. **Deploy** - Deployment to staging and production
6. **Monitor** - Post-deployment verification and rollback

## 🔄 GitHub Actions Workflows

### Complete CI/CD Pipeline
```yaml
# File: .github/workflows/ci-cd.yml
name: CI/CD Pipeline

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

env:
  REGISTRY: ghcr.io
  IMAGE_NAME: ${{ github.repository }}

jobs:
  test:
    name: Run Tests
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_DB: warehouse_test
          POSTGRES_USER: warehouse
          POSTGRES_PASSWORD: test
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
        ports:
          - 5432:5432
      
      redis:
        image: redis:7-alpine
        options: >-
          --health-cmd "redis-cli ping"
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
        ports:
          - 6379:6379

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, xml, ctype, iconv, intl, pdo_pgsql, redis
          coverage: pcov

      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: /tmp/composer-cache
          key: ${{ runner.os }}-${{ hashFiles('**/composer.lock') }}

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Setup test environment
        run: |
          cp .env.test .env.test.local
          php bin/console doctrine:database:create --env=test
          php bin/console doctrine:migrations:migrate --no-interaction --env=test

      - name: Run PHP_CodeSniffer
        run: vendor/bin/phpcs src tests --standard=PSR12 --report=junit --report-file=phpcs-report.xml

      - name: Run Psalm
        run: vendor/bin/psalm --output-format=github --shepherd

      - name: Run PHPUnit tests
        run: vendor/bin/phpunit --coverage-clover coverage.xml --log-junit phpunit-report.xml

      - name: Upload test results
        uses: actions/upload-artifact@v3
        if: always()
        with:
          name: test-results
          path: |
            phpcs-report.xml
            phpunit-report.xml
            coverage.xml

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          file: ./coverage.xml
          flags: unittests

  security:
    name: Security Scan
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Run security audit
        run: composer audit --format=json > security-audit.json

      - name: Security audit for JS dependencies
        run: |
          npm audit --audit-level=moderate --json > npm-audit.json || true

      - name: Upload security reports
        uses: actions/upload-artifact@v3
        with:
          name: security-reports
          path: |
            security-audit.json
            npm-audit.json

  build:
    name: Build and Push Image
    runs-on: ubuntu-latest
    needs: [test, security]
    if: github.ref == 'refs/heads/main' || github.ref == 'refs/heads/develop'
    
    outputs:
      image: ${{ steps.image.outputs.image }}
      digest: ${{ steps.build.outputs.digest }}

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3

      - name: Log in to Container Registry
        uses: docker/login-action@v3
        with:
          registry: ${{ env.REGISTRY }}
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: Extract metadata
        id: meta
        uses: docker/metadata-action@v5
        with:
          images: ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}
          tags: |
            type=ref,event=branch
            type=ref,event=pr
            type=sha,prefix={{branch}}-
            type=raw,value=latest,enable={{is_default_branch}}

      - name: Build and push Docker image
        id: build
        uses: docker/build-push-action@v5
        with:
          context: .
          platforms: linux/amd64,linux/arm64
          push: true
          tags: ${{ steps.meta.outputs.tags }}
          labels: ${{ steps.meta.outputs.labels }}
          cache-from: type=gha
          cache-to: type=gha,mode=max

      - name: Output image
        id: image
        run: |
          echo "image=${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}:${{ steps.meta.outputs.version }}" >> $GITHUB_OUTPUT

  deploy-staging:
    name: Deploy to Staging
    runs-on: ubuntu-latest
    needs: build
    if: github.ref == 'refs/heads/develop'
    environment: staging
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Deploy to DigitalOcean App Platform
        uses: digitalocean/app_action@v1.1.5
        with:
          app_name: warehouse-staging
          token: ${{ secrets.DIGITALOCEAN_ACCESS_TOKEN }}
          images: '[{
            "name": "web",
            "image": {
              "registry_type": "GHCR",
              "repository": "${{ needs.build.outputs.image }}",
              "tag": "develop"
            }
          }]'

      - name: Run staging smoke tests
        run: |
          sleep 30  # Wait for deployment
          curl -f https://staging.warehouse.com/health || exit 1

  deploy-production:
    name: Deploy to Production
    runs-on: ubuntu-latest
    needs: build
    if: github.ref == 'refs/heads/main'
    environment: production
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Deploy to production
        run: |
          echo "Deploying ${{ needs.build.outputs.image }} to production"
          # Add your production deployment commands here

      - name: Run production smoke tests
        run: |
          sleep 60  # Wait for deployment
          curl -f https://warehouse.com/health || exit 1

      - name: Notify deployment success
        uses: 8398a7/action-slack@v3
        with:
          status: success
          text: "✅ Production deployment successful!"
        env:
          SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}

  rollback:
    name: Rollback if needed
    runs-on: ubuntu-latest
    needs: [deploy-production]
    if: failure()
    environment: production
    
    steps:
      - name: Rollback deployment
        run: |
          echo "Rolling back production deployment"
          # Add rollback commands here

      - name: Notify rollback
        uses: 8398a7/action-slack@v3
        with:
          status: failure
          text: "🚨 Production deployment failed, rolling back!"
        env:
          SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
```

### Feature Branch Workflow
```yaml
# File: .github/workflows/feature-branch.yml
name: Feature Branch CI

on:
  pull_request:
    branches: [ main, develop ]

jobs:
  changes:
    name: Detect Changes
    runs-on: ubuntu-latest
    outputs:
      backend: ${{ steps.changes.outputs.backend }}
      frontend: ${{ steps.changes.outputs.frontend }}
      docker: ${{ steps.changes.outputs.docker }}

    steps:
      - uses: actions/checkout@v4
      - uses: dorny/paths-filter@v2
        id: changes
        with:
          filters: |
            backend:
              - 'src/**'
              - 'tests/**'
              - 'composer.json'
              - 'composer.lock'
            frontend:
              - 'assets/**'
              - 'templates/**'
              - 'package.json'
              - 'package-lock.json'
            docker:
              - 'Dockerfile'
              - 'docker-compose.yml'
              - '.dockerignore'

  test-backend:
    name: Test Backend Changes
    runs-on: ubuntu-latest
    needs: changes
    if: ${{ needs.changes.outputs.backend == 'true' }}
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Run backend tests
        run: |
          echo "Running backend tests..."
          # Add backend testing commands

  test-frontend:
    name: Test Frontend Changes
    runs-on: ubuntu-latest
    needs: changes
    if: ${{ needs.changes.outputs.frontend == 'true' }}
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          cache: 'npm'

      - name: Install dependencies
        run: npm ci

      - name: Run frontend tests
        run: npm test

      - name: Build assets
        run: npm run build

  preview-deployment:
    name: Preview Deployment
    runs-on: ubuntu-latest
    needs: [test-backend, test-frontend]
    if: always() && !failure()
    
    steps:
      - name: Deploy preview environment
        run: |
          echo "Deploying preview environment for PR #${{ github.event.number }}"
          # Add preview deployment commands

      - name: Comment PR with preview URL
        uses: actions/github-script@v6
        with:
          script: |
            github.rest.issues.createComment({
              issue_number: context.issue.number,
              owner: context.repo.owner,
              repo: context.repo.repo,
              body: '🚀 Preview deployment available at: https://pr-${{ github.event.number }}.staging.warehouse.com'
            })
```

## 🔧 GitLab CI/CD Pipeline

### Complete GitLab Pipeline
```yaml
# File: .gitlab-ci.yml
stages:
  - prepare
  - test
  - security
  - build
  - deploy-staging
  - deploy-production

variables:
  DOCKER_TLS_CERTDIR: "/certs"
  DOCKER_HOST: tcp://docker:2376
  DOCKER_TLS_VERIFY: 1
  REGISTRY: $CI_REGISTRY
  IMAGE_NAME: $CI_REGISTRY_IMAGE

cache:
  key: ${CI_COMMIT_REF_SLUG}
  paths:
    - vendor/
    - node_modules/

prepare:
  stage: prepare
  image: composer:2
  script:
    - composer install --prefer-dist --no-progress --no-dev --optimize-autoloader
  artifacts:
    paths:
      - vendor/
    expire_in: 1 hour

test:unit:
  stage: test
  image: php:8.4-fpm
  services:
    - postgres:15
    - redis:7-alpine
  
  variables:
    POSTGRES_DB: warehouse_test
    POSTGRES_USER: warehouse
    POSTGRES_PASSWORD: test
    REDIS_URL: redis://redis:6379
    DATABASE_URL: postgresql://warehouse:test@postgres:5432/warehouse_test

  before_script:
    - apt-get update -qq && apt-get install -y -qq git unzip libpq-dev
    - docker-php-ext-install pdo_pgsql
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install

  script:
    - cp .env.test .env.test.local
    - php bin/console doctrine:database:create --env=test
    - php bin/console doctrine:migrations:migrate --no-interaction --env=test
    - vendor/bin/phpunit --coverage-text --coverage-cobertura=coverage.xml

  coverage: '/^\s*Lines:\s*\d+.\d+\%/'
  
  artifacts:
    reports:
      coverage_report:
        coverage_format: cobertura
        path: coverage.xml
    expire_in: 1 week

test:integration:
  stage: test
  image: php:8.4-fpm
  services:
    - postgres:15
    - redis:7-alpine
  
  script:
    - vendor/bin/phpunit tests/Integration/

code_quality:
  stage: test
  image: php:8.4-cli
  script:
    - vendor/bin/phpcs src tests --standard=PSR12
    - vendor/bin/psalm --output-format=json > psalm-report.json
  
  artifacts:
    reports:
      codequality: psalm-report.json

security_scan:
  stage: security
  image: php:8.4-cli
  script:
    - composer audit --format=json > security-audit.json
  
  artifacts:
    reports:
      dependency_scanning: security-audit.json
  
  allow_failure: true

container_scanning:
  stage: security
  image: docker:stable
  services:
    - docker:dind
  
  variables:
    DOCKER_DRIVER: overlay2
  
  script:
    - docker build -t $CI_REGISTRY_IMAGE:$CI_COMMIT_SHA .
    - echo "Container vulnerability scanning would go here"
  
  allow_failure: true

build:
  stage: build
  image: docker:stable
  services:
    - docker:dind
  
  variables:
    DOCKER_DRIVER: overlay2
  
  before_script:
    - echo $CI_REGISTRY_PASSWORD | docker login -u $CI_REGISTRY_USER $CI_REGISTRY --password-stdin
  
  script:
    - docker build -t $CI_REGISTRY_IMAGE:$CI_COMMIT_SHA .
    - docker tag $CI_REGISTRY_IMAGE:$CI_COMMIT_SHA $CI_REGISTRY_IMAGE:latest
    - docker push $CI_REGISTRY_IMAGE:$CI_COMMIT_SHA
    - docker push $CI_REGISTRY_IMAGE:latest
  
  only:
    - main
    - develop

deploy:staging:
  stage: deploy-staging
  image: alpine:latest
  
  before_script:
    - apk add --no-cache curl
  
  script:
    - echo "Deploying to staging environment"
    - curl -X POST "$STAGING_DEPLOY_WEBHOOK" -H "Authorization: Bearer $STAGING_DEPLOY_TOKEN"
  
  environment:
    name: staging
    url: https://staging.warehouse.com
  
  only:
    - develop

deploy:production:
  stage: deploy-production
  image: alpine:latest
  
  before_script:
    - apk add --no-cache curl
  
  script:
    - echo "Deploying to production environment"
    - curl -X POST "$PRODUCTION_DEPLOY_WEBHOOK" -H "Authorization: Bearer $PRODUCTION_DEPLOY_TOKEN"
  
  environment:
    name: production
    url: https://warehouse.com
  
  when: manual
  only:
    - main

rollback:production:
  stage: deploy-production
  image: alpine:latest
  
  script:
    - echo "Rolling back production deployment"
    - curl -X POST "$PRODUCTION_ROLLBACK_WEBHOOK" -H "Authorization: Bearer $PRODUCTION_DEPLOY_TOKEN"
  
  environment:
    name: production
    action: stop
  
  when: manual
  only:
    - main
```

## 🏗️ Platform-Specific Deployment Automation

### DigitalOcean App Platform
```yaml
# File: .github/workflows/deploy-do.yml
name: Deploy to DigitalOcean

on:
  workflow_call:
    inputs:
      environment:
        required: true
        type: string
      app_name:
        required: true
        type: string
    secrets:
      DO_TOKEN:
        required: true

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Install doctl
        uses: digitalocean/action-doctl@v2
        with:
          token: ${{ secrets.DO_TOKEN }}

      - name: Update app spec
        run: |
          # Update the app spec with new image
          sed -i 's|image:.*|image: ghcr.io/${{ github.repository }}:${{ github.sha }}|' .do/app.yaml

      - name: Deploy to DigitalOcean App Platform
        run: |
          doctl apps update ${{ inputs.app_name }} --spec .do/app.yaml --wait

      - name: Get app info
        run: |
          doctl apps get ${{ inputs.app_name }} --format ID,Name,Status,CreatedAt,UpdatedAt

      - name: Health check
        run: |
          APP_URL=$(doctl apps get ${{ inputs.app_name }} --format DefaultIngress --no-header)
          sleep 30
          curl -f "$APP_URL/health" || exit 1
```

### Google Cloud Run
```yaml
# File: .github/workflows/deploy-gcp.yml
name: Deploy to Google Cloud Run

on:
  workflow_call:
    inputs:
      environment:
        required: true
        type: string
      service_name:
        required: true
        type: string
      region:
        required: true
        type: string
    secrets:
      GCP_SA_KEY:
        required: true
      GCP_PROJECT_ID:
        required: true

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup Google Cloud CLI
        uses: google-github-actions/setup-gcloud@v1
        with:
          service_account_key: ${{ secrets.GCP_SA_KEY }}
          project_id: ${{ secrets.GCP_PROJECT_ID }}

      - name: Configure Docker for GCR
        run: gcloud auth configure-docker

      - name: Build and push image
        run: |
          docker build -t gcr.io/${{ secrets.GCP_PROJECT_ID }}/${{ inputs.service_name }}:${{ github.sha }} .
          docker push gcr.io/${{ secrets.GCP_PROJECT_ID }}/${{ inputs.service_name }}:${{ github.sha }}

      - name: Deploy to Cloud Run
        run: |
          gcloud run deploy ${{ inputs.service_name }} \
            --image gcr.io/${{ secrets.GCP_PROJECT_ID }}/${{ inputs.service_name }}:${{ github.sha }} \
            --region ${{ inputs.region }} \
            --platform managed \
            --allow-unauthenticated \
            --set-env-vars="APP_ENV=${{ inputs.environment }}" \
            --memory=512Mi \
            --cpu=1 \
            --min-instances=0 \
            --max-instances=10

      - name: Get service URL
        run: |
          SERVICE_URL=$(gcloud run services describe ${{ inputs.service_name }} \
            --region ${{ inputs.region }} \
            --format 'value(status.url)')
          echo "Service deployed at: $SERVICE_URL"
          
          # Health check
          sleep 30
          curl -f "$SERVICE_URL/health" || exit 1
```

### Kubernetes Deployment
```yaml
# File: .github/workflows/deploy-k8s.yml
name: Deploy to Kubernetes

on:
  workflow_call:
    inputs:
      environment:
        required: true
        type: string
      namespace:
        required: true
        type: string
    secrets:
      KUBE_CONFIG:
        required: true

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup kubectl
        uses: azure/setup-kubectl@v3
        with:
          version: 'v1.28.0'

      - name: Configure kubectl
        run: |
          echo "${{ secrets.KUBE_CONFIG }}" | base64 -d > kubeconfig
          export KUBECONFIG=kubeconfig

      - name: Update deployment manifest
        run: |
          sed -i 's|image:.*|image: ghcr.io/${{ github.repository }}:${{ github.sha }}|' \
            deployment/kubernetes/deployment.yaml

      - name: Deploy to Kubernetes
        run: |
          kubectl apply -f deployment/kubernetes/namespace.yaml
          kubectl apply -f deployment/kubernetes/configmap.yaml
          kubectl apply -f deployment/kubernetes/secrets.yaml
          kubectl apply -f deployment/kubernetes/deployment.yaml
          kubectl apply -f deployment/kubernetes/service.yaml
          kubectl apply -f deployment/kubernetes/ingress.yaml

      - name: Wait for rollout
        run: |
          kubectl rollout status deployment/warehouse-app -n ${{ inputs.namespace }} --timeout=300s

      - name: Verify deployment
        run: |
          kubectl get pods -n ${{ inputs.namespace }}
          kubectl get services -n ${{ inputs.namespace }}
          
          # Get ingress URL and run health check
          INGRESS_URL=$(kubectl get ingress warehouse-ingress -n ${{ inputs.namespace }} -o jsonpath='{.status.loadBalancer.ingress[0].ip}')
          if [ ! -z "$INGRESS_URL" ]; then
            curl -f "http://$INGRESS_URL/health" || exit 1
          fi
```

## 🔄 Deployment Strategies

### Blue-Green Deployment
```bash
#!/bin/bash
# File: scripts/blue-green-deploy.sh

NAMESPACE="warehouse"
NEW_VERSION=$1
CURRENT_VERSION=$(kubectl get deployment warehouse-app -n $NAMESPACE -o jsonpath='{.metadata.labels.version}')

if [ -z "$NEW_VERSION" ]; then
    echo "Usage: $0 <new-version>"
    exit 1
fi

echo "🔄 Starting blue-green deployment"
echo "📊 Current version: $CURRENT_VERSION"
echo "🆕 New version: $NEW_VERSION"

# Deploy new version (green)
echo "🟢 Deploying green environment..."
kubectl apply -f - <<EOF
apiVersion: apps/v1
kind: Deployment
metadata:
  name: warehouse-app-green
  namespace: $NAMESPACE
  labels:
    app: warehouse
    version: $NEW_VERSION
    environment: green
spec:
  replicas: 3
  selector:
    matchLabels:
      app: warehouse
      version: $NEW_VERSION
  template:
    metadata:
      labels:
        app: warehouse
        version: $NEW_VERSION
    spec:
      containers:
      - name: app
        image: ghcr.io/warehouse/app:$NEW_VERSION
        ports:
        - containerPort: 8080
EOF

# Wait for green deployment to be ready
echo "⏳ Waiting for green deployment to be ready..."
kubectl rollout status deployment/warehouse-app-green -n $NAMESPACE --timeout=300s

# Health check on green environment
echo "🔍 Running health check on green environment..."
kubectl port-forward deployment/warehouse-app-green 8081:8080 -n $NAMESPACE &
PF_PID=$!
sleep 5

if curl -f http://localhost:8081/health; then
    echo "✅ Green environment health check passed"
    kill $PF_PID
else
    echo "❌ Green environment health check failed"
    kill $PF_PID
    kubectl delete deployment warehouse-app-green -n $NAMESPACE
    exit 1
fi

# Switch traffic to green (update service selector)
echo "🔄 Switching traffic to green environment..."
kubectl patch service warehouse-service -n $NAMESPACE -p '{"spec":{"selector":{"version":"'$NEW_VERSION'"}}}'

# Verify traffic switch
echo "⏳ Waiting for traffic switch..."
sleep 30

# Final health check
if curl -f http://your-domain.com/health; then
    echo "✅ Traffic switch successful"
    
    # Clean up old blue deployment
    if [ ! -z "$CURRENT_VERSION" ]; then
        echo "🧹 Cleaning up old blue deployment..."
        kubectl delete deployment warehouse-app-blue -n $NAMESPACE --ignore-not-found=true
    fi
    
    # Rename green to blue for next deployment
    kubectl patch deployment warehouse-app-green -n $NAMESPACE -p '{"metadata":{"name":"warehouse-app-blue"}}'
    
    echo "🎉 Blue-green deployment completed successfully!"
else
    echo "❌ Traffic switch failed, rolling back..."
    kubectl patch service warehouse-service -n $NAMESPACE -p '{"spec":{"selector":{"version":"'$CURRENT_VERSION'"}}}'
    kubectl delete deployment warehouse-app-green -n $NAMESPACE
    exit 1
fi
```

### Canary Deployment
```yaml
# File: deployment/kubernetes/canary-deployment.yaml
apiVersion: argoproj.io/v1alpha1
kind: Rollout
metadata:
  name: warehouse-canary
  namespace: warehouse
spec:
  replicas: 10
  strategy:
    canary:
      steps:
      - setWeight: 10    # 10% traffic to canary
      - pause: {duration: 2m}
      - setWeight: 25    # 25% traffic to canary
      - pause: {duration: 5m}
      - setWeight: 50    # 50% traffic to canary
      - pause: {duration: 10m}
      - setWeight: 75    # 75% traffic to canary
      - pause: {duration: 5m}
      # 100% traffic (full rollout)
      
      analysis:
        templates:
        - templateName: success-rate
        args:
        - name: service-name
          value: warehouse-service
      
      trafficRouting:
        nginx:
          stableService: warehouse-service-stable
          canaryService: warehouse-service-canary
          annotationPrefix: nginx.ingress.kubernetes.io
          
  selector:
    matchLabels:
      app: warehouse
  template:
    metadata:
      labels:
        app: warehouse
    spec:
      containers:
      - name: app
        image: ghcr.io/warehouse/app:latest
        ports:
        - containerPort: 8080
        resources:
          requests:
            memory: 256Mi
            cpu: 250m
          limits:
            memory: 512Mi
            cpu: 500m

---
apiVersion: argoproj.io/v1alpha1
kind: AnalysisTemplate
metadata:
  name: success-rate
  namespace: warehouse
spec:
  args:
  - name: service-name
  metrics:
  - name: success-rate
    interval: 1m
    count: 3
    successCondition: result[0] >= 0.95
    provider:
      prometheus:
        address: http://prometheus:9090
        query: |
          sum(rate(http_requests_total{service="{{args.service-name}}",status!~"5.."}[1m])) /
          sum(rate(http_requests_total{service="{{args.service-name}}"}[1m]))
```

This comprehensive CI/CD pipeline documentation provides automated deployment solutions for your Symfony application across different platforms while maintaining high reliability and security standards.