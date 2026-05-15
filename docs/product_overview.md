## Product Overview

The application is a **Multi-HR Recruitment and Workforce Management Platform** built to connect **Recruiters, Job Seekers, HR Officers, Relationship Officers, and Super Admins** in a single ecosystem.

The primary goal of the platform is to streamline recruitment operations, improve transparency, strengthen candidate-job matching, and reduce financial leakages or irregularities in HR remittance and workforce placement processes.

The system should support job posting, candidate onboarding, HR performance tracking, candidate discovery, wallet-based payments, communication tools, online assessments, interview workflows, and operational reporting.

Expected users

## Super admin 
The Super Admin is the highest-level user with unrestricted access across the platform.,
### Responsibilities

    * Manage platform-wide settings
    * Manage all users and roles
    * Approve, suspend, or deactivate accounts when necessary
    * Manage HR officers, recruiters and relationship officers
    * Monitor operational reports, financial reports, and performance reports
    * Configure fees, commissions, remittance rules, and system policies
    * View all jobs, applications, placements, wallet transactions, and platform activity logs
    * Oversee compliance, governance, and audit controls

## Relationship Officers
Relationship Officers support job operations and employer-facing job management.

### Responsibilities
    * Post jobs
    * Update job listings
    * Manage assigned jobs
    * Support employer job fulfillment workflows
    * Coordinate with HR officers and Recruiters when necessary


## HR Officer 
HR Officers are responsible for candidate sourcing, job matching, screening, placement coordination, and recruitment process management.

### Responsibilities
    * Post job opportunities where permitted
    * Manage candidate pipelines
    * Reach out to job seekers about job opportunities
    * Match candidates to suitable job openings
    * Conduct interviews and assessments
    * Administer screening tests
    * Issue work agreements and job descriptions
    * Track onboarding, placement, and recruitment lifecycle progress
    * Manage communications with both job seekers and Recruiters
    * Monitor assigned candidates and their placement outcomes
    * Be evaluated based on onboarded and successfully placed candidates


## Recruiters 
Recruiters are organizations or individuals seeking to hire qualified candidates.

### Responsibilities

    * Register and manage employer accounts
    * open, assign or close jobs
    * Complete company profile and verification details
    * Fund wallet using Paystack
    * Pay for platform services
    * Post and manage job vacancies
    * Review matched candidates
    * Access candidate discovery tools
    * View candidate details only after required registration and payment
    * Send interview invitations through the candidate’s assigned HR officer
    * Monitor job posting performance and recruitment progress

## Job Seekers

Job seekers are candidates seeking job opportunities through the platform.

### Responsibilities

* Register and create an account
* Complete personal and professional profile
* Upload CV, guarantor details, and required supporting documents
* Add work history, skills, qualifications, certifications, and preferences
* Receive notifications for jobs relevant to their skills and experience
* Browse job listings through a job discovery page
* View full job details and apply only after registration
* Take online tests and virtual assessments
* Communicate with HR officers through in-app messaging
* Participate in interviews via in-app video calls
* Maintain wallet for payments where applicable
* Optionally register with an HR referral code


---

# 1. Core Objectives

The platform should:

* Centralize recruitment and candidate management across multiple HR stakeholders
* Enable Recruiters to post jobs and discover suitable candidates
* Enable job seekers to register, build profiles, apply for jobs, and participate in recruitment processes
* Allow HR officers to manage candidate sourcing, screening, matching, interviews, and placement workflows
* Track HR performance through referral and placement attribution
* Provide wallet-based payment functionality for users
* Improve accountability and transparency in HR-related remittance operations
* Support secure communication, testing, and interview processes within the platform

---

# 2. User Roles and Permissions

## 2.1 Super Admin

The Super Admin is the highest-level user with unrestricted access across the platform.

### Responsibilities

* Manage platform-wide settings
* Manage all users and roles
* Approve, suspend, or deactivate accounts when necessary
* Manage HR officers and relationship officers
* Monitor operational reports, financial reports, and performance reports
* Configure fees, commissions, remittance rules, and system policies
* View all jobs, applications, placements, wallet transactions, and platform activity logs
* Oversee compliance, governance, and audit controls

---

## 2.2 Recruiters

Recruiters are organizations or individuals seeking to hire qualified candidates.

### Responsibilities

* Register and manage employer accounts
* open, assign or close jobs
* Complete company profile and verification details
* Fund wallet using Paystack
* Pay for platform services
* Post and manage job vacancies
* Review matched candidates
* Access candidate discovery tools
* View candidate details only after required registration and payment
* Send interview invitations through the candidate’s assigned HR officer
* Monitor job posting performance and recruitment progress

