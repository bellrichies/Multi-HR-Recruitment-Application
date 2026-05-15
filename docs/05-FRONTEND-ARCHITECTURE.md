# Frontend Architecture

## Frontend Stack

- React.js
- Tailwind CSS
- JavaScript
- jQuery/AJAX where progressive enhancement is needed
- Alpine.js for lightweight interactivity where useful
- React Router
- Chart.js
- flatpickr.js
- Axios or Fetch API wrapper

## Frontend Goals

The frontend must be:

- Mobile-first
- Responsive
- API-driven
- Fast
- Secure
- Role-aware
- Permission-aware
- Easy to maintain
- Clean and professional
- Suitable for an HR/recruitment SaaS product

## Frontend Folder Structure

```text
frontend/
  src/
    api/
      client.js
      auth.api.js
      users.api.js
      roles.api.js
      jobs.api.js
      applications.api.js
      candidates.api.js
      wallet.api.js
      reports.api.js
    assets/
    components/
      ui/
      forms/
      tables/
      modals/
      charts/
      navigation/
      feedback/
    layouts/
      PublicLayout.jsx
      AuthLayout.jsx
      DashboardLayout.jsx
    modules/
      auth/
      admin/
      recruiter/
      jobSeeker/
      hrOfficer/
      relationshipOfficer/
      jobs/
      applications/
      candidates/
      wallet/
      assessments/
      interviews/
      messages/
      notifications/
      reports/
      settings/
    routes/
      AppRoutes.jsx
      ProtectedRoute.jsx
      PermissionRoute.jsx
    store/
      auth.store.js
      user.store.js
    hooks/
      useAuth.js
      usePermissions.js
      useApi.js
      usePagination.js
    utils/
      formatCurrency.js
      formatDate.js
      permissions.js
      constants.js
  package.json
  vite.config.js
```

## Separation of Concerns

### API Layer

Responsibilities:

- Centralize HTTP requests
- Add auth token/cookie handling
- Handle base URL from environment
- Handle common errors
- Handle request/response interceptors

### Components

Responsibilities:

- Render UI only
- Receive props
- Trigger callbacks
- Avoid direct API calls where possible

### Modules

Responsibilities:

- Group feature-specific pages
- Own feature-specific forms
- Own feature-specific table views
- Own feature-specific hooks where necessary

### Layouts

Responsibilities:

- Public page layout
- Auth page layout
- Dashboard shell
- Sidebar
- Header
- Role-aware navigation

### Hooks

Responsibilities:

- Reusable stateful logic
- Pagination
- Authentication
- Permissions
- API loading state

### Utilities

Responsibilities:

- Formatters
- Constants
- Permission helpers
- Date/currency helpers

## UI Standards

Every page should include:

- Loading state
- Empty state
- Error state
- Success notification
- Breadcrumbs
- Pagination where needed
- Search where needed
- Filters where needed
- Sort where needed
- Confirmation modal for destructive actions
- Mobile responsive layout
- Accessible form labels
- Clear validation messages

## Dashboard Layout

The dashboard layout should include:

- Sidebar
- Top navigation
- User profile menu
- Notification icon
- Mobile menu
- Breadcrumbs
- Page title
- Main content slot

## Role-Based Dashboards

### Super Admin Dashboard

Widgets:

- Total users
- Total recruiters
- Total job seekers
- Total HR Officers
- Total Relationship Officers
- Total jobs
- Active jobs
- Applications
- Placements
- Revenue
- Pending verifications
- Wallet transaction volume
- Recent audit logs
- HR performance chart

### HR Officer Dashboard

Widgets:

- Assigned candidates
- Assigned jobs
- Pending screenings
- Interviews today
- Assessment results pending
- Candidates placed
- Candidate pipeline chart

### Relationship Officer Dashboard

Widgets:

- Assigned employers
- Assigned jobs
- Open jobs
- Jobs pending update
- Fulfillment progress
- Employer activity

### Recruiter Dashboard

Widgets:

- Wallet balance
- Jobs posted
- Active jobs
- Matched candidates
- Candidate unlocks
- Interviews scheduled
- Hired candidates
- Payment history

### Job Seeker Dashboard

Widgets:

- Profile completion
- Recommended jobs
- Applications
- Assessment invitations
- Interview invitations
- Messages
- Notifications
- Wallet balance where applicable

## Protected Routing

Use:

- `ProtectedRoute` for authenticated routes
- `PermissionRoute` for permission-specific pages
- Role-based dashboard redirect after login

Example route logic:

```text
Unauthenticated user -> login
Authenticated user without permission -> forbidden page
Authenticated user with permission -> allowed page
```

## Frontend Security Rules

- Do not rely on frontend permissions alone.
- Hide unauthorized actions for UX.
- Backend must always enforce permission.
- Never store secrets in frontend code.
- Use environment variables for API URL.
- Validate forms before submission.
- Escape user-generated content when rendering.

## Recommended UI Modules

```text
Auth
Admin
Recruiter
Job Seeker
HR Officer
Relationship Officer
Jobs
Applications
Candidates
Wallet
Payments
Assessments
Interviews
Messages
Notifications
Reports
Settings
```

## Frontend Acceptance Criteria

1. UI is fully responsive.
2. Every form has validation.
3. Every table has pagination where required.
4. Every API call has loading and error states.
5. Role-specific dashboards work.
6. Unauthorized actions are hidden.
7. Protected routes are enforced.
8. Dashboard navigation changes based on permissions.
9. Charts consume backend JSON cleanly.
10. Frontend can be deployed separately from backend.
