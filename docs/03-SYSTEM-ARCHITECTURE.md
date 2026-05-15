# System Architecture

## Architecture Pattern

Use a modular monolith for the MVP.

The system should be structured as a monolith with strong internal module boundaries. This keeps development fast while preserving scalability and maintainability.

The platform can later evolve into service-oriented or microservice architecture if transaction volume, team size, or operational complexity requires it.

## Why Modular Monolith

Advantages:

- Faster MVP delivery
- Lower infrastructure complexity
- Easier debugging
- Easier deployment
- Stronger consistency for wallet and recruitment workflows
- Easier database transactions
- Lower cost at early stage
- Clear path to future service extraction

## High-Level Architecture

```text
React Web Application
        |
        v
RESTful API Layer
        |
        v
PHP 8 Custom MVC Backend
        |
        v
Controllers
        |
        v
Request Validators / Middleware
        |
        v
Services
        |
        v
Repositories
        |
        v
MySQL Database
```

## Supporting Services

```text
Paystack
PHPMailer SMTP
Queue Worker
Redis Cache
File Storage
Logging
Monitoring
CI/CD
Backup System
```

## Core Modules

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

## Separation of Concerns

### Controllers

Responsibilities:

- Receive HTTP request
- Call request validators
- Call services
- Return JSON responses
- Stay thin

Controllers must not contain:

- SQL queries
- Business rules
- Complex calculations
- Payment processing logic
- Authorization ownership rules beyond middleware usage

### Services

Responsibilities:

- Business logic
- Workflow orchestration
- Transaction management
- Permission-sensitive ownership checks
- Integration coordination

### Repositories

Responsibilities:

- Database reads/writes
- Query building
- Persistence abstraction
- Returning raw records or entities

Repositories must not contain business decisions.

### Request Validators

Responsibilities:

- Validate request payloads
- Return validation errors
- Normalize safe request data

### Resources / Transformers

Responsibilities:

- Format API responses
- Hide sensitive fields
- Control response shape

### Middleware

Responsibilities:

- Authentication
- Authorization
- Rate limiting
- CSRF where needed
- JSON request validation
- Security headers

### Jobs / Queues

Responsibilities:

- Email sending
- Notification dispatch
- Report generation
- Payment reconciliation
- Candidate matching recalculation
- Webhook processing where needed

## Recommended Deployment Topology

```text
Browser / Mobile App
        |
        v
Load Balancer / Reverse Proxy
        |
        v
Frontend Static App / CDN
        |
        v
Backend API Server
        |
        +--> MySQL
        +--> Redis
        +--> Queue Worker
        +--> File Storage
        +--> Mail Provider
        +--> Paystack
```

## Production Components

Minimum:

- Nginx or Apache
- PHP 8.2+
- MySQL 8+
- Redis
- Queue worker
- SSL certificate
- GitHub Actions
- Log files
- Database backups

Advanced:

- Docker
- Kubernetes
- Object storage
- Monitoring and alerting
- Centralized logging
- Horizontal API scaling
- CDN