---

## 2.3 Job Seekers

Job seekers are candidates seeking job opportunities through the platform.

### Responsibilities

* Register and create an account
* Complete personal and professional profile
* Upload CV, guarantor details, and required supporting documents
* Add work history, skills, qualifications, certifications, and preferences
* Receive notifications for jobs relevant to their skills and experience
* Browse job listings through a job discovery page
* View full job details and apply only after registration
* Take online tests and virtual assessments
* Communicate with HR officers through in-app messaging
* Participate in interviews via in-app video calls
* Maintain wallet for payments where applicable
* Optionally register with an HR referral code

### Referral Requirement

During registration, a job seeker may enter an **optional HR referral code**. This code will be used to:

* Link the job seeker to the onboarding HR officer
* Measure HR officer performance
* Determine payment, commission, or reward eligibility based on successful onboarding and placement

---

## 2.4 HR Officers

HR Officers are responsible for candidate sourcing, job matching, screening, placement coordination, and recruitment process management.

### Responsibilities

* Post job opportunities where permitted
* Manage candidate pipelines
* Reach out to job seekers about job opportunities
* Match candidates to suitable job openings
* Conduct interviews and assessments
* Administer screening tests
* Issue work agreements and job descriptions
* Track onboarding, placement, and recruitment lifecycle progress
* Manage communications with both job seekers and Recruiters
* Monitor assigned candidates and their placement outcomes
* Be evaluated based on onboarded and successfully placed candidates

---

## 2.5 Relationship Officers

Relationship Officers support job operations and employer-facing job management.

### Responsibilities

* Post jobs
* Update job listings
* Manage assigned jobs
* Support employer job fulfillment workflows
* Coordinate with HR officers and Recruiters when necessary

---

# 3. Core Modules and Features

## 3.1 Authentication and Account Management

* User registration by role
* Secure login and logout
* Password reset and account recovery
* Role-based access control
* Email and phone verification
* Optional KYC or admin approval for selected account types
* HR referral code support for job seeker onboarding

---

## 3.2 User Profile Management

### Job Seeker Profile

* Personal details
* Contact information
* Skills and competencies
* Employment history
* Education history
* Certifications
* CV upload
* Guarantor details
* Other required documents
* Preferred location, role type, and industry

### Employer Profile

* Company name
* Company registration details
* Industry and company type
* Contact persons
* Hiring preferences
* Billing details
* Verification status

### HR Officer / Relationship Officer Profile

* Personal and staff details
* Department or assignment
* Performance summary
* Candidate activity records
* Job management permissions

---

## 3.3 Job Management

* Job creation and publishing
* Draft, published, closed, and archived job states
* Job categories and subcategories
* Skills and qualification requirements
* Location, salary range, job type, and experience level
* Application deadline
* Internal approval flow if needed
* Job editing and updating
* Job status tracking

---

## 3.4 Job Discovery for Job Seekers

* Public-facing job discovery page
* Search and filtering by title, location, salary, skill, industry, and job type
* Registered job seekers can view full job details
* Only registered job seekers can apply
* Recommended jobs based on profile and skills
* Saved jobs and application history

---

## 3.5 Candidate Discovery for Recruiters

* Candidate search and discovery page for Recruiters
* Search and filter candidates by skill, experience, qualification, location, and industry fit
* Match scoring against employer job specifications
* Only registered and paid Recruiters can view full candidate details
* Recruiters can request interviews through the candidate’s assigned HR officer

---

## 3.6 Candidate Management

* Candidate listing and segmentation
* Candidate status tracking
* Shortlisting and rejection workflows
* Interview scheduling support
* Candidate-job matching engine
* Notes, comments, and internal evaluations
* Assignment of candidates to HR officers

---

## 3.7 Application Management

* Apply to job
* Track application status
* HR and employer review stages
* Screening, shortlisted, interview, offered, hired, rejected, withdrawn
* Candidate communication history
* Application analytics and reporting

---

## 3.8 Assessments and Virtual Testing

* Online test creation and management
* Timed assessments
* Objective and descriptive questions
* Auto-grading for objective tests
* Score tracking and candidate evaluation
* Test invitations and submission monitoring

---

## 3.9 Interview Management

* Interview scheduling
* Interview feedback forms
* Interview stage progression
* In-app video call support
* Interview invitation workflow
* Employer-to-candidate interview requests routed through assigned HR officer

