# Database Design

## Database Engine

Use MySQL with InnoDB.

## Database Design Principles

- Use foreign keys.
- Use indexes on frequently queried columns.
- Use transactions for financial and critical workflows.
- Use soft deletes where records should be recoverable.
- Use audit logs for sensitive operations.
- Avoid storing calculated balances without ledger history.
- Keep wallet transactions append-only.
- Use JSON columns only for flexible metadata, not core searchable fields.

## Common Columns

Most tables should include:

```sql
id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
created_at TIMESTAMP NULL,
updated_at TIMESTAMP NULL
```

Where soft delete is needed:

```sql
deleted_at TIMESTAMP NULL
```

Where actor tracking is needed:

```sql
created_by BIGINT UNSIGNED NULL,
updated_by BIGINT UNSIGNED NULL
```

## Users and Access Control

### users

```text
id
uuid
first_name
last_name
email
phone
password_hash
status
email_verified_at
phone_verified_at
last_login_at
created_at
updated_at
deleted_at
```

### roles

```text
id
name
slug
description
is_system
created_at
updated_at
```

### permissions

```text
id
name
slug
module
description
created_at
updated_at
```

### user_roles

```text
id
user_id
role_id
created_at
```

### role_permissions

```text
id
role_id
permission_id
created_at
```

## Admin and Staff Profiles

### admin_profiles

```text
id
user_id
staff_code
department
designation
created_at
updated_at
```

### hr_officer_profiles

```text
id
user_id
employee_code
referral_code
performance_score
active_candidate_count
successful_placements_count
created_at
updated_at
```

### relationship_officer_profiles

```text
id
user_id
employee_code
assigned_employer_count
assigned_job_count
created_at
updated_at
```

## Recruiters

### recruiter_profiles

```text
id
user_id
company_name
company_email
company_phone
company_website
industry
company_size
rc_number
address
verification_status
verified_at
created_at
updated_at
```

### recruiter_documents

```text
id
recruiter_id
document_type
file_path
status
reviewed_by
reviewed_at
rejection_reason
created_at
updated_at
```

## Job Seekers

### job_seeker_profiles

```text
id
user_id
profile_code
gender
date_of_birth
location
current_job_title
years_of_experience
salary_expectation_min
salary_expectation_max
availability_status
profile_completion_percentage
assigned_hr_officer_id
referred_by_hr_officer_id
created_at
updated_at
```

### job_seeker_skills

```text
id
job_seeker_id
skill_name
proficiency_level
created_at
```

### job_seeker_work_experiences

```text
id
job_seeker_id
company_name
job_title
start_date
end_date
is_current
description
created_at
updated_at
```

### job_seeker_educations

```text
id
job_seeker_id
institution
qualification
field_of_study
start_year
end_year
created_at
updated_at
```

### job_seeker_certifications

```text
id
job_seeker_id
name
issuer
issue_date
expiry_date
file_path
created_at
updated_at
```

### job_seeker_documents

```text
id
job_seeker_id
document_type
file_path
status
reviewed_by
reviewed_at
created_at
updated_at
```

### guarantors

```text
id
job_seeker_id
full_name
phone
email
relationship
address
occupation
document_path
created_at
updated_at
```

## Jobs

### jobs

```text
id
uuid
recruiter_id
created_by
assigned_hr_officer_id
assigned_relationship_officer_id
title
slug
description
requirements
responsibilities
location
employment_type
work_mode
salary_min
salary_max
currency
experience_level
application_deadline
status
published_at
closed_at
created_at
updated_at
deleted_at
```

### job_skills

```text
id
job_id
skill_name
required_level
created_at
```

### job_assignments

```text
id
job_id
assigned_to_user_id
assigned_by_user_id
assignment_type
status
created_at
updated_at
```

## Applications and Matching

### job_applications

```text
id
job_id
job_seeker_id
applied_by
status
current_stage
cover_letter
match_score
submitted_at
created_at
updated_at
```

### application_stage_logs

```text
id
application_id
from_stage
to_stage
changed_by
note
created_at
```

### candidate_matches

```text
id
job_id
job_seeker_id
matched_by
match_score
match_reason
status
created_at
updated_at
```

### candidate_unlocks

```text
id
recruiter_id
job_seeker_id
job_id
transaction_id
unlocked_by
expires_at
created_at
```

## Assessments

### assessments

```text
id
title
description
assessment_type
duration_minutes
pass_mark
created_by
status
created_at
updated_at
```

### assessment_questions

```text
id
assessment_id
question_text
question_type
options_json
correct_answer_json
score
created_at
updated_at
```

### assessment_assignments

```text
id
assessment_id
job_seeker_id
job_id
assigned_by
status
due_date
started_at
submitted_at
created_at
updated_at
```

### assessment_answers

```text
id
assignment_id
question_id
answer_json
score_awarded
created_at
updated_at
```

### assessment_results

```text
id
assignment_id
total_score
percentage
status
graded_by
graded_at
created_at
updated_at
```

## Interviews

### interviews

```text
id
job_id
application_id
job_seeker_id
recruiter_id
scheduled_by
interview_type
meeting_link
scheduled_at
duration_minutes
status
created_at
updated_at
```

### interview_feedback

```text
id
interview_id
submitted_by
rating
feedback
recommendation
created_at
updated_at
```

## Wallet and Payments

### wallets

```text
id
user_id
wallet_type
currency
available_balance
ledger_balance
status
created_at
updated_at
```

### wallet_transactions

```text
id
wallet_id
user_id
reference
transaction_type
direction
amount
balance_before
balance_after
status
description
metadata_json
created_at
updated_at
```

### payments

```text
id
user_id
wallet_id
provider
provider_reference
internal_reference
amount
currency
status
purpose
metadata_json
verified_at
created_at
updated_at
```

### payment_webhook_events

```text
id
provider
event_type
event_reference
payload_json
processed_at
created_at
```

### remittances

```text
id
recruiter_id
job_id
placement_id
amount_due
amount_paid
commission_amount
status
due_date
paid_at
created_at
updated_at
```

## Messaging and Notifications

### conversations

```text
id
conversation_type
created_by
created_at
updated_at
```

### conversation_participants

```text
id
conversation_id
user_id
created_at
```

### messages

```text
id
conversation_id
sender_id
message_body
attachment_path
read_at
created_at
updated_at
```

### notifications

```text
id
user_id
title
body
type
channel
data_json
read_at
created_at
updated_at
```

## Audit Logs

### audit_logs

```text
id
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

### activity_logs

```text
id
user_id
activity_type
description
metadata_json
created_at
```

## Recommended Indexes

Add indexes for:

```text
users.email
users.phone
users.status
roles.slug
permissions.slug
recruiter_profiles.user_id
recruiter_profiles.verification_status
job_seeker_profiles.user_id
job_seeker_profiles.assigned_hr_officer_id
job_seeker_profiles.location
jobs.recruiter_id
jobs.status
jobs.slug
jobs.location
jobs.assigned_hr_officer_id
jobs.assigned_relationship_officer_id
job_applications.job_id
job_applications.job_seeker_id
job_applications.current_stage
candidate_matches.job_id
candidate_matches.job_seeker_id
wallet_transactions.wallet_id
wallet_transactions.reference
payments.provider_reference
payments.internal_reference
audit_logs.actor_id
audit_logs.module
audit_logs.entity_type
```
