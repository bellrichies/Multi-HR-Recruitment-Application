# Project Instructions

## Project Name

Multi-HR Recruitment and Workforce Management Platform

## Purpose of This File

This file defines the technical, architectural, coding, security, and implementation instructions that AI coding assistants must follow when generating, modifying, reviewing, or refactoring code for this project.

All AI-generated work must strictly align with these instructions unless the project owner explicitly overrides them.

---

# 1. Product Context

This application is a Multi-HR Recruitment and Workforce Management Platform built to connect:

- Super Admins
- Relationship Officers
- HR Officers
- Recruiters / Employers
- Job Seekers

The platform supports recruitment operations, job posting, candidate onboarding, candidate discovery, HR performance tracking, online assessments, interview workflows, wallet-based payments, remittance tracking, messaging, notifications, and operational reporting.

The system must be designed as a secure, scalable, maintainable, API-driven application with strong separation of concerns.

---

# 2. Required Technology Stack

## Backend

Use:

- PHP 8+
- Custom MVC architecture
- Object-Oriented Programming
- RESTful APIs
- Structured JSON responses
- PSR-4 autoloading
- PSR-12 coding standard
- Composer
- PDO for database access
- MySQL with InnoDB
- `vlucas/phpdotenv` for environment configuration
- PHPMailer for email
- Paystack for payments

## Frontend

Use:

- React.js
- HTML5
- Tailwind CSS
- JavaScript
- jQuery/AJAX where needed
- Alpine.js for lightweight interactivity where useful
- Chart.js for dashboards and analytics
- flatpickr.js for date/time inputs

## Mobile

Use:

- React Native

## Database

Default database:

- MySQL
- InnoDB engine
- Foreign keys
- Indexes
- Transactions

Supported alternatives only when explicitly requested:

- MariaDB
- PostgreSQL

## Infrastructure

Use where appropriate:

- Docker
- Kubernetes
- GitHub Actions or equivalent CI/CD
- Redis for production cache/queue where available
- HTTPS
- Environment-based deployment

---

# 3. Architectural Principles

AI assistants must follow these architecture principles:

1. Use clean separation of concerns.
2. Keep controllers thin.
3. Place business logic in services.
4. Place database queries in repositories.
5. Use middleware for authentication, authorization, CSRF, rate limiting, and request guards.
6. Use request validator classes for input validation.
7. Use resource/transformer classes for API response formatting.
8. Use dependency injection where possible.
9. Avoid hardcoded values.
10. Use `.env` for secrets and environment-specific configuration.
11. Use database transactions for critical operations.
12. Use audit logs for sensitive operations.
13. Use queues for slow or retryable background tasks.
14. Write scalable, testable, and maintainable code.

---

# 4. Recommended Project Structure

## Backend Structure

```text
backend/
  app/
    Core/
      Application.php
      Router.php
      Request.php
      Response.php
      Controller.php
      Database.php
      Validator.php
      Container.php
      Auth.php
    Config/
      app.php
      database.php
      auth.php
      permissions.php
      services.php
    Middleware/
      AuthMiddleware.php
      PermissionMiddleware.php
      CsrfMiddleware.php
      RateLimitMiddleware.php
      JsonMiddleware.php
      SecurityHeadersMiddleware.php
    Modules/
      Auth/
        Controllers/
        Services/
        Repositories/
        Requests/
        Resources/
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
      Helpers.php
      Logger.php
      FileUpload.php
      Money.php
      Slug.php
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
      client.js
      auth.api.js
      users.api.js
      roles.api.js
      jobs.api.js
      applications.api.js
      candidates.api.js
      wallet.api.js
      reports.api.js
    assets/
    components/
      ui/
      forms/
      tables/
      modals/
      charts/
      navigation/
      feedback/
    layouts/
      PublicLayout.jsx
      AuthLayout.jsx
      DashboardLayout.jsx
    modules/
      auth/
      admin/
      recruiter/
      jobSeeker/
      hrOfficer/
      relationshipOfficer/
      jobs/
      applications/
      candidates/
      wallet/
      assessments/
      interviews/
      messages/
      notifications/
      reports/
      settings/
    routes/
      AppRoutes.jsx
      ProtectedRoute.jsx
      PermissionRoute.jsx
    store/
    hooks/
    utils/
  package.json
  vite.config.js
```

---

# 5. Backend Coding Rules

## Controllers

Controllers must:

- Receive HTTP requests.
- Call validators.
- Call services.
- Return structured JSON responses.
- Stay thin and readable.

