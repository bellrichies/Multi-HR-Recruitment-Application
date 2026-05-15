# Copilot-Ready Prompts

Use these prompts with AI coding assistants.

---

## Phase 1 Prompt: Project Foundation

```text
Act as a senior PHP 8 software architect. Build the foundation of a custom PHP MVC backend for a Multi-HR Recruitment and Workforce Management Platform.

Use PSR-4 autoloading, PSR-12 coding standards, OOP, SOLID principles, dependency injection, repository pattern, service layer, and thin controllers.

Create the following:
1. public/index.php front controller
2. app/Core/Application.php
3. app/Core/Router.php
4. app/Core/Request.php
5. app/Core/Response.php
6. app/Core/Controller.php
7. app/Core/Database.php using PDO singleton connection
8. app/Core/Validator.php
9. app/Core/Container.php
10. Centralized error handling
11. routes/api.php
12. .env.example
13. composer.json with PSR-4 autoloading
14. Base JSON response format for success and errors

Use vlucas/phpdotenv for environment variables.
Use MySQL with PDO.
Ensure the architecture is scalable and ready for modules such as Auth, Users, Jobs, Wallet, Payments, Applications, Assessments, Interviews, Reports, and Audit.
```

---

## Phase 2 Prompt: Authentication and RBAC

```text
Build the Authentication, User, Role, and Permission modules for the PHP custom MVC backend.

Requirements:
1. Create migrations for users, roles, permissions, user_roles, and role_permissions.
2. Implement user registration and login.
3. Hash passwords using password_hash().
4. Implement JWT authentication for REST APIs.
5. Create AuthMiddleware to validate JWT.
6. Create PermissionMiddleware to enforce granular permissions.
7. Seed default roles: super_admin, relationship_officer, hr_officer, recruiter, job_seeker.
8. Seed system permissions grouped by modules.
9. Seed one Super Admin user from .env values.
10. Implement endpoints for:
   - POST /api/v1/auth/login
   - POST /api/v1/auth/logout
   - GET /api/v1/auth/me
   - GET /api/v1/roles
   - POST /api/v1/roles
   - GET /api/v1/permissions
   - POST /api/v1/users/{id}/roles
11. Ensure controllers remain thin and business logic is inside services.
12. Return structured JSON responses.

Follow PSR-12, SOLID, repository pattern, dependency injection, and secure coding best practices.
```

---

## Phase 3 Prompt: User Profiles and Onboarding

```text
Implement onboarding modules for Recruiters, Job Seekers, HR Officers, and Relationship Officers.

Create migrations, repositories, services, controllers, validators, and API routes for:
1. recruiter_profiles
2. recruiter_documents
3. job_seeker_profiles
4. job_seeker_skills
5. job_seeker_work_experiences
6. job_seeker_educations
7. job_seeker_certifications
8. job_seeker_documents
9. guarantors
10. hr_officer_profiles
11. relationship_officer_profiles

Business rules:
- A recruiter must complete company profile before posting jobs.
- A recruiter verification status can be pending, under_review, verified, rejected, or suspended.
- A job seeker profile completion percentage must be calculated based on required profile sections.
- Job seekers may optionally register with an HR referral code.
- File uploads must validate type, size, and destination.
- Sensitive documents must not be publicly exposed without authorization.

Create API endpoints for creating, updating, viewing, and reviewing profiles.
Use structured JSON responses and permission middleware.
```

---

## Phase 4 Prompt: Job Management

```text
Build the Job Management module for the Multi-HR Recruitment platform.

Create migrations, models/repositories, services, controllers, request validators, resources, and routes for:
1. jobs
2. job_skills
3. job_assignments

Features:
- Create job
- Update job
- Submit job for approval
- Approve job
- Publish job
- Pause job
- Close job
- Assign job to HR Officer
- Assign job to Relationship Officer
- Public job listing
- Recruiter job listing
- Admin job listing
- Job detail page API

Job statuses:
draft, pending_approval, published, open, assigned, paused, closed, cancelled, filled.

Business rules:
- Recruiters can manage only their own jobs.
- HR Officers and Relationship Officers can manage assigned jobs only unless they have broader permissions.
- Super Admin can manage all jobs.
- Closed jobs cannot accept applications.
- Required fields must be validated before publishing.
- Every status change must be logged in audit_logs.

Follow clean architecture, service-based business logic, repository pattern, and permission middleware.
```

---

## Phase 5 Prompt: Applications and Candidate Matching

```text
Implement the Applications, Candidate Matching, and Candidate Discovery modules.

Create migrations and backend logic for:
1. job_applications
2. application_stage_logs
3. candidate_matches
4. candidate_unlocks

Features:
- Job seeker applies for a job.
- HR Officer manually matches candidate to job.
- System calculates a basic match score based on skills, location, experience, salary expectation, and availability.
- HR Officer can shortlist, reject, or move candidates through pipeline stages.
- Recruiter can view application progress.
- Candidate discovery filters should support skills, location, experience, availability, salary range, education, and profile completion.
- Candidate full profile should be protected until unlock/payment permission is granted.
- Every pipeline stage change should be logged.

Pipeline stages:
applied, matched, screening, assessment_invited, assessment_completed, shortlisted, interview_scheduled, interview_completed, offer_pending, offer_accepted, placed, rejected, withdrawn.

Use thin controllers, services, repositories, request validators, and structured JSON responses.
```

---

## Phase 6 Prompt: Wallet, Paystack, and Candidate Unlock

