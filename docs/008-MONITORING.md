# Monitoring

Skavoo does not ship with a monitoring stack by default. This doc outlines what to monitor and simple ways to add visibility.

## What to Monitor

### Application-level

- Login failures (rate, IPs)
- Password reset requests (rate)
- Error rates (500s, 404s)
- Slow requests (DB queries, rendering)

### Infrastructure-level

- CPU, memory, disk
- PHP-FPM worker saturation (if using PHP-FPM)
- MySQL performance (connections, slow queries)

## Logging

### PHP errors

Ensure PHP errors are logged to a file rather than displayed in production.

- Set `APP_DEBUG=false` for production-like behavior

### Audit logs table

Schema includes an `audit_logs` table. If you want to use it, you can log:

- login events
- password reset events
- post deletions

This is currently a capability “hook” rather than a fully-wired feature.

## Health Checks

A simple approach is to add a `/health` endpoint that:

- returns HTTP 200
- optionally checks DB connectivity

(Only do this if you want to expose health checks; keep it protected if needed.)

## Alerting

Common alerts:

- 5xx rate > threshold
- DB connection failures
- disk nearly full (uploads/mail storage)
- unusual spikes in auth endpoints