---

## 3.10 Messaging and Communication

* In-app messaging between users where permitted by role
* Employer to HR communication
* HR to job seeker communication
* System notifications and alerts
* Email and in-app notification support
* Communication history logs

---

## 3.11 Wallet and Payment Module

All users except the **Super Admin** must have an in-app wallet.

### Wallet Features

* Wallet creation per eligible user
* Wallet funding through Paystack
* Wallet balance tracking
* Payment deductions for services
* Transaction history
* Refund handling where applicable
* Payment status logging
* Financial audit trail

### Payment Use Cases

* Recruiters paying to post jobs or unlock candidate details
* Job seekers making any required platform payments
* Service fees and platform charges
* HR remittance and commission tracking logic where applicable

---

## 3.12 HR Referral and Performance Tracking

* Referral code generation for HR officers
* Job seeker-to-HR linkage during onboarding
* Candidate lifecycle attribution
* Placement attribution
* Performance dashboards for HR officers
* Commission/remittance tracking rules
* Success metrics based on onboarded, active, shortlisted, interviewed, and hired candidates

---

## 3.13 Agreements and Documentation

* Generate or upload work agreements
* Generate or upload job descriptions
* Document sharing between parties
* Candidate document verification
* Version history where needed

---

## 3.14 Reporting and Analytics

* User registration metrics
* Job posting metrics
* Application conversion metrics
* Candidate pipeline analytics
* Employer activity metrics
* HR officer performance reports
* Placement and hiring reports
* Wallet and transaction reports
* Operational and compliance reports

---

## 3.15 Admin Settings and Controls

* App-wide configuration
* Wallet and payment settings
* Referral and commission rules
* Job posting pricing rules
* Notification settings
* Role and permission controls
* Content moderation and review tools
* Audit logs and system logs

---

# 4. Key Business Rules

## Registration and Access

* Job seekers may register with or without an HR referral code
* Only registered job seekers can view full job details and apply
* Only registered Recruiters who have paid can view detailed candidate profiles
* Super Admin does not require a wallet
* All other user roles must have wallets

## Candidate Discovery

* Recruiters can discover candidates matching their job requirements
* Access to candidate details must be gated by registration and payment
* Employer interview invitations must go through the assigned HR officer

## Financial Rules

* Wallet funding must be done through Paystack
* Transactions must be logged and traceable
* Platform fees and remittance logic should be configurable
* HR performance-related payments should be based on defined business rules

## Recruitment Flow

* Job seekers apply to jobs
* HR officers manage the screening and matching process
* Recruiters review matched candidates
* Interviews and assessments are coordinated through platform workflows
* Offer, agreement, and job description handling should follow structured workflow states

---

# 5. Suggested Non-Functional Requirements

## Security

* Role-based access control
* Secure authentication and session/JWT handling
* Input validation and output escaping
* Secure file upload restrictions
* HTTPS enforcement
* Audit logging for critical actions
* Data privacy and access restrictions

## Scalability

* Modular architecture
* Queue-based background processing for notifications, emails, and reports
* Caching for high-read modules
* Search indexing for jobs and candidates
* Media/file storage strategy for documents and CVs

## Performance

* Fast job and candidate discovery
* Paginated listings
* Optimized search and filtering
* Database indexing on key entities
* CDN/object storage for uploaded files where needed

## Reliability

* Transaction integrity for wallet operations
* Error logging and monitoring
* Retry mechanisms for payment and notification workflows
* Backup and disaster recovery strategy

## Usability

* Mobile-first responsive interface
* Clear dashboards for each role
* Guided onboarding flows
* Accessible forms and structured user journeys

---

# 6. Recommended Workflow Summary

## Employer Workflow

Register → Verify account → Fund wallet → Post job → Receive matched candidates → Pay to unlock candidate details → Invite candidate for interview through HR → Complete recruitment process

## Job Seeker Workflow

Register → Optionally enter HR referral code → Complete profile → Upload documents → Discover jobs → Apply for jobs → Take tests → Attend interview → Receive offer/work agreement

## HR Officer Workflow

Onboard candidates → Manage candidates → Match candidates to jobs → Communicate with candidates → Conduct assessments/interviews → Coordinate employer interactions → Track placements and performance

## Relationship Officer Workflow

Create/update jobs → Support employer-side recruitment activities → Coordinate job visibility and job data quality

## Super Admin Workflow

Manage users → Configure system settings → Monitor reports → Manage HR operations → Track remittance/performance → Oversee compliance and platform activity