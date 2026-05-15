# Key Workflows and Business Logic

## Recruiter Onboarding Workflow

```text
Recruiter registers
  -> Email/phone verification
  -> Completes company profile
  -> Uploads verification documents
  -> Account status becomes pending verification
  -> Super Admin reviews
  -> Account approved or rejected
  -> Recruiter funds wallet
  -> Recruiter can post jobs and access paid services
```

Business rules:

1. Recruiter cannot access paid candidate discovery until account is active.
2. Recruiter cannot post jobs if company profile is incomplete.
3. Recruiter may require verification before job publishing depending on system policy.
4. Verification decisions must be audited.

## Job Seeker Onboarding Workflow

```text
Job seeker registers
  -> Optional HR referral code is captured
  -> Email/phone verification
  -> Completes profile
  -> Uploads CV and documents
  -> Adds skills, experience, education, preferences
  -> Profile completion score is calculated
  -> Candidate becomes searchable/matchable
  -> Candidate receives job recommendations
```

Business rules:

1. Candidate profile completion must be calculated automatically.
2. Candidate should not be fully visible to recruiters unless unlock rules are satisfied.
3. Candidate documents should be protected from unauthorized access.
4. HR referral code should link candidate to HR Officer where applicable.

## Job Posting Workflow

```text
Recruiter / HR Officer / Relationship Officer creates job
  -> Job saved as draft
  -> Required fields validated
  -> Job submitted for approval if policy requires approval
  -> Super Admin or authorized officer approves
  -> Job becomes published/open
  -> Candidates can apply
  -> HR Officers can match candidates
  -> Recruiter monitors progress
```

Business rules:

1. Closed jobs cannot receive applications.
2. Jobs must have a clear owner.
3. Jobs should have status transition validation.
4. Job approval requirement should be configurable.
5. Every job status change must be audited.

## Candidate Matching Workflow

```text
Job is open
  -> System compares job requirements with candidate profiles
  -> Matching score is generated
  -> HR Officer reviews recommended candidates
  -> HR Officer shortlists candidates
  -> Candidate is notified
  -> Candidate applies or confirms interest
  -> Recruiter sees permitted candidate summary
  -> Full profile access requires payment/unlock if configured
```

Business rules:

1. Candidate match score should be explainable.
2. HR Officer can manually override or add candidates.
3. Recruiter should not see private candidate data before unlock.
4. Candidate matching should support reprocessing when profile or job changes.

## Candidate Unlock Workflow

```text
Recruiter views candidate summary
  -> Recruiter clicks unlock full profile
  -> System checks recruiter account status
  -> System checks wallet balance
  -> Fee is calculated from platform settings
  -> Wallet is debited
  -> Transaction is recorded
  -> Candidate profile access is granted
  -> Audit log is created
```

Business rules:

1. Candidate unlock requires sufficient wallet balance.
2. Wallet debit must be atomic.
3. Unlock record must reference the transaction.
4. Duplicate unlock for the same candidate/job should not double-charge if still active.
5. Unlock access may expire based on platform settings.

## Application Pipeline Workflow

```text
Candidate applies
  -> Application record is created
  -> Pipeline stage is set to applied
  -> HR Officer reviews
  -> Candidate may be moved to screening
  -> Candidate may be assigned assessment
  -> Candidate may be shortlisted
  -> Interview may be scheduled
  -> Offer may be issued
  -> Candidate may be placed
```

Business rules:

1. Stage changes must be logged.
2. Invalid stage transitions should be blocked.
3. Recruiter can view progress for own jobs.
4. Candidate can view own application status.
5. HR Officer manages assigned pipeline.

## Interview Request Workflow

```text
Recruiter selects candidate
  -> Recruiter sends interview request
  -> Request goes to assigned HR Officer
  -> HR Officer reviews candidate availability
  -> HR Officer schedules interview
  -> Candidate receives invite
  -> Recruiter receives invite
  -> Interview is completed
  -> HR Officer records feedback
  -> Recruiter submits feedback
  -> Application pipeline is updated
```

Business rules:

1. Recruiter should not directly schedule candidate interview without HR workflow unless allowed.
2. HR Officer must coordinate candidate communication.
3. Interview changes must notify all participants.
4. Feedback must be linked to interview and application.

## Placement Workflow

```text
Candidate passes screening/interview
  -> Recruiter confirms selection
  -> HR Officer marks candidate as offer pending
  -> Offer/work agreement is issued
  -> Candidate accepts
  -> Placement is confirmed
  -> Placement fee is calculated
  -> Recruiter wallet is debited or invoice generated
  -> HR Officer performance is updated
  -> Reports are updated
```

Business rules:

1. Placement confirmation should require authorized actor.
2. Placement fee should come from configured pricing.
3. HR Officer performance should update only after confirmed placement.
4. Placement should be tied to job, application, recruiter, and candidate.

## Wallet Funding Workflow

```text
Recruiter initiates wallet funding
  -> Paystack transaction is initialized
  -> Recruiter completes payment
  -> Paystack callback/webhook is received
  -> Backend verifies transaction with Paystack
  -> Wallet is credited
  -> Ledger transaction is created
  -> Notification is sent
```

Business rules:

1. Never trust frontend payment confirmation.
2. Always verify transaction server-side.
3. Paystack webhook must be idempotent.
4. Duplicate references must not create duplicate credits.
5. Wallet balance update and ledger insert must happen in one transaction.

## Assessment Workflow

```text
HR Officer creates assessment
  -> Adds questions
  -> Assigns assessment to candidate
  -> Candidate receives notification
  -> Candidate starts test
  -> Candidate submits answers
  -> Objective questions are auto-graded
  -> Essay questions are manually graded
  -> Result is published
  -> Application pipeline is updated
```

Business rules:

1. Candidate cannot retake assessment unless permitted.
2. Assessment timer must be enforced.
3. Submitted assessments cannot be modified.
4. Results should be visible based on role permission.

## Notification Workflow

```text
System event occurs
  -> Notification service receives event
  -> In-app notification is created
  -> Email job is queued
  -> User sees notification
  -> User marks notification as read
```

Business rules:

1. Notification creation should not block critical workflows.
2. Email sending should use queues.
3. Failed notification jobs should be retryable.
