# SKILL.md — Multi-HR Recruitment and Workforce Management Platform

## 1. Project Identity

You are working on a **Multi-HR Recruitment and Workforce Management Platform**.

The application connects the following users in one ecosystem:

- Super Admins
- Relationship Officers
- HR Officers
- Recruiters / Employers
- Job Seekers

The platform must support:

- Job posting
- Candidate onboarding
- Candidate discovery
- Candidate-job matching
- HR performance tracking
- Wallet-based payments
- Paystack payment integration
- Online assessments
- Interview workflows
- In-app messaging
- Notifications
- Operational reports
- Financial reports
- Audit logs
- Role-based access control

The system must be built with strong separation of concerns, clean architecture, security, scalability, and maintainability.

---

## 2. Required Technology Stack

Unless a task explicitly states otherwise, use the following stack.

### Backend

- PHP 8+
- Custom MVC architecture
- Object-Oriented Programming
- RESTful APIs
- Structured JSON responses
- PDO for database access
- MySQL with InnoDB
- PSR-4 autoloading
- PSR-12 coding standards

### Frontend

- React.js
- Tailwind CSS
- JavaScript
- jQuery/AJAX where needed for progressive enhancement
- Alpine.js only for lightweight interactivity where React is unnecessary
- Chart.js for charts and analytics
- flatpickr.js for date/time fields

### Database

- MySQL
- InnoDB engine
- Foreign keys
- Indexes
- Transactions
- Soft deletes where appropriate

### Libraries and Integrations

- `vlucas/phpdotenv` for environment variables
- `PHPMailer` for email notifications
- Paystack for payments
- JWT for API authentication
- Redis or file cache for caching
- Queue workers for background jobs

### DevOps

- Docker for containerization
- Kubernetes for orchestration where required
- GitHub Actions or equivalent CI/CD
- HTTPS in production
- Automated testing and deployment pipeline

---

## 3. Core Engineering Principles

Always follow these principles:

1. Use separation of concerns.
2. Keep controllers thin.
3. Place business logic inside services (only logic, no queries).
4. Place database queries inside repositories.
5. Use middleware for authentication, authorization, CSRF, rate limiting, and request filtering.
6. Use validators/request classes for input validation.
7. Use resources/transformers for API response formatting.
8. Use dependency injection where possible.
9. Avoid hardcoded configuration.
10. Read secrets and environment-specific values from `.env`.
11. Write production-ready, maintainable code.
12. Prioritize security, auditability, and data integrity.
13. Use database transactions for critical workflows.
14. Avoid duplicate logic.
15. Make code modular and testable.

---

## 4. Architecture Pattern

Use a **modular monolith** for the MVP.

The application should be structured into domain modules with clear boundaries.

Do not build a microservice architecture unless the product owner or technical lead explicitly requests it in writing.

Recommended modules:

```text
Auth
Users
Roles
Permissions
Recruiters
JobSeekers
HR
RelationshipOfficers
Jobs
Applications
Matching
Assessments
Interviews
Wallet
Payments
Remittance
Messaging
Notifications
Reports
Settings
Audit
Documents
```

The modular monolith must be designed so modules can later be extracted into services if the product scales.

---

## 5. Backend Request Lifecycle

Every backend request should follow this flow:

```text
HTTP Request
  -> Router
  -> Middleware
  -> Controller
  -> Request Validator
  -> Service
  -> Models
  -> Repository
  -> Database
  -> Resource / Transformer
  -> JSON Response
```

Do not skip layers for convenience when implementing business features.

---

## 6. Backend Folder Structure