Controllers must not:

- Run SQL queries directly.
- Contain business logic.
- Process payment logic directly.
- Directly update wallet balances.
- Directly send complex notifications.
- Contain large conditional workflows.

## Services

Services must:

- Contain business logic only, no SQL queries.
- Enforce ownership rules.
- Coordinate workflows.
- Start and commit database transactions.
- Call repositories.
- Call external integrations.
- Trigger notifications.
- Trigger audit logs.

## Repositories

Repositories must:

- Handle database reads and writes.
- Use prepared statements.
- Keep SQL organized.
- Return clean arrays/entities.
- Avoid business decisions.

Repositories must not:

- Enforce high-level business workflows.
- Send notifications.
- Process payments.
- Modify unrelated modules without service orchestration.

## Request Validators

Validators must:

- Validate required fields.
- Validate data types.
- Validate file uploads.
- Normalize input where useful.
- Return clear validation errors.

## Resources / Transformers

Resources must:

- Format API output.
- Hide sensitive fields.
- Keep API response shape consistent.
- Prevent accidental exposure of private data.

---

# 6. API Standards

All APIs must be RESTful and versioned.

Base API prefix:

```text
/api/v1
```

## Success Response Format

```json
{
  "success": true,
  "message": "Request completed successfully.",
  "data": {},
  "meta": {}
}
```

## Error Response Format

```json
{
  "success": false,
  "message": "An error occurred.",
  "errors": {}
}
```

## Validation Error Format

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "field_name": ["Validation error message."]
  }
}
```

## Pagination Format

```json
{
  "success": true,
  "message": "Records retrieved successfully.",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5
  }
}
```

## API Rules

1. Use proper HTTP methods.
2. Return correct status codes.
3. Use JSON for API requests and responses.
4. Do not expose stack traces in production.
5. Use resources/transformers for responses.
6. Validate all request payloads.
7. Protect private endpoints with authentication.
8. Protect privileged endpoints with permission middleware.

---

# 7. Authentication and Authorization Rules

## Authentication

Use JWT for API authentication.

Required behavior:

- Hash passwords using `password_hash()`.
- Verify passwords using `password_verify()`.
- Use short-lived access tokens.
- Use refresh tokens where necessary.
- Invalidate tokens on logout where supported.
- Invalidate sessions/tokens after password reset.
- Add login throttling.
- Use MFA for Super Admin where possible.

## Authorization

Use Role-Based Access Control with granular permissions.

Permission checks must happen in middleware and service layer.

Example permission names:

```text
users.view
users.create
users.update
users.suspend

roles.view
roles.create
roles.update
roles.delete

permissions.view
permissions.assign

jobs.view
jobs.create
jobs.update
jobs.approve
jobs.assign
jobs.close

candidates.discover
candidates.match
candidates.unlock
candidates.view_full_profile

applications.view
applications.update
applications.move_stage

wallet.view
wallet.fund
wallet.debit
wallet.credit

reports.view
reports.export

