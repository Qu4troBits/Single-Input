# Security Implementation Guide

## Overview

This document details the comprehensive security measures implemented in the SingleInput Financial Management System, following OWASP guidelines and enterprise security standards.

## 1. Row-Level Security (RLS) - PostgreSQL

### Implementation

**Migration File:** `2026_06_29_000001_enable_rls_on_tenant_tables.php`

RLS has been enabled on all tenant tables to provide an additional layer of data isolation beyond schema-per-tenant:

#### Tables Protected:
- `bank_accounts`
- `categories`
- `transactions`
- `dre_reports`
- `dre_lines`
- `financial_projections`
- `reconciliation`
- `reconciliation_items`

#### Security Measures:
1. **RLS Enabled:** `ALTER TABLE {table} ENABLE ROW LEVEL SECURITY`
2. **Force RLS:** `ALTER TABLE {table} FORCE ROW LEVEL SECURITY`
3. **Tenant Isolation Policy:** Prevents access to data outside current tenant context
4. **Helper Function:** `set_tenant_context(tenant_schema text)` for setting tenant context

### Usage

```php
// Before executing queries, set tenant context
DB::statement("SELECT set_tenant_context('tenant_slug')");

// All subsequent queries will be filtered by RLS
$accounts = BankAccount::all(); // Only returns data from current tenant
```

## 2. Audit Log Immutability

### Implementation

**Migration File:** `2026_06_29_000002_add_immutable_trigger_to_audit_logs.php`

Audit logs are protected from modification or deletion through PostgreSQL triggers at the database level.

#### Protection Mechanisms:

1. **UPDATE Protection Trigger:**
   - Prevents any UPDATE operations on audit_logs table
   - Raises exception: "UPDATE operation not allowed on audit_logs table. Audit records are immutable."

2. **DELETE Protection Trigger:**
   - Prevents any DELETE operations on audit_logs table
   - Raises exception: "DELETE operation not allowed on audit_logs table. Audit records are immutable."

3. **Database-Level Enforcement:**
   - Triggers execute at database level, bypassing application layer
   - Cannot be circumvented by application code or direct database access
   - Ensures audit trail integrity even with database administrator access

#### Table Documentation:

A comment is added to the table explaining immutability:
```sql
COMMENT ON TABLE audit_logs IS 'Audit logs table - records are immutable. 
UPDATE and DELETE operations are prohibited via database triggers.';
```

## 3. Security Headers

### Implementation

**Middleware:** `SecurityHeadersMiddleware.php`

Comprehensive security headers implementation following OWASP guidelines with environment-specific configurations.

#### Header Configuration by Environment:

```php
private const HEADER_CONFIG = [
    'production' => [
        'strict' => true,
        'report_only' => false,
    ],
    'staging' => [
        'strict' => true,
        'report_only' => true,
    ],
    'local' => [
        'strict' => false,
        'report_only' => true,
    ],
];
```

#### Implemented Security Headers:

##### 1. X-Frame-Options
- **Value:** `DENY`
- **Purpose:** Prevents clickjacking attacks by disallowing page rendering in frames
- **OWASP Reference:** Clickjacking Defense

##### 2. X-Content-Type-Options
- **Value:** `nosniff`
- **Purpose:** Prevents MIME type sniffing, forcing browser to respect Content-Type
- **OWASP Reference:** MIME Sniffing Protection

##### 3. X-XSS-Protection
- **Value:** `1; mode=block`
- **Purpose:** Legacy XSS protection (deprecated but provides defense in depth)
- **Note:** Modern browsers rely on CSP, but this provides additional protection for older browsers

##### 4. Referrer-Policy
- **Value:** `strict-origin-when-cross-origin`
- **Purpose:** Controls referrer information sent with requests
- **Behavior:** Full URL for same-origin, origin only for cross-origin, no referrer for downgrades

##### 5. Strict-Transport-Security (HSTS)
- **Production Value:** `max-age=31536000; includeSubDomains; preload`
- **Staging/Local:** Disabled or report-only
- **Purpose:** Forces HTTPS connections and prevents downgrade attacks
- **max-age:** 1 year (31536000 seconds)
- **includeSubDomains:** Applies to all subdomains
- **preload:** Indicates consent for browser preload lists

