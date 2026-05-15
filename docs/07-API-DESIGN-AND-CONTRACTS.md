# API Design and Contracts

## API Style

Use RESTful APIs with structured JSON responses.

## Versioning

All endpoints should be prefixed:

```text
/api/v1
```

## Standard Success Response

```json
{
  "success": true,
  "message": "Request completed successfully.",
  "data": {},
  "meta": {}
}
```

## Standard Error Response

```json
{
  "success": false,
  "message": "An error occurred.",
  "errors": {}
}
```

## Validation Error Response

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": ["Email address is required."]
  }
}
```

## Pagination Response

```json
{
  "success": true,
  "message": "Records retrieved successfully.",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 120,
    "last_page": 6
  }
}
```

## Authentication Endpoints

```text
POST /api/v1/auth/register/job-seeker
POST /api/v1/auth/register/recruiter
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/auth/me
POST /api/v1/auth/forgot-password
POST /api/v1/auth/reset-password
```

## Role and Permission Endpoints

```text
GET    /api/v1/roles
POST   /api/v1/roles
GET    /api/v1/roles/{id}
PUT    /api/v1/roles/{id}
DELETE /api/v1/roles/{id}

GET    /api/v1/permissions
POST   /api/v1/permissions
PUT    /api/v1/roles/{id}/permissions
POST   /api/v1/users/{id}/roles
```

## User Management Endpoints

```text
GET    /api/v1/users
POST   /api/v1/users
GET    /api/v1/users/{id}
PUT    /api/v1/users/{id}
POST   /api/v1/users/{id}/suspend
POST   /api/v1/users/{id}/activate
POST   /api/v1/users/{id}/deactivate
```

## Recruiter Endpoints

```text
GET    /api/v1/recruiters
GET    /api/v1/recruiters/{id}
PUT    /api/v1/recruiters/profile
POST   /api/v1/recruiters/documents
POST   /api/v1/recruiters/{id}/verify
POST   /api/v1/recruiters/{id}/reject
GET    /api/v1/recruiters/dashboard
```

## Job Seeker Endpoints

```text
GET    /api/v1/job-seekers
GET    /api/v1/job-seekers/{id}
PUT    /api/v1/job-seekers/profile
POST   /api/v1/job-seekers/skills
POST   /api/v1/job-seekers/work-experiences
POST   /api/v1/job-seekers/educations
POST   /api/v1/job-seekers/certifications
POST   /api/v1/job-seekers/documents
POST   /api/v1/job-seekers/guarantors
GET    /api/v1/job-seekers/dashboard
```

## Job Endpoints

```text
GET    /api/v1/jobs
POST   /api/v1/jobs
GET    /api/v1/jobs/{id}
PUT    /api/v1/jobs/{id}
POST   /api/v1/jobs/{id}/submit-for-approval
POST   /api/v1/jobs/{id}/approve
POST   /api/v1/jobs/{id}/publish
POST   /api/v1/jobs/{id}/pause
POST   /api/v1/jobs/{id}/close
POST   /api/v1/jobs/{id}/assign-hr-officer
POST   /api/v1/jobs/{id}/assign-relationship-officer
GET    /api/v1/public/jobs
GET    /api/v1/public/jobs/{slug}
```

## Application Endpoints

```text
GET    /api/v1/applications
POST   /api/v1/jobs/{id}/apply
GET    /api/v1/applications/{id}
POST   /api/v1/applications/{id}/move-stage
POST   /api/v1/applications/{id}/shortlist
POST   /api/v1/applications/{id}/reject
POST   /api/v1/applications/{id}/withdraw
```

## Candidate Discovery Endpoints

```text
GET    /api/v1/candidates/discover
GET    /api/v1/candidates/{id}/summary
GET    /api/v1/candidates/{id}/full-profile
POST   /api/v1/candidates/{id}/unlock
POST   /api/v1/jobs/{id}/match-candidates
```

## Wallet and Payment Endpoints

```text
GET    /api/v1/wallet
POST   /api/v1/wallet/fund
GET    /api/v1/wallet/transactions
POST   /api/v1/payments/paystack/callback
POST   /api/v1/payments/paystack/webhook
GET    /api/v1/payments
GET    /api/v1/payments/{id}
```

## Assessment Endpoints

```text
GET    /api/v1/assessments
POST   /api/v1/assessments
GET    /api/v1/assessments/{id}
PUT    /api/v1/assessments/{id}
POST   /api/v1/assessments/{id}/questions
POST   /api/v1/assessments/{id}/assign
POST   /api/v1/assessment-assignments/{id}/start
POST   /api/v1/assessment-assignments/{id}/submit
POST   /api/v1/assessment-assignments/{id}/grade
```

## Interview Endpoints

```text
GET    /api/v1/interviews
POST   /api/v1/interviews
GET    /api/v1/interviews/{id}
PUT    /api/v1/interviews/{id}
POST   /api/v1/interviews/{id}/reschedule
POST   /api/v1/interviews/{id}/cancel
POST   /api/v1/interviews/{id}/feedback
```

## Messaging and Notification Endpoints

```text
GET    /api/v1/conversations
POST   /api/v1/conversations
GET    /api/v1/conversations/{id}/messages
POST   /api/v1/conversations/{id}/messages

GET    /api/v1/notifications
POST   /api/v1/notifications/{id}/read
POST   /api/v1/notifications/read-all
```

## Report Endpoints

```text
GET /api/v1/reports/admin/summary
GET /api/v1/reports/hr-officer/summary
GET /api/v1/reports/relationship-officer/summary
GET /api/v1/reports/recruiter/summary
GET /api/v1/reports/job-seeker/summary
GET /api/v1/reports/financial
GET /api/v1/reports/placements
GET /api/v1/reports/audit
```

## Sample Request: Register Job Seeker

```http
POST /api/v1/auth/register/job-seeker
```

```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "08012345678",
  "password": "Password@123",
  "referral_code": "HR-AB123"
}
```

## Sample Request: Create Job

```http
POST /api/v1/jobs
```

```json
{
  "title": "Customer Service Representative",
  "description": "We are hiring a customer service representative.",
  "requirements": "Minimum OND, good communication skill.",
  "responsibilities": "Handle customer inquiries and support.",
  "location": "Lagos",
  "employment_type": "full_time",
  "work_mode": "onsite",
  "salary_min": 80000,
  "salary_max": 120000,
  "currency": "NGN",
  "experience_level": "entry",
  "application_deadline": "2026-07-30",
  "skills": ["Communication", "CRM", "Microsoft Office"]
}
```

## Sample Request: Fund Wallet

```http
POST /api/v1/wallet/fund
```

```json
{
  "amount": 50000,
  "provider": "paystack",
  "purpose": "wallet_funding"
}
```

## Sample Request: Unlock Candidate

```http
POST /api/v1/candidates/123/unlock
```

```json
{
  "job_id": 45,
  "payment_source": "wallet"
}
```

## Sample Request: Schedule Interview

```http
POST /api/v1/interviews
```

```json
{
  "application_id": 77,
  "scheduled_at": "2026-08-12 10:00:00",
  "duration_minutes": 45,
  "interview_type": "video",
  "notes": "First-stage interview"
}
```