Use this structure unless instructed otherwise:

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
    Controllers/
      AuthController.php
      UserController.php
      RoleController.php
      RecruiterController.php
      JobSeekerController.php
      JobController.php
      ApplicationController.php
      CandidateController.php
      AssessmentController.php
      InterviewController.php
      WalletController.php
      PaymentController.php
      NotificationController.php
      ReportController.php
      AuditController.php
      SettingsController.php
    Requests/
      LoginRequest.php
      RegisterRequest.php
      UpdateProfileRequest.php
      CreateJobRequest.php
      UpdateJobRequest.php
      ApplyJobRequest.php
      FundWalletRequest.php
      UnlockCandidateRequest.php
      ScheduleInterviewRequest.php
      CreateAssessmentRequest.php
      SubmitAssessmentRequest.php
      CreateReportRequest.php
      UpdateSettingsRequest.php
    Resources/
      UserResource.php
      RecruiterResource.php
      JobSeekerResource.php
      JobResource.php
      ApplicationResource.php
      CandidateResource.php
      AssessmentResource.php
      InterviewResource.php
      WalletResource.php
      PaymentResource.php
      NotificationResource.php
      ReportResource.php
      AuditResource.php
      SettingsResource.php
    Services/
      AuthService.php
      UserService.php
      RoleService.php
      RecruiterService.php
      JobSeekerService.php
      JobService.php
      ApplicationService.php
      MatchingService.php
      AssessmentService.php
      InterviewService.php
      WalletService.php
      PaymentService.php
      NotificationService.php
      ReportService.php
      AuditService.php
      SettingsService.php
    Models/
      User.php
      Role.php
      Recruiter.php
      JobSeeker.php
      Job.php
      Application.php
      Candidate.php
      Assessment.php
      Interview.php
      Wallet.php
      Payment.php
      Notification.php
      Report.php
      Audit.php
      Settings.php
    Repositories/
      UserRepository.php
      RoleRepository.php
      RecruiterRepository.php
      JobSeekerRepository.php
      JobRepository.php
      ApplicationRepository.php
      CandidateRepository.php
      AssessmentRepository.php
      InterviewRepository.php
      WalletRepository.php
      PaymentRepository.php
      NotificationRepository.php
      ReportRepository.php
      AuditRepository.php
      SettingsRepository.php 
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

---

## 7. Frontend Folder Structure

Use this structure unless instructed otherwise:

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
```

---

## 8. Coding Standards

### PHP Standards

Follow:

- PSR-4
- PSR-12
- Strict typing where suitable
- OOP
- SOLID
- Dependency injection
- Repository pattern
- Service layer
- Middleware pattern

Use clear class names:

```text
UserController
UserService
UserRepository
CreateUserRequest
UserResource
AuthMiddleware
PermissionMiddleware
```

### JavaScript / React Standards

Use:

- Functional components
- Reusable hooks
- Reusable API client
- Clean component composition
- Tailwind CSS utility classes
- Loading states
- Empty states
- Error states
- Toast notifications
- Responsive design

---

## 9. API Standards

All APIs must use versioned routes:

```text
/api/v1
```

Use RESTful route naming.

### Standard Success Response

```json
{
  "success": true,
  "message": "Request completed successfully.",
  "data": {},
  "meta": {}
}
```

### Standard Error Response

```json
{
  "success": false,
  "message": "An error occurred.",
  "errors": {}
}
```

### Standard Validation Error Response

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": ["Email address is required."]
  }
}
```

### Standard Pagination Response

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

---

## 10. Authentication and Authorization Rules

### Authentication

Use JWT for authentication in all REST APIs unless otherwise specified.

Authentication requirements:

- Passwords must be hashed with `password_hash()`.
- Never store plain-text passwords.
- Tokens must have expiration.
- Logout should invalidate or blacklist tokens where applicable.
- Super Admin should support MFA where possible.
- Login attempts should be rate-limited.
- If a JWT token is invalid or expired, respond with a 401 Unauthorized status and a clear error message indicating the reason (e.g., 'Token expired', 'Invalid token signature').

### Authorization

Use granular RBAC permissions.

Do not rely only on roles.

Use permissions such as:

```text
users.view
users.create
users.update
users.suspend

roles.manage
permissions.manage

jobs.view
jobs.create
jobs.update
jobs.approve
jobs.publish
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
transactions.view

reports.view
reports.export
settings.manage
audit.view
```

Authorization must be checked in:

1. Route middleware
2. Service layer business ownership rules

Example:

- A recruiter can update only their own jobs.
- An HR Officer can manage only assigned candidates unless granted broader permission.
- A Relationship Officer can manage only assigned jobs unless granted broader permission.
- A Job Seeker can view only their own application history.
- Super Admin can access all modules.

---

## 11. User Roles

### Super Admin

Has unrestricted access.

Can manage:

- Users
- Roles
- Permissions
- Platform settings
- Recruiters
- HR Officers
- Relationship Officers
- Job Seekers
- Jobs
- Applications
- Payments
- Wallets
- Reports
- Audit logs

### Relationship Officer

Supports employer-facing job operations.

Can:

