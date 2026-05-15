# User Roles and Permissions

## Role Overview

The platform uses Role-Based Access Control with granular permissions.

Authorization must be enforced at two levels:

1. Route/middleware level.
2. Service/business-rule level.

A user may have a permission but still be restricted by ownership or assignment logic.

Example:

- A recruiter can update jobs but only jobs owned by their company.
- An HR Officer can manage candidates but only assigned candidates unless granted broader permission.
- A Relationship Officer can manage assigned jobs but not all jobs.
- Super Admin can access all modules.

---

## Super Admin

The Super Admin is the highest-level platform user.

### Responsibilities

- Manage platform-wide settings
- Manage users
- Manage roles
- Manage permissions
- Approve, suspend, or deactivate accounts
- Manage HR Officers
- Manage Relationship Officers
- Manage Recruiters
- Manage Job Seekers
- Configure fees
- Configure commissions
- Configure remittance rules
- Configure system policies
- View all jobs
- View all applications
- View all placements
- View all wallet transactions
- View all platform activity logs
- Monitor operational reports
- Monitor financial reports
- Monitor performance reports
- Oversee compliance, governance, and audit controls

---

## Relationship Officer

Relationship Officers support job operations and employer-facing job management.

### Responsibilities

- Post jobs where permitted
- Update assigned job listings
- Manage assigned jobs
- Open jobs
- Assign jobs to job seekers where permitted
- Close jobs where permitted
- Support employer job fulfillment workflows
- Coordinate with HR Officers and Recruiters
- Escalate operational issues to Super Admin

---

## HR Officer

HR Officers manage candidate sourcing, job matching, screening, placement coordination, and recruitment process management.

### Responsibilities

- Post job opportunities where permitted
- Open jobs
- Assign jobs to job seekers where permitted
- Close jobs where permitted
- Manage candidate pipelines
- Reach out to job seekers about job opportunities
- Match candidates to suitable jobs
- Conduct interviews and assessments
- Administer screening tests
- Issue work agreements
- Issue job descriptions
- Track onboarding
- Track placement lifecycle
- Manage communications with job seekers
- Manage communications with recruiters
- Monitor assigned candidates
- Track placement outcomes
- Be evaluated based on onboarded and successfully placed candidates

---

## Recruiter / Employer

Recruiters are organizations or individuals seeking qualified candidates.

### Responsibilities

- Register employer account
- Manage employer account
- Complete company profile
- Submit verification details
- Fund wallet using Paystack
- Pay for platform services
- Post job vacancies
- Manage job vacancies
- Review matched candidates
- Access candidate discovery tools
- View candidate details only after registration and required payment
- Send interview invitations through the candidate’s assigned HR Officer
- Monitor job posting performance
- Monitor recruitment progress

---

## Job Seeker

Job seekers are candidates seeking job opportunities.

### Responsibilities

- Register account
- Create profile
- Complete personal profile
- Complete professional profile
- Upload CV
- Upload guarantor details
- Upload required supporting documents
- Add work history
- Add skills
- Add qualifications
- Add certifications
- Add job preferences
- Receive notifications for relevant jobs
- Browse job listings
- View full job details after registration
- Apply for jobs
- Take online tests
- Take virtual assessments
- Communicate with HR Officers
- Participate in interviews
- Maintain wallet where applicable
- Optionally register with HR referral code

---

## Recommended Permissions

```text
users.view
users.create
users.update
users.suspend
users.deactivate
users.delete

roles.view
roles.create
roles.update
roles.delete
roles.assign

permissions.view
permissions.create
permissions.update
permissions.delete
permissions.assign

recruiters.view
recruiters.create
recruiters.update
recruiters.verify
recruiters.suspend

job_seekers.view
job_seekers.create
job_seekers.update
job_seekers.verify
job_seekers.assign_hr

hr_officers.view
hr_officers.create
hr_officers.update
hr_officers.suspend
hr_officers.performance.view

relationship_officers.view
relationship_officers.create
relationship_officers.update
relationship_officers.suspend

jobs.view
jobs.create
jobs.update
jobs.approve
jobs.publish
jobs.assign
jobs.close
jobs.delete

applications.view
applications.create
applications.update
applications.shortlist
applications.reject
applications.move_stage

candidates.discover
candidates.match
candidates.unlock
candidates.view_full_profile

assessments.view
assessments.create
assessments.update
assessments.assign
assessments.grade

interviews.view
interviews.schedule
interviews.reschedule
interviews.cancel
interviews.feedback

wallet.view
wallet.fund
wallet.debit
wallet.credit
wallet.adjust

payments.view
payments.verify
payments.refund

transactions.view
transactions.export

remittances.view
remittances.create
remittances.update
remittances.mark_paid

messages.view
messages.send

notifications.view
notifications.manage

reports.view
reports.export

settings.view
settings.manage

audit.view
```

---

## Permission Matrix

| Module | Super Admin | Relationship Officer | HR Officer | Recruiter | Job Seeker |
|---|---:|---:|---:|---:|---:|
| Platform settings | Full | None | None | None | None |
| Roles and permissions | Full | None | None | None | None |
| User management | Full | Limited | Limited | None | None |
| Recruiter management | Full | Assigned only | Limited | Own account | None |
| Job seeker management | Full | Limited | Assigned only | Limited after payment | Own account |
| Job posting | Full | Permitted | Permitted | Own jobs | None |
| Job approval | Full | Limited | Limited | None | None |
| Job assignment | Full | Assigned jobs | Assigned jobs | Limited | None |
| Candidate matching | Full | Limited | Full for assigned jobs | View only | None |
| Applications | Full | Limited | Full for assigned jobs | Own jobs | Own applications |
| Candidate discovery | Full | Limited | Full | Paid/controlled | None |
| Assessments | Full | None | Full | View results only | Take tests |
| Interviews | Full | Limited | Full | Request/participate | Participate |
| Wallet | Full | None | None | Own wallet | Own wallet if enabled |
| Reports | Full | Limited | Limited | Own reports | Own reports |
| Audit logs | Full | None | Limited | None | None |

---

## RBAC Rules

1. Super Admin must always have all permissions.
2. System roles should not be deleted.
3. Permission checks must exist on every protected route.
4. Sensitive actions must be logged.
5. Ownership checks must be handled in service classes.
6. Permission names must be consistent and grouped by module.
7. Frontend must hide unauthorized actions but backend must remain the source of truth.