```text
Build the Wallet and Payment module with Paystack integration.

Create migrations, repositories, services, controllers, and routes for:
1. wallets
2. wallet_transactions
3. payments
4. payment_webhook_events
5. candidate_unlocks

Features:
- Automatically create wallet for recruiters.
- Initialize Paystack wallet funding.
- Verify Paystack transaction server-side.
- Process Paystack webhook events idempotently.
- Credit wallet after successful verified payment.
- Debit wallet for candidate unlock fee.
- Record every financial movement in wallet_transactions.
- Prevent duplicate webhook processing.
- Prevent wallet debit when balance is insufficient.
- Use database transactions for wallet credit/debit.
- Add transaction history endpoint.
- Add admin financial report endpoint.

Business rules:
- Wallet balance must never change without ledger entry.
- Every wallet operation must be auditable.
- Candidate unlock should grant recruiter access to full candidate profile for a configured period.
- Fees should be configurable by Super Admin.

Use .env for Paystack keys.
Do not hard-code secrets.
```

---

## Phase 7 Prompt: Assessments and Interviews

```text
Build the Assessment and Interview modules.

Assessment requirements:
1. Create assessments.
2. Add questions.
3. Support multiple-choice and essay questions.
4. Assign assessments to candidates.
5. Candidate can start and submit assessment.
6. Auto-grade objective questions.
7. Allow HR Officer to manually grade essay questions.
8. Store results and pass/fail status.

Interview requirements:
1. Schedule interview for application.
2. Store meeting link.
3. Support video, phone, and physical interview types.
4. Allow HR Officer to reschedule or cancel.
5. Allow feedback submission by HR Officer and Recruiter.
6. Update application pipeline based on interview outcome.
7. Send notifications when interview is scheduled or changed.

Use migrations, repositories, services, controllers, validators, routes, and JSON resources.
Enforce role and permission checks.
```

---

## Phase 8 Prompt: Messaging and Notifications

```text
Implement Messaging and Notification modules.

Create migrations and backend logic for:
1. conversations
2. conversation_participants
3. messages
4. notifications

Features:
- HR Officer can message assigned job seekers.
- HR Officer can message recruiters for assigned jobs.
- Recruiters can send interview requests through HR Officer.
- Job seekers can communicate with assigned HR Officer.
- Users can see unread message count.
- Users can mark notifications as read.
- Email notifications should be sent using PHPMailer.
- In-app notifications should be stored in the database.
- Queue email notification jobs for background processing.

Notification events:
- New job match
- Application submitted
- Candidate shortlisted
- Assessment assigned
- Interview scheduled
- Wallet funded
- Payment failed
- Placement confirmed
- Account approved or suspended

Follow clean architecture and secure access control.
```

---

## Phase 9 Prompt: Dashboards, Reports, and Audit

```text
Build dashboards, reports, and audit logging for all roles.

Create dashboard APIs for:
1. Super Admin
2. HR Officer
3. Relationship Officer
4. Recruiter
5. Job Seeker

Super Admin dashboard should include:
- Total users
- Total recruiters
- Total job seekers
- Total HR officers
- Total relationship officers
- Total jobs
- Active jobs
- Applications
- Placements
- Revenue
- Wallet transaction volume
- Pending verifications
- Recent audit logs

HR dashboard should include:
- Assigned candidates
- Assigned jobs
- Candidates screened
- Candidates placed
- Pending interviews
- Assessment results pending
- Pipeline summary

Recruiter dashboard should include:
- Wallet balance
- Jobs posted
- Active jobs
- Matched candidates
- Interviews scheduled
- Hired candidates
- Payment history

Job seeker dashboard should include:
- Profile completion
- Recommended jobs
- Applications
- Assessments
- Interviews
- Notifications

Use Chart.js-friendly JSON structures for analytics.
Create audit logging service and ensure sensitive actions are recorded.
```

---

## Phase 10 Prompt: React Frontend

```text
Build the React frontend for the Multi-HR Recruitment and Workforce Management Platform.

Use:
- React.js
- Tailwind CSS
- React Router
- Axios or Fetch API wrapper
- Chart.js
- flatpickr.js where dates are needed
- Responsive mobile-first design
- Loading states
- Empty states
- Error states
- Toast notifications
- Breadcrumbs
- Protected routes
- Permission-based UI rendering

Create layouts:
1. PublicLayout
2. AuthLayout
3. DashboardLayout

Create modules:
1. auth
2. admin
3. recruiter
4. jobSeeker
5. hrOfficer
6. relationshipOfficer
7. jobs
8. applications
9. wallet
10. reports
11. assessments
12. interviews
13. messages
14. notifications

Implement:
- Login page
- Registration pages by user type
- Super Admin dashboard
- Recruiter dashboard
- HR Officer dashboard
- Relationship Officer dashboard
- Job Seeker dashboard
- Job listing page
- Job detail page
- Candidate discovery page
- Application pipeline page
- Wallet page
- Reports page
- Role and permission management UI
- User management UI

Ensure the UI is clean, modern, professional, mobile-responsive, and suitable for an HR/recruitment SaaS platform.
```

---

## Phase 11 Prompt: Production Hardening

```text
Prepare the application for production.

Tasks:
1. Add centralized logging.
2. Add rate limiting for auth and sensitive endpoints.
3. Add CSRF protection where session-based forms exist.
4. Add secure headers.
5. Add input sanitization and output escaping.
6. Add upload size and MIME validation.
7. Add database indexes for performance.
8. Add queue worker for notifications and reports.
9. Add caching for permissions, settings, and dashboards.
10. Add automated database backup strategy.
11. Add CI/CD pipeline using GitHub Actions.
12. Add Dockerfile for backend.
13. Add Dockerfile for frontend.
14. Add docker-compose.yml for local development.
15. Add Kubernetes deployment manifests for production-ready scaling.
16. Add smoke tests.
17. Add README deployment guide.
18. Add .env.example with all required variables.

Ensure secrets are never committed.
Ensure production uses HTTPS.
Ensure Paystack webhook verification is secure.
```