- Post jobs where permitted
- Update assigned jobs
- Manage assigned job workflows
- Open, assign, or close assigned jobs where permitted
- Coordinate with HR Officers and Recruiters

### HR Officer

Manages candidate recruitment lifecycle.

Can:

- Manage assigned candidates
- Match candidates to jobs
- Manage application pipelines
- Conduct screenings
- Assign assessments
- Schedule interviews
- Issue job descriptions/work agreements
- Track placements
- Communicate with candidates and recruiters

### Recruiter

Employer or hiring organization.

Can:

- Register and manage employer profile
- Fund wallet
- Post jobs
- Manage own vacancies
- Review matched candidates
- Unlock candidate details after payment
- Request interviews through HR Officer
- Track recruitment progress

### Job Seeker

Candidate looking for jobs.

Can:

- Register
- Complete profile
- Upload CV and documents
- Add skills, education, work history, certifications
- Browse jobs
- Apply for jobs
- Take assessments
- Attend interviews
- Communicate with assigned HR Officer

---

## 12. Database Rules

Use MySQL with InnoDB.

Rules:

1. Use foreign keys.
2. Add indexes to frequently queried fields.
3. Use transactions for payments, wallet updates, candidate unlocks, placements, and critical workflows.
4. Use soft deletes where records may need recovery.
5. Use audit logs for sensitive changes.
6. Do not update wallet balance without ledger entry.
7. Use append-only wallet transaction records.
8. Avoid storing sensitive documents in public paths.

Common columns:

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

---

## 13. Wallet and Payment Rules

This platform contains financial workflows. Treat wallet and payment code as high-risk.

### Payment Processing

| Action | Preconditions | Postconditions |
|--------|---------------|-----------------|
| Process Payment | Payment initiated by recruiter | Verify Paystack webhook server-side; never trust frontend success alone; check for duplicate reference to prevent double-credit |
| Webhook Handling | Webhook received from Paystack | Webhook must be idempotent; use unique reference + status hash for deduplication; log all webhook attempts |
| Token Validation | User submits request | Validate JWT token before processing; check token expiration |

### Wallet Operations

| Action | Preconditions | Postconditions |
|--------|---------------|-----------------|
| Credit Wallet | Valid transaction received | Execute inside DB transaction; create wallet transaction record; update wallet balance; emit success notification |
| Debit Wallet | Recruiter/user initiates action | Check available balance first; execute inside DB transaction; create transaction record; fail gracefully if insufficient funds |
| Candidate Unlock | Recruiter requests unlock | Debit wallet for unlock fee; verify balance; reference successful transaction; create unlock record; prevent duplicate active unlocks |
| Admin Adjustment | Super Admin initiates | Require permission check; execute inside DB transaction; create audited transaction record; log who made the adjustment |

### Wallet Transaction Types

```text
wallet_funding        - Recruiter funds wallet via Paystack
job_posting_fee       - Fee charged for posting a job
candidate_unlock_fee  - Fee charged for unlocking candidate profile
assessment_fee        - Fee for assessment services
placement_fee         - Fee upon successful placement
commission            - Platform commission on transactions
refund                - Refund of previous transaction
admin_adjustment      - Manual adjustment by admin
remittance            - Payment to recruiter or job seeker
```

### Transaction Statuses

```text
pending      - Awaiting processing
successful   - Completed successfully
failed       - Transaction failed
reversed     - Transaction has been reversed
cancelled    - Transaction was cancelled
```

### General Wallet Rules

- Wallet ledger must be append-only (no deletions or updates to existing records).
- Never update wallet balance without a corresponding transaction record.
- All sensitive wallet operations must be audited.

---

## 14. Job Workflow Rules

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

1. Jobs must have an owner.
2. Recruiters can manage only their own jobs.
3. HR Officers and Relationship Officers can manage assigned jobs only unless granted broader permission.
4. Closed jobs cannot accept applications.
5. Filled jobs must be linked to successful placement records.
6. Job status changes must be audited.
7. Job publishing may require Super Admin approval depending on settings.

---

## 15. Application Pipeline Rules

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

1. Every application must belong to a job and job seeker.
2. Every pipeline stage change must be logged.
3. Invalid stage transitions should be blocked.
4. Recruiters can view applications for their own jobs.
5. Job Seekers can view only their own applications.
6. HR Officers can manage assigned pipelines.
7. Super Admin can view all pipelines.

---

## 16. Candidate Discovery and Unlock Rules

