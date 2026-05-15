# Multi-HR Recruitment and Workforce Management Platform

## Documentation Index

This `/docs` folder contains the complete project planning documentation for building the Multi-HR Recruitment and Workforce Management Platform.

The documentation is separated by concern to make implementation easier, maintainable, and scalable.

## Files

| File | Purpose |
|---|---|
| `01-PRODUCT-REQUIREMENTS-DOCUMENT.md` | Product scope, goals, users, modules, acceptance criteria |
| `02-USER-ROLES-AND-PERMISSIONS.md` | Role definitions, responsibilities, permissions matrix |
| `03-SYSTEM-ARCHITECTURE.md` | High-level system architecture and module boundaries |
| `04-BACKEND-ARCHITECTURE.md` | PHP MVC backend architecture, services, repositories, middleware |
| `05-FRONTEND-ARCHITECTURE.md` | React/Tailwind frontend structure, dashboards, UI standards |
| `06-DATABASE-DESIGN.md` | Database design approach and core data models |
| `07-API-DESIGN-AND-CONTRACTS.md` | REST API standards, response formats, sample contracts |
| `08-KEY-WORKFLOWS-AND-BUSINESS-LOGIC.md` | Recruitment, payment, onboarding, placement workflows |
| `09-SECURITY-SCALABILITY-AND-DEPLOYMENT.md` | Security, caching, queues, infrastructure, CI/CD |
| `10-IMPLEMENTATION-BLUEPRINT.md` | Project structure, development standards, build order |
| `11-PHASE-BY-PHASE-WORKFLOW.md` | Logical implementation phases from MVP to production |
| `12-COPILOT-READY-PROMPTS.md` | GitHub Copilot prompts for each implementation phase |

## Recommended Usage

Place this folder inside your project root:

```text
project-root/
  docs/
  backend/
  frontend/
  database/
  README.md
```

Use the files in this order:

1. Start with the PRD.
2. Review roles and permissions.
3. Confirm system, backend, frontend, and database architecture.
4. Implement phase by phase using the workflow file.
5. Use the Copilot prompts to generate code module by module.
6. Update the documentation as implementation decisions evolve.

## Preferred Technology Stack

Backend:

- PHP 8+
- Custom MVC architecture
- RESTful APIs with structured JSON
- OOP, PSR-4, PSR-12
- SOLID principles
- Dependency injection
- Repository pattern
- Service-based business logic
- Thin controllers

Frontend:

- React.js
- Tailwind CSS
- JavaScript/jQuery
- AJAX with loading states
- Alpine.js for lightweight interactivity where appropriate

Database:

- MySQL
- InnoDB
- Foreign keys
- Indexes
- Transactions

Libraries and Tools:

- `vlucas/phpdotenv`
- `PHPMailer`
- `flatpickr.js`
- `Chart.js`
- Paystack
- Docker
- Kubernetes
- GitHub Actions CI/CD