settings.manage
audit.view
```

## Ownership Rules

Permissions alone are not enough.

Always enforce ownership and assignment rules:

- Recruiters can manage only their own jobs.
- Recruiters can view full candidate profiles only after unlock/payment rules are satisfied.
- HR Officers can manage assigned candidates and assigned jobs unless granted broader permission.
- Relationship Officers can manage assigned jobs and assigned recruiters unless granted broader permission.
- Job seekers can manage only their own profiles and applications.
- Super Admin can access all platform records.

---

# 8. User Roles

## Super Admin

Full access across the platform.

Can:

- Manage users
- Manage roles and permissions
- Manage settings
- Manage financial rules
- View all reports
- View audit logs
- Approve/suspend/deactivate accounts
- Configure fees, commissions, and remittance policies

## Relationship Officer

Employer-facing operational user.

Can:

- Manage assigned jobs
- Support employer job fulfillment
- Coordinate with HR Officers
- Post/update jobs where permitted
- Open/assign/close jobs where permitted

## HR Officer

Candidate and recruitment lifecycle manager.

Can:

- Manage assigned candidates
- Match candidates to jobs
- Manage candidate pipelines
- Conduct assessments/interviews
- Communicate with job seekers and recruiters
- Track onboarding and placement outcomes

## Recruiter / Employer

Hiring-side user.

Can:

- Register company profile
- Fund wallet
- Post jobs
- Review matched candidates
- Unlock candidate details after payment
- Request interviews through HR Officer
- Monitor recruitment progress

## Job Seeker

Candidate-side user.

Can:

- Register account
- Complete profile
- Upload CV/documents
- Browse jobs
- Apply for jobs
- Take assessments
- Attend interviews
- Message assigned HR Officer

---

# 9. Database Rules

## General Rules

1. Use MySQL InnoDB.
2. Use foreign keys for relational integrity.
3. Use indexes for searchable/filterable columns.
4. Use database transactions for critical operations.
5. Use soft deletes where records should be recoverable.
6. Avoid deleting financial records.
7. Use append-only wallet ledger.
8. Use audit logs for sensitive actions.
9. Store timestamps consistently.
10. Use clear snake_case table and column names.

## Required Common Columns

Most tables should include:

```sql
id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
created_at TIMESTAMP NULL,
updated_at TIMESTAMP NULL
```

Where needed:

```sql
deleted_at TIMESTAMP NULL,
created_by BIGINT UNSIGNED NULL,
updated_by BIGINT UNSIGNED NULL
```

## Wallet Rules

The wallet system must be ledger-based.

Never update a wallet balance without:

1. Starting a database transaction.
2. Locking the wallet row where necessary.
3. Creating a wallet transaction record.
4. Updating the wallet balance.
5. Committing the transaction.
6. Logging the operation.

Every debit must check available balance.

Every credit must have a valid source.

Every Paystack webhook must be idempotent.

---

# 10. Financial and Payment Rules

Use Paystack for wallet funding and payment processing.

## Payment Rules

1. Never trust frontend payment confirmation.
2. Always verify payment server-side with Paystack.
3. Store provider references.
4. Store internal references.
5. Prevent duplicate payment processing.
6. Process webhooks idempotently.
7. Log all payment events.
8. Use transactions for wallet credit/debit.
9. Keep payment secrets in `.env`.

## Candidate Unlock Rules

A recruiter can view a full candidate profile only when:

1. Recruiter account is active.
2. Recruiter company profile is complete.
3. Recruiter has enough wallet balance.
4. Candidate unlock fee is successfully debited.
5. Unlock record is created.
6. Unlock access has not expired.

Do not double-charge for an already active unlock.

---

# 11. Security Rules

AI-generated code must include security best practices.

## Required Security Practices

- Validate all inputs server-side.
- Escape output.
- Use prepared statements.
- Use CSRF protection for session-based forms.
- Use HTTPS in production.
- Use secure cookies where applicable.
- Use rate limiting for auth and sensitive endpoints.
- Store secrets in `.env`.
- Never hardcode API keys.
- Restrict uploads by file type and size.
- Rename uploaded files.
- Store sensitive files outside public root where possible.
- Use signed or protected download routes.
- Do not expose sensitive candidate data without authorization.
- Log sensitive actions.

## Sensitive Data

Treat the following as sensitive:

- CVs
- Guarantor details
- Candidate contact details
- Salary expectations
- Identity documents
- Company documents
- Assessment results
- Wallet and transaction records

---

# 12. Frontend Rules

## General Frontend Standards

Every page must include:

- Responsive mobile-first layout
- Loading states
- Empty states
- Error states
- Success notifications
- Breadcrumbs where appropriate
- Search/filter/sort where useful
- Pagination for large tables
- Confirmation modals for destructive actions
- Form validation
- Accessible labels
- Permission-aware UI rendering

## Frontend Security Rules

1. Do not store secrets in frontend code.
2. Do not rely on frontend permission checks only.
3. Hide unauthorized UI actions, but backend must enforce access.
4. Use environment variables for API base URL.
5. Sanitize/escape rendered user content where needed.
6. Handle expired sessions gracefully.

## Dashboard Rules

Dashboards must be role-specific.

Each dashboard should show only relevant metrics and actions for the logged-in user.

---

# 13. Module-Specific Business Rules

## Jobs

Job statuses:

```text
draft
pending_approval
published
open
assigned
paused
closed
cancelled
filled
```

Rules:

- Closed jobs cannot receive applications.
- Jobs must have a creator.
- Recruiter-owned jobs must be linked to recruiter profile.
- Job approval should be configurable.
- Job status changes must be audited.

## Applications

Pipeline stages:

```text
applied
matched
screening
assessment_invited
assessment_completed
shortlisted
interview_scheduled
interview_completed
offer_pending
offer_accepted
placed
rejected
withdrawn
```

Rules:

- Every stage change must be logged.
- Invalid stage transitions must be blocked.
- Job seekers can view their own application status.
- Recruiters can view applications for their jobs.
- HR Officers manage assigned application pipelines.

## Assessments

Rules:

- Submitted assessments cannot be modified.
- Objective questions may be auto-graded.
- Essay questions require manual grading.
- Assessment results should update application progress where appropriate.

## Interviews

Rules:

- Recruiters request interviews through HR Officers unless policy allows direct scheduling.
- Interview changes must notify all participants.
- Feedback must be stored and linked to the application.

## Notifications

Rules:

- In-app notifications should be stored in the database.
- Emails should be queued.
- Notification failures should not break core workflows.
- Failed jobs should be retryable.

---

# 14. Caching and Queues

## Cache These

- System settings
- Permissions
- Role permissions
- Public job listings
- Dashboard summary cards
- Reports with heavy aggregation
- Lookup lists such as job categories and locations

## Queue These

- Emails
- Notifications
- Report generation
- Assessment grading
- Candidate match recalculation
- Paystack webhook processing where appropriate
- Payment reconciliation
- File/document processing

---

# 15. Logging and Audit Rules

## Audit These Actions

- Login failures
- Password changes
- Role changes
- Permission changes
- Account suspension/deactivation
- Recruiter verification decisions
- Job approval/status changes
- Application stage changes
- Candidate unlocks
- Wallet credits/debits
- Payment verification
- Admin wallet adjustments
- Settings changes

## Audit Log Fields

```text
actor_id
action
module
entity_type
entity_id
old_values_json
new_values_json
ip_address
user_agent
created_at
```

---

# 16. Testing Expectations

AI assistants should generate code that is easy to test.

Prioritize tests for:

- Authentication
- RBAC
- User onboarding
- Job posting
- Job application
- Candidate matching
- Candidate unlock
- Wallet funding
- Wallet debit/credit
- Paystack webhook idempotency
- Application pipeline transitions
- File upload validation
- Dashboard metrics
- Reports

---

# 17. CI/CD Expectations

Recommended CI/CD pipeline:

```text
Pull Request
  -> Install backend dependencies
  -> Run PHP syntax checks
  -> Run backend tests
  -> Install frontend dependencies
  -> Run frontend lint
  -> Build frontend
  -> Run migration checks
  -> Build Docker image
  -> Deploy to staging
  -> Run smoke tests
  -> Manual approval
  -> Deploy to production
