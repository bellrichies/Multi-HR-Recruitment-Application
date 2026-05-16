# Deployment Guide

This guide covers the production-oriented setup for the Multi-HR Recruitment and Workforce Management Platform.

## Required Services

- PHP 8.2+ with PDO MySQL
- MySQL 8+ using InnoDB
- Node 24+ for frontend builds
- Redis or file cache
- Queue worker scheduler
- HTTPS termination
- Paystack live keys and webhook secret
- SMTP credentials for queued mail

## Environment

Copy `backend/.env.example` to `backend/.env` and set production values.

Minimum production changes:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example.com
APP_FORCE_HTTPS=true
JWT_SECRET=<long-random-secret>
PAYSTACK_SECRET_KEY=<live-secret>
PAYSTACK_WEBHOOK_SECRET=<live-webhook-secret>
```

Never commit `.env`, private keys, database dumps, or live credentials.

## Local Docker

```powershell
docker compose up --build
docker compose exec backend php bin/migrate.php
docker compose exec backend php bin/seed.php
```

Frontend: `http://127.0.0.1:5201`

Backend: `http://127.0.0.1:8080/api/v1/health`

## Queue Workers

Run mail queue processing every few minutes:

```powershell
php backend/bin/process_email_queue.php 50
```

Run report queue heartbeat:

```powershell
php backend/bin/process_report_queue.php 10
```

In production, configure these through cron, systemd timers, Kubernetes CronJobs, or your process manager.

## Database Migrations

Run migrations before traffic is shifted to a new release:

```powershell
php backend/bin/migrate.php
```

Phase 11 adds production indexes. Review large production tables before applying indexes during peak traffic.

## Backups

Create a manual MySQL backup:

```powershell
php backend/bin/backup_database.php
```

Backups are written to `backend/storage/backups`. Store production backups in encrypted off-server storage.

Recommended schedule:

- Daily database backup
- Weekly full backup
- Retain at least 14 days unless business policy requires longer

## Smoke Tests

After deployment:

```powershell
php backend/bin/smoke_test.php https://api.example.com
```

The smoke test verifies that the API health endpoint returns successfully.

## Kubernetes

Example manifests are in `deployment/kubernetes`.

Before applying:

1. Replace image names.
2. Create real secrets from `secret.example.yaml`.
3. Configure TLS secret `multi-hr-tls`.
4. Update `APP_URL`, hostnames, and database host.

```powershell
kubectl apply -f deployment/kubernetes/namespace.yaml
kubectl apply -f deployment/kubernetes/configmap.yaml
kubectl apply -f deployment/kubernetes/secret.example.yaml
kubectl apply -f deployment/kubernetes/
```

## Security Checklist

- `APP_DEBUG=false`
- HTTPS enabled and enforced
- Secure `JWT_SECRET`
- Paystack webhook secret configured
- Database user has least required privileges
- Upload directory is not publicly browsable
- Queue workers are running
- Backups are encrypted and tested
- CI build and smoke tests pass