Rules:

1. Candidate full profiles must not be publicly exposed.
2. Recruiters should see limited candidate summaries before unlock.
3. Candidate unlock requires:
   - Active recruiter account
   - Completed recruiter profile
   - Required wallet balance
   - Successful wallet debit
   - Candidate unlock record
4. Duplicate active unlocks should not double-charge the recruiter.
5. Unlock access may expire based on platform settings.
6. Sensitive candidate documents must remain permission-protected.

---

## 17. Assessment Rules

Assessment statuses:

```text
not_started
in_progress
submitted
auto_graded
manual_review
passed
failed
expired
```

Rules:

1. Assessments can be assigned by authorized HR Officers or Super Admin.
2. Objective questions may be auto-graded.
3. Essay questions require manual grading.
4. Submitted assessments should not be editable by candidates.
5. Results should be visible based on permissions.
6. Assessment outcome may affect application pipeline.

---

## 18. Interview Rules

Interview statuses:

```text
scheduled
rescheduled
completed
cancelled
no_show
feedback_pending
```

Rules:

1. Interviews must be linked to an application.
2. Recruiters request interviews through the HR Officer workflow unless policy allows direct scheduling.
3. HR Officer coordinates scheduling.
4. Candidate and recruiter must be notified.
5. Feedback must be stored.
6. Interview result may update application pipeline.

---

## 19. Notification and Messaging Rules

Notifications should be created for:

- Registration
- Account approval/rejection
- Job match
- Application submission
- Candidate shortlist
- Assessment assignment
- Interview scheduling
- Wallet funding
- Payment failure
- Candidate unlock
- Placement confirmation

Rules:

1. In-app notifications must be stored in the database.
2. Email notifications should be sent through PHPMailer.
3. Email sending should be queued.
4. Notification failure must not break the main workflow.
5. Users must be able to mark notifications as read.

---

## 20. Security Requirements

Always implement:

- Input validation
- Output escaping
- Prepared statements
- CSRF protection for session-based forms
- JWT validation for APIs
- Rate limiting on sensitive endpoints
- Secure upload validation
- HTTPS in production
- Environment-based secrets
- Audit logging for sensitive actions
- Permission checks for protected resources

Never:

- Hardcode API keys
- Store plain-text passwords
- Trust frontend validation only
- Expose internal errors to users
- Expose sensitive candidate documents publicly
- Update wallet balances without ledger entries
- Process duplicate payment webhooks without idempotency checks

---

## 21. File Upload Rules

Allowed file types should be configurable.

Default allowed types:

```text
pdf
jpg
jpeg
png
doc
docx
```

Rules:

1. Validate MIME type.
2. Validate extension.
3. Validate file size.
4. Rename files securely.
5. Store sensitive documents outside public root where possible.
6. Use controlled download routes.
7. Check permissions before serving files.
8. If a file upload fails validation, return a 400 Bad Request response with a detailed error message specifying which validation rule was violated (e.g., 'File type not allowed', 'File size exceeds maximum limit of 5MB').

---

## 22. Caching Rules

Cache:

- System settings
- Role permissions
- Public job listings
- Job categories
- Locations
- Dashboard summaries
- Heavy reports

Do not cache:

- Sensitive candidate documents
- Wallet balances without strong invalidation rules
- Payment verification responses as source of truth
- Highly sensitive user sessions without security controls

---

## 23. Queue Rules

Use queues for:

- Emails
- Notifications
- Report generation
- Candidate matching recalculation
- Payment reconciliation
- Webhook processing
- Assessment grading
- Document processing

Queue jobs must:

- Be retryable
- Log failures
- Avoid duplicate processing
- Handle idempotency where needed

---

## 24. Frontend UI Rules

Every major page must include:

- Loading states
- Empty states
- Error states
- Success notifications
- Breadcrumbs
- Search/filter/sort where applicable
- Pagination where needed
- Confirmation modal for destructive actions
- Mobile-first responsive layout
- Form validation
- Permission-aware buttons and navigation

The backend remains the source of truth for permissions.

---

## 25. Dashboard Requirements

### Super Admin Dashboard

Include:

- Total users
- Total recruiters
- Total job seekers
- Total HR Officers
- Total Relationship Officers
- Total jobs
- Active jobs
- Applications
- Placements
- Revenue
- Wallet transactions
- Pending verifications
- Recent audit logs