```

---

# 18. Environment Variables

Use `.env` and provide `.env.example`.

Example:

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

---

# 19. Naming Conventions

## Backend

```text
Controllers: UserController
Services: UserService
Repositories: UserRepository
Requests: CreateUserRequest
Resources: UserResource
Middleware: AuthMiddleware
Tables: snake_case plural
Columns: snake_case
Classes: PascalCase
Methods: camelCase
```

## Frontend

```text
Components: PascalCase
Hooks: useAuth, useJobs
API files: jobs.api.js
Routes: kebab-case
State files: camelCase
```

---

# 20. AI Assistant Behavior Rules

When generating code for this project, AI assistants must:

1. Follow this `instructions.md` file.
2. Preserve separation of concerns.
3. Avoid placing business logic in controllers.
4. Avoid hardcoded secrets, URLs, credentials, or API keys.
5. Use `.env` values for configuration.
6. Use structured JSON API responses.
7. Validate inputs.
8. Enforce authorization.
9. Include audit logging for sensitive operations.
10. Use database transactions for critical workflows.
11. Avoid changing unrelated files unnecessarily.
12. Keep code production-ready.
13. Explain important architectural decisions briefly.
14. Generate migrations when adding new database-backed features.
15. Generate seeders where demo data is needed.
16. Respect existing project structure.
17. Avoid breaking existing routes and contracts.
18. Add or update tests where appropriate.
19. Maintain mobile-first responsive frontend design.
20. Prioritize security, maintainability, scalability, and clarity.

---

# 21. Implementation Priority

Build the project in this order:

```text
1. Foundation and project setup
2. Authentication and RBAC
3. User profiles and onboarding
4. Job management
5. Applications and candidate matching
6. Wallet, Paystack, and candidate unlock
7. Assessments and interviews
8. Messaging and notifications
9. Reports, dashboards, and audit logs
10. Production hardening and deployment
```

---

# 22. Final Instruction

This project must be treated as a production-grade HR/recruitment SaaS platform.

All generated code, documentation, database design, frontend UI, API contracts, and deployment configuration must be secure, maintainable, scalable, and aligned with clean architecture principles.
