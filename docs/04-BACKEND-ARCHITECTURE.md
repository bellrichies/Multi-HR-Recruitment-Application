# Backend Architecture

## Backend Stack

- PHP 8+
- Custom MVC
- OOP
- PSR-4 autoloading
- PSR-12 coding style
- RESTful APIs
- JSON responses
- MySQL with PDO
- `vlucas/phpdotenv`
- PHPMailer
- Paystack integration
- Repository pattern
- Service layer
- Middleware-based authentication and authorization

## Backend Request Lifecycle

```text
HTTP Request
  -> public/index.php
  -> Router
  -> Middleware
  -> Controller
  -> Request Validator
  -> Service
  -> Repository
  -> Database
  -> Resource/Transformer
  -> JSON Response
```

## Backend Folder Structure

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
    uploads/
  routes/
    api.php
    web.php
  storage/
    logs/
    cache/
    queues/
  tests/
  composer.json
  .env.example
```

## Core Classes

### Application

Responsibilities:

- Bootstrap environment
- Load configuration
- Register routes
- Register services
- Dispatch requests

### Router

Responsibilities:

- Register routes
- Match incoming requests
- Execute middleware
- Execute controller action

### Request

Responsibilities:

- Read HTTP method
- Read request path
- Read query parameters
- Read JSON payload
- Read files
- Read headers

### Response

Responsibilities:

- Return JSON success response
- Return JSON error response
- Return validation response
- Set HTTP status code

### Database

Responsibilities:

- Manage PDO singleton connection
- Load database credentials from `.env`
- Configure PDO error mode
- Provide transaction helpers

### Container

Responsibilities:

- Register dependencies
- Resolve classes
- Support dependency injection

## Module Internal Structure

Example for Jobs module:

```text
app/Modules/Jobs/
  Controllers/
    JobController.php
  Services/
    JobService.php
  Repositories/
    JobRepository.php
    JobSkillRepository.php
    JobAssignmentRepository.php
  Requests/
    CreateJobRequest.php
    UpdateJobRequest.php
  Resources/
    JobResource.php
    JobCollection.php
```

## Controller Rules

Controllers should:

- Be thin
- Validate requests
- Call services
- Return responses

Controllers should not:

- Run SQL
- Process payment logic
- Perform large business workflows
- Directly manipulate wallet balances
- Directly send emails unless very simple and delegated

## Service Rules

Services should:

- Handle business logic
- Enforce ownership rules
- Start and commit database transactions
- Call repositories
- Call external integration classes
- Trigger notifications
- Trigger audit logs

## Repository Rules

Repositories should:

- Execute queries
- Return data
- Keep SQL organized
- Use prepared statements
- Avoid business logic

## Middleware

Required middleware:

```text
AuthMiddleware
PermissionMiddleware
RateLimitMiddleware
CsrfMiddleware
JsonMiddleware
SecurityHeadersMiddleware
```

## Authentication

Use JWT authentication for APIs.

Recommended:

- Short-lived access token
- Refresh token rotation
- Logout token invalidation
- Password reset token expiration
- Secure HTTP-only cookie for web implementation where possible

## Authorization

Use permission middleware:

```php
$router->post('/api/v1/jobs', [JobController::class, 'store'])
    ->middleware(['auth', 'permission:jobs.create']);
```

Service layer must still verify ownership:

```text
Recruiter can update only own jobs.
HR Officer can manage only assigned candidates.
Relationship Officer can manage only assigned jobs.
Super Admin can access all records.
```

## Backend Naming Conventions

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

## Example Backend Wiring: Create Job

```text
POST /api/v1/jobs
  -> AuthMiddleware
  -> PermissionMiddleware: jobs.create
  -> JobController::store()
  -> CreateJobRequest validates input
  -> JobService::createJob()
  -> JobRepository::create()
  -> JobSkillRepository::syncSkills()
  -> AuditLogService::record()
  -> NotificationService::notifyAdminsIfApprovalRequired()
  -> JobResource formats response
```

## Example Backend Wiring: Candidate Unlock

```text
POST /api/v1/candidates/{id}/unlock
  -> AuthMiddleware
  -> PermissionMiddleware: candidates.unlock
  -> CandidateUnlockController::store()
  -> CandidateUnlockService::unlock()
  -> WalletService::debit()
  -> CandidateUnlockRepository::create()
  -> AuditLogService::record()
  -> NotificationService::notify()
  -> JSON response
```