### HR Officer Dashboard

Include:

- Assigned candidates
- Assigned jobs
- Pending screenings
- Interviews today
- Assessment results pending
- Candidates placed
- Pipeline chart

### Relationship Officer Dashboard

Include:

- Assigned employers
- Assigned jobs
- Open jobs
- Fulfillment progress
- Employer activity

### Recruiter Dashboard

Include:

- Wallet balance
- Jobs posted
- Active jobs
- Matched candidates
- Unlocked candidates
- Interviews scheduled
- Hired candidates
- Payment history

### Job Seeker Dashboard

Include:

- Profile completion
- Recommended jobs
- Applications
- Assessments
- Interviews
- Messages
- Notifications

---

## 26. User Onboarding Rules

Onboarding must support resumable workflows to handle incomplete processes.

Rules:

1. Users must be able to pause onboarding and resume from the step they left off.
2. Onboarding progress must be tracked in the database with a status field (e.g., `pending`, `in_progress`, `completed`).
3. If a user abandons onboarding after initial registration, send an email notification after 24-48 hours encouraging them to complete their profile.
4. Provide a clear "Resume Onboarding" button on the dashboard for users with incomplete profiles.
5. Allow users to restart onboarding from the beginning if they choose to update their initial information.
6. Track which onboarding steps have been completed per user.

---

## 27. Testing Requirements

Write or prepare tests for:

- Authentication
- RBAC
- User management
- Job creation
- Job approval
- Job application
- Candidate matching
- Candidate unlock
- Wallet funding
- Paystack webhook idempotency
- Wallet debit/credit transactions
- Assessment submission
- Interview scheduling
- Notification creation
- Audit logging
- JWT token validation and expiration
- File upload validation
- Onboarding resumption workflows

At minimum, test high-risk workflows manually and document the result.

---

## 28. Environment Variables

Use `.env` and provide `.env.example`.

Required variables:

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

## 29. Implementation Order

Build in this order:

```text
1. Project foundation
2. Authentication and RBAC
3. User profiles and onboarding
4. Job management
5. Applications and candidate matching
6. Wallet and Paystack payments
7. Candidate unlock
8. Assessments
9. Interviews
10. Messaging and notifications
11. Dashboards and reports
12. Audit logs
13. Production hardening
14. Deployment
```

Do not build advanced modules before authentication, RBAC, and core user workflows are stable.

---

## 30. AI Assistant Behavior Rules

When generating code for this project:

1. Always follow the project stack.
2. Always preserve separation of concerns.
3. Do not place business logic in controllers.
4. Do not write raw SQL inside controllers.
5. Do not hardcode secrets, URLs, or credentials.
6. Always use `.env` for configuration.
7. Always validate inputs.
8. Always protect sensitive routes with middleware.
9. Always return structured JSON responses.
10. Always consider audit logs for sensitive actions.
11. Always use transactions for payment, wallet, placement, and unlock workflows.
12. Always consider permissions and ownership rules.
13. Always write code that can scale and be tested.
14. Always include migration and seeder considerations where database changes are required.
15. Always include loading, error, and empty states in frontend features.
16. Always make frontend mobile-first and responsive.
17. Always explain any architectural decision that affects maintainability, security, or scalability.

---

## 31. Output Expectations for AI Coding Tasks

When asked to implement a feature, provide:

1. Short implementation summary.
2. Files to create or update.
3. Backend route definitions.
4. Controller code.
5. Request validation.
6. Service logic.
7. Repository logic.
8. Database migration if needed.
9. Seeder if needed.
10. API response examples.
11. Frontend components/pages if needed.
12. Security and permission checks.
13. Testing checklist.

When asked to refactor, provide:

1. Problems found.
2. Refactor strategy.
3. Updated structure.
4. Updated code.
5. Explanation of separation of concerns.
6. Regression testing checklist.

---

## 32. Definition of Done

A feature is complete only when:

1. Backend route exists.
2. Middleware protection is applied.
3. Request validation exists.
4. Business logic is in service class.
5. Database logic is in repository class.
6. JSON response follows standard format.
7. Permissions and ownership rules are enforced.
8. Audit logging is added where needed.
9. Frontend UI has loading, empty, error, and success states.
10. Feature is mobile responsive.
11. Database changes have migrations.
12. Sensitive actions are secure.
13. Manual or automated testing has been considered.
14. Code follows project conventions.
