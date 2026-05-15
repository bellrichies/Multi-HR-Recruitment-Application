# Product Requirement Document

## Product Name

Multi-HR Recruitment and Workforce Management Platform

## Product Overview

The application is a multi-role recruitment and workforce management platform designed to connect Recruiters, Job Seekers, HR Officers, Relationship Officers, and Super Admins in one secure operational ecosystem.

The platform will streamline recruitment operations, improve candidate-job matching, reduce operational opacity, strengthen HR officer accountability, and minimize financial leakage in payments, remittances, placement fees, and service charges.

## Product Goals

The platform must:

1. Centralize recruitment operations.
2. Support employer onboarding and verification.
3. Support job seeker onboarding and profile management.
4. Allow recruiters to post jobs and access candidate discovery tools.
5. Enable HR Officers to manage candidate sourcing, matching, screening, assessments, interviews, and placement.
6. Enable Relationship Officers to manage employer-facing job operations.
7. Enable Super Admins to control platform settings, users, permissions, financial rules, reports, and audit logs.
8. Support wallet-based payments using Paystack.
9. Support online assessments and interview workflows.
10. Provide operational, financial, and performance reporting.

## Core User Types

- Super Admin
- Relationship Officer
- HR Officer
- Recruiter / Employer
- Job Seeker

## Core Modules

### 1. Authentication and Account Management

Features:

- Registration
- Login
- Logout
- Password reset
- Email verification
- Optional phone verification
- JWT authentication
- Role-based dashboard redirection
- Account status management

Account statuses:

```text
pending
active
suspended
deactivated
rejected
```

### 2. Role and Permission Management

Features:

- Manage roles
- Manage permissions
- Assign permissions to roles
- Assign roles to users
- Enforce permissions at route and service level
- Protect Super Admin role from deletion

### 3. Recruiter Management

Features:

- Recruiter registration
- Company profile
- Company verification
- Wallet funding
- Job posting
- Candidate discovery
- Candidate profile unlock
- Interview request workflow
- Recruiter dashboard

### 4. Job Seeker Management

Features:

- Candidate registration
- Personal profile
- Professional profile
- CV upload
- Guarantor details
- Skills
- Work history
- Education
- Certifications
- Job preferences
- Job application history
- Assessment history
- Interview history

### 5. Job Management

Features:

- Job creation
- Job editing
- Job approval
- Job publishing
- Job assignment
- Job closing
- Job search
- Public job discovery
- Recruiter job management
- HR/Relationship Officer job management

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

### 6. Candidate Discovery and Matching

Features:

- Candidate search
- Candidate filtering
- Candidate matching score
- Manual HR matching
- Candidate shortlisting
- Candidate unlock rules
- Paid access to full candidate details

Matching score should consider:

- Skills
- Location
- Experience
- Salary expectation
- Availability
- Education
- Assessment result
- Profile completion

### 7. Application Pipeline

Features:

- Job application
- Candidate pipeline
- Stage tracking
- Shortlisting
- Screening
- Assessment invitation
- Interview scheduling
- Offer tracking
- Placement tracking
- Rejection/withdrawal handling

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

### 8. Assessments

Features:

- Assessment creation
- Question bank
- Multiple-choice questions
- Essay questions
- Timed tests
- Assignment to candidates
- Auto grading
- Manual grading
- Result tracking

### 9. Interviews

Features:

- Interview scheduling
- Interview invitation
- Meeting link support
- Feedback management
- Recruiter feedback
- HR feedback
- Candidate interview status tracking

### 10. Wallet and Payments

Features:

- Wallet creation
- Wallet funding via Paystack
- Payment verification
- Paystack webhook handling
- Candidate unlock fee
- Job posting fee
- Assessment fee
- Placement fee
- Commission tracking
- Remittance tracking
- Wallet ledger
- Transaction reports

### 11. Messaging and Notifications

Features:

- In-app notifications
- Email notifications
- HR-to-candidate messaging
- HR-to-recruiter messaging
- Interview request notifications
- Assessment notifications
- Payment notifications

### 12. Reporting and Analytics

Reports:

- User reports
- Job reports
- Candidate reports
- Application reports
- Placement reports
- Wallet reports
- Transaction reports
- HR performance reports
- Relationship Officer performance reports
- Recruiter reports
- Audit reports

## MVP Scope

The MVP should include:

1. Authentication
2. RBAC
3. User management
4. Recruiter onboarding
5. Job seeker onboarding
6. Job posting
7. Job applications
8. Candidate matching
9. Wallet funding
10. Candidate unlock
11. Basic interviews
12. Notifications
13. Dashboards
14. Audit logs
15. Basic reports

## Post-MVP Scope

Post-MVP modules:

1. Advanced assessments
2. Advanced candidate matching
3. In-app video calls
4. Dispute management
5. Automated remittance workflows
6. Advanced analytics
7. Mobile app
8. AI-powered recommendations
9. Bulk import tools
10. Advanced compliance center

## Acceptance Criteria

The MVP is complete when:

1. Super Admin can manage users, roles, and permissions.
2. Recruiters can register, complete company profiles, fund wallet, and post jobs.
3. Job seekers can register, complete profiles, upload documents, browse jobs, and apply.
4. HR Officers can manage assigned candidates and application pipelines.
5. Relationship Officers can manage assigned jobs and employer operations.
6. Recruiters can unlock candidate details after payment.
7. Wallet funding works through verified Paystack callbacks/webhooks.
8. Every financial transaction is recorded in the wallet ledger.
9. Application pipeline stages are trackable.
10. Notifications are delivered for important events.
11. Dashboards show role-specific metrics.
12. Audit logs capture sensitive actions.
13. API routes are protected with authentication and permission middleware.
14. Frontend is responsive, clean, and role-aware.
15. Application is deployable to staging and production.