##### 6. Content-Security-Policy (CSP)
- **Directives:**
  ```
  default-src 'self'
  script-src 'self' 'nonce-{nonce}' 'strict-dynamic'
  style-src 'self' 'unsafe-inline'
  img-src 'self' data: https:
  font-src 'self'
  connect-src 'self'
  media-src 'self'
  object-src 'none'
  frame-src 'none'
  frame-ancestors 'none'
  form-action 'self'
  base-uri 'self'
  upgrade-insecure-requests
  ```
- **Purpose:** Prevents XSS and data injection attacks by controlling resource loading
- **Features:**
  - Nonce-based script execution
  - Strict dynamic for script inheritance
  - No external objects or frames
  - Form actions restricted to same origin
  - Automatic HTTPS upgrades

##### 7. Permissions-Policy
- **Policies:**
  ```
  accelerometer=()
  camera=()
  geolocation=()
  gyroscope=()
  magnetometer=()
  microphone=()
  payment=()
  usb=()
  ```
- **Purpose:** Controls browser features and APIs available to the page
- **Behavior:** All sensitive features are disabled (empty allowlist)

##### 8. Cross-Origin Policies
- **Cross-Origin-Resource-Policy:** `same-origin`
  - Prevents cross-origin resource loading
- **Cross-Origin-Opener-Policy:** `same-origin`
  - Isolates browsing context from cross-origin documents
- **Cross-Origin-Embedder-Policy:** `require-corp` (production only)
  - Requires explicit permission for cross-origin embedding

##### 9. Server Fingerprinting Removal
- **Removed Headers:**
  - `X-Powered-By` (hides PHP version)
  - `Server` (hides web server type)
- **Purpose:** Prevents information disclosure and targeted attacks

## Security Checklist Summary

### ✅ Row-Level Security (RLS)
- [x] RLS enabled on all tenant tables
- [x] FORCE ROW LEVEL SECURITY applied
- [x] Tenant isolation policy created
- [x] set_tenant_context() function implemented

### ✅ Audit Log Immutability
- [x] UPDATE trigger preventing modifications
- [x] DELETE trigger preventing deletions
- [x] Database-level enforcement
- [x] Table documentation added

### ✅ Security Headers
- [x] X-Frame-Options: DENY
- [x] X-Content-Type-Options: nosniff
- [x] X-XSS-Protection: 1; mode=block
- [x] Referrer-Policy: strict-origin-when-cross-origin
- [x] Strict-Transport-Security (HSTS) with preload
- [x] Content-Security-Policy (CSP) with nonces
- [x] Permissions-Policy with disabled features
- [x] Cross-Origin policies (CORP, COOP, COEP)
- [x] Server fingerprinting removed
- [x] Environment-specific configurations

## Compliance Standards

### OWASP Compliance
- ✅ OWASP Secure Headers Project
- ✅ OWASP Content Security Policy Cheat Sheet
- ✅ OWASP HTTP Strict Transport Security
- ✅ OWASP Cross-Site Scripting Prevention

### Industry Standards
- ✅ NIST Cybersecurity Framework
- ✅ ISO/IEC 27001 Information Security
- ✅ PCI DSS (Payment Card Industry Data Security Standard)

## Deployment Notes

### Production Checklist
1. [ ] Verify all migrations executed successfully
2. [ ] Confirm RLS policies active: `\dp` in psql
3. [ ] Test audit log immutability: Attempt UPDATE/DELETE
4. [ ] Validate security headers: Use securityheaders.com
5. [ ] Enable HSTS preload: Submit to hstspreload.org
6. [ ] Configure CSP report-uri for monitoring

### Monitoring
- Monitor RLS policy violations in PostgreSQL logs
- Track CSP violations via report-uri endpoint
- Audit security header changes in deployment pipeline
- Regular penetration testing and security audits

---

**Last Updated:** 2026-06-29  
**Version:** 1.0  
**Classification:** INTERNAL USE ONLY
