# Implementation Blueprint

## Recommended Project Structure

```text
project-root/
  backend/
  frontend/
  database/
  docs/
  deployment/
  README.md
```

## Backend Structure

```text
backend/
  app/
    Core/
    Config/
    Middleware/
    Modules/
      Auth/
      Users/
      Roles/
      Recruiters/
      JobSeekers/
      HR/
      RelationshipOfficers/
      Jobs/
      Applications/
      Matching/
      Assessments/
      Interviews/
      Wallet/
      Payments/
      Notifications/
      Messaging/
      Reports/
      Audit/
      Settings/
    Support/
  database/
    migrations/
    seeders/
  public/
    index.php
  routes/
    api.php
    web.php
  storage/
    logs/
    uploads/
    cache/
    queues/
  tests/
  composer.json
  .env.example
```

## Frontend Structure

```text
frontend/
  src/
    api/
    assets/
    components/
    layouts/
    modules/
    routes/
    store/
    hooks/
    utils/
  package.json
  vite.config.js
```

## Development Standards

### Backend Standards

- PHP 8+
- PSR-4 autoloading
- PSR-12 formatting
- SOLID principles
- Thin controllers
- Service layer for business logic
- Repository pattern for database queries
- Middleware for authentication and authorization
- Structured JSON responses
- Centralized validation
- Centralized error handling
- Secure file upload handling
- Audit logs for sensitive actions
- Database transactions for critical workflows

### Frontend Standards

- React components should be modular.
- Use Tailwind CSS.
- Use loading states.
- Use error states.
- Use empty states.
- Use toast notifications.
- Use form validation.
- Use role-aware navigation.
- Use permission-aware UI rendering.
- Use reusable table and modal components.
- Use API client wrapper.
- Use environment variables for API base URL.

### Database Standards

- Use MySQL InnoDB.
- Use foreign keys.
- Use indexes.
- Use transactions.
- Use append-only wallet ledger.
- Use audit logs.
- Use soft deletes where necessary.

## Build Order

### MVP Build Order

```text
1. Project foundation
2. Authentication and RBAC
3. User onboarding
4. Recruiter profile
5. Job seeker profile
6. Job management
7. Applications
8. Candidate matching
9. Wallet and Paystack
10. Candidate unlock
11. Basic interviews
12. Notifications
13. Dashboards
14. Audit logs
15. Deployment
```

### Production Build Order

```text
16. Assessments
17. Messaging
18. Advanced reports
19. Remittance management
20. Compliance workflows
21. Disputes
22. Advanced candidate discovery
23. Mobile app
24. AI matching
25. Kubernetes scaling
```

## Required Backend Services

```text
AuthService
UserService
RoleService
PermissionService
RecruiterService
JobSeekerService
JobService
ApplicationService
CandidateMatchingService
CandidateUnlockService
AssessmentService
InterviewService
WalletService
PaymentService
PaystackService
NotificationService
MessagingService
ReportService
AuditLogService
SettingsService
FileUploadService
```

## Required Frontend Shared Components

```text
Button
Input
Select
Textarea
Modal
ConfirmDialog
DataTable
Pagination
Toast
Badge
Card
StatCard
ChartCard
Breadcrumbs
Sidebar
Topbar
ProtectedRoute
PermissionGate
FileUpload
DatePicker
CurrencyInput
EmptyState
LoadingState
ErrorState
```

## Recommended `.env.example`

```env
APP_NAME="Multi HR Platform"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multi_hr
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=change_this_secret
JWT_TTL=3600
JWT_REFRESH_TTL=604800

MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Multi HR Platform"

PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
PAYSTACK_CALLBACK_URL=http://localhost:8080/api/v1/payments/paystack/callback
PAYSTACK_WEBHOOK_SECRET=

UPLOAD_MAX_SIZE=5242880
UPLOAD_ALLOWED_TYPES=pdf,jpg,jpeg,png,doc,docx

CACHE_DRIVER=file
QUEUE_DRIVER=database

SUPER_ADMIN_NAME="Super Admin"
SUPER_ADMIN_EMAIL=admin@example.com
SUPER_ADMIN_PASSWORD=Password@123
```

## Git Branching Strategy

```text
main
staging
develop
feature/auth-module
feature/job-management
feature/wallet-payments
bugfix/payment-webhook-idempotency
hotfix/login-rate-limit
```

## Testing Strategy

Test areas:

- Auth
- RBAC
- Job posting
- Job application
- Candidate matching
- Wallet funding
- Candidate unlock
- Payment webhook idempotency
- Application pipeline stage transitions
- File upload validation
- Report calculations
- Dashboard metrics

## MVP Definition of Done

The MVP is done when:

1. All core modules are implemented.
2. Permission checks are enforced.
3. Wallet ledger is reliable.
4. Paystack integration is verified.
5. Candidate unlock works.
6. Job and application pipelines work.
7. Dashboards show live data.
8. Audit logs capture sensitive actions.
9. Frontend is responsive.
10. Application is deployed to staging.
11. Smoke tests pass.
