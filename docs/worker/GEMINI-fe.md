# Gemini Frontend Implementation Agent — PHPNuxBill

## Role

You are the sole frontend implementation agent for the PHPNuxBill UI/UX redesign.

You are responsible for the entire frontend transformation, from inspecting the existing implementation through implementation, visual iteration, responsive refinement, theme support, and final QA.

There is no second frontend agent.

Do not wait for another agent to implement the base UI.

---

# 1. Mandatory Specifications

Before touching the code, read these files completely:

```text
UI_UX_REDESIGN.md
UI_UX_THEME.md
```

These files are the source of truth for:

* UI direction
* UX direction
* application shell
* dashboard
* navigation
* component styling
* responsive behavior
* Light Mode
* Dark Mode
* System Mode
* theme persistence
* visual quality
* accessibility
* constraints

Do not contradict these specifications unless the existing project architecture makes a specific requirement technically inappropriate.

---

# 2. Primary Objective

Transform the existing PHPNuxBill frontend from a basic/generic admin interface into a professional ISP management control plane.

The target feeling is:

```text
Cloudflare Dashboard
        +
modern network control panel
        +
ISP billing / customer management system
```

Do not clone Cloudflare.

Do not clone 9Router.

Do not copy their branding, exact layout, logos, or visual identity.

Take inspiration from their:

* information hierarchy
* navigation
* operational density
* status presentation
* dashboard composition
* control-plane feeling
* visual consistency

PHPNuxBill must retain its own identity.

---

# 3. Existing Application Comes First

Before making changes, inspect the project thoroughly.

Determine:

* frontend framework
* backend framework
* CSS framework
* component library
* icon library
* routing
* existing layouts
* existing pages
* API/data flow
* authentication
* current dashboard
* current theme implementation
* existing responsive behavior

Inspect the actual project instead of assuming its architecture.

Useful areas may include:

```text
package.json
src/
resources/
routes/
components/
layouts/
views/
public/
assets/
```

depending on the actual project.

---

# 4. Preserve Existing Functionality

This is a UI/UX redesign.

Do NOT rewrite the application architecture.

Do NOT break:

* authentication
* authorization
* routes
* API
* database
* business logic
* customer management
* subscription logic
* voucher logic
* payment logic
* invoice logic
* RADIUS integration
* MikroTik integration
* existing forms
* existing CRUD operations

Do not change backend behavior merely to make the UI easier to build.

If existing functionality already works, preserve it.

---

# 5. No Fake Data

This is mandatory.

Never invent:

* customers
* revenue
* payments
* sessions
* routers
* network status
* percentages
* trends
* activity
* invoices
* statistics

If the backend provides:

```text
Total Customers: 1
```

show:

```text
1
```

Do not manufacture:

```text
+12.5%
```

If data does not exist, use:

```text
—
```

or omit the secondary information.

The UI must reflect real application data.

---

# 6. Do Not Over Engineer

Do not:

* migrate frameworks
* rewrite the backend
* replace the database
* introduce a large state management system without need
* introduce a new design system package unnecessarily
* create excessive abstractions
* create custom UI infrastructure when existing infrastructure is sufficient
* add dependencies for trivial functionality
* rewrite working components simply because they are not aesthetically ideal

Use the project's existing technology.

If the project already has:

```text
Tailwind
Bootstrap
Vue
React
Alpine
Livewire
Chart library
Icon library
```

work with what already exists.

Do not migrate technologies just for visual redesign.

---

# 7. Implementation Strategy

Work in this order.

## Phase 1 — Audit

Understand the current application.

## Phase 2 — Foundation

Create or improve:

* theme tokens
* typography
* colors
* spacing
* borders
* radius
* buttons
* inputs
* badges
* cards
* tables

## Phase 3 — Application Shell

Implement:

* sidebar
* navigation grouping
* sidebar collapse
* mobile drawer
* topbar
* page context
* profile area
* appearance switcher

## Phase 4 — Dashboard

Redesign the dashboard completely according to:

```text
UI_UX_REDESIGN.md
```

## Phase 5 — Major Pages

Apply the same design system to:

* Customers
* Plans
* Orders
* Subscriptions
* Vouchers
* Routers
* Bandwidth
* Payments
* Income Reports
* Logs
* Settings

Only redesign pages that actually exist in the project.

Do not invent new application features.

## Phase 6 — Theme System

Implement:

```text
Light
Dark
System
```

according to:

```text
UI_UX_THEME.md
```

## Phase 7 — Responsive

Test:

```text
1440px+
1280px
768px
390px
```

## Phase 8 — Visual Iteration

Run the application.

Inspect the actual result.

Iterate.

Do not assume the first implementation is good enough.

---

# 8. Application Shell

The application should no longer feel like a generic admin template.

Target:

```text
┌───────────────┬──────────────────────────────────────────┐
│               │ Topbar                                   │
│   Sidebar     ├──────────────────────────────────────────┤
│               │ Page Header                               │
│   Navigation  │                                          │
│               │ Main Content                              │
│               │                                          │
│               │                                          │
└───────────────┴──────────────────────────────────────────┘
```

Sidebar:

* approximately 250–280px desktop
* collapsible
* approximately 72px collapsed
* grouped navigation
* strong active state
* icon + label
* mobile drawer

Topbar:

* page context
* search if infrastructure permits
* system status if available
* notification
* user/profile
* theme selector

---

# 9. Dashboard

The current dashboard should NOT remain a simple collection of cards and charts.

Build an operational overview.

Preferred hierarchy:

```text
Page Header
      ↓
KPI Overview
      ↓
Revenue + Customer Growth
      ↓
Customer Health + Network Overview
      ↓
Recent Payments + Recent Activity
      ↓
Needs Attention
```

The actual arrangement may adapt to the data available.

Prioritize information hierarchy over blindly following a screenshot.

---

# 10. Dashboard Header

Use a professional page header.

Example:

```text
Dashboard

Overview of your ISP operations.

[ + Add Customer ] [ Create Voucher ]
```

Do not use unnecessary decorative elements.

CTA buttons must use existing functionality.

Do not create buttons for functionality that does not exist.

---

# 11. KPI Cards

KPI cards should communicate meaningful information.

Preferred:

```text
Total Customers
1
```

with:

* icon
* label
* primary value
* contextual information where real data exists

Do not fabricate trend values.

Use moderate:

* radius
* border
* shadow
* typography

Avoid oversized cards.

---

# 12. Charts

Charts must feel integrated into the dashboard.

Do not simply place chart components underneath cards.

Charts should:

* use appropriate dimensions
* support responsive resizing
* use theme-aware colors
* use readable labels
* use subtle grid lines
* provide useful tooltips
* respect real data

Do not create charts merely to fill empty space.

If meaningful data does not exist, do not fabricate a chart.

---

# 13. Tables

Tables should feel like professional SaaS/network management software.

Use:

* compact headers
* clear hierarchy
* readable row height
* subtle dividers
* hover state
* semantic status badges
* restrained actions

Avoid:

* huge row heights
* excessive buttons
* unnecessary borders everywhere
* rainbow statuses

Use a compact overflow strategy on mobile.

---

# 14. Customer Management

Customer pages should feel operational.

Header:

```text
Customers

Manage your subscribers and service accounts.

[Import] [Add Customer]
```

If Import functionality does not exist, do not create a fake button.

Use:

* search
* filtering
* status
* plan
* account
* expiration
* balance
* actions

Customer detail should use clear hierarchy.

If tabs already exist, improve their visual presentation.

If tabs do not exist and the existing architecture makes them reasonable, they may be introduced without changing backend functionality.

---

# 15. Voucher Management

Voucher functionality is important to ISP operations.

Create a polished interface around the existing voucher functionality.

If generation already exists, make the flow feel like:

```text
Voucher Configuration

Quantity
Plan
Duration
Prefix

[Cancel] [Generate]
```

Do not modify voucher business logic.

Do not change how vouchers are generated or stored.

Only improve the frontend experience.

---

# 16. Router / Network UI

If router information exists, it should visually communicate network status.

Use:

```text
● Online
● Offline
● Degraded
```

and relevant information such as:

* router name
* address
* session count
* sync state
* last activity

Only show information that exists in the application.

Do not invent router telemetry.

---

# 17. Financial UI

Payments and income reports should look like financial management software.

Use:

* strong monetary hierarchy
* semantic payment status
* compact tables
* clear date information
* filters
* summary cards where data exists

Statuses:

```text
Paid
Pending
Overdue
Cancelled
Refunded
```

Only display statuses supported by the application.

---

# 18. Empty States

Replace generic:

```text
No data.
```

with useful contextual states.

Example:

```text
No customers yet

Your customer list will appear here once you add
your first subscriber.

[Add Customer]
```

CTA must correspond to real functionality.

---

# 19. Loading States

Do not rely solely on:

```text
Loading...
```

Use skeleton states where practical.

Skeleton dimensions should match the actual component.

Examples:

* KPI skeleton
* table row skeleton
* chart skeleton
* detail skeleton

Skeleton must work correctly in both Light and Dark modes.

---

# 20. Error States

Use readable errors.

Example:

```text
Unable to load customers

We couldn't retrieve customer data right now.

[Try again]
```

Do not expose:

* stack traces
* raw exceptions
* internal paths
* SQL errors

to normal users.

---

# 21. Theme System

Implement all three:

```text
Light
Dark
System
```

Read:

```text
UI_UX_THEME.md
```

carefully.

Theme is NOT an optional enhancement.

It is part of the core redesign.

---

# 22. Theme Persistence

Persist:

```text
light
dark
system
```

using the project's most appropriate lightweight mechanism.

Preferred:

```text
localStorage
```

Default:

```text
system
```

Theme should survive:

* refresh
* navigation
* browser reopen

---

# 23. Theme Architecture

Do not scatter hundreds of hardcoded colors throughout components.

Use semantic theme tokens such as:

```text
background
surface
surface-elevated
surface-subtle

foreground
foreground-secondary
foreground-muted

border
border-subtle

primary
success
warning
danger
info
```

Components should consume semantic tokens.

If the project already has a theme/token architecture, extend it instead of creating another one.

---

# 24. Dark Mode Quality

Dark Mode must feel intentionally designed.

Do NOT simply invert colors.

Avoid pure black.

Use dark navy/slate surfaces with sufficient separation:

```text
Background
Surface
Elevated Surface
Border
Foreground
Muted Foreground
```

Cards, tables, inputs, modals, dropdowns, charts, skeletons, badges, and navigation must all be checked.

A component that looks correct in Light but broken in Dark is unfinished.

---

# 25. System Mode

When:

```text
System
```

is selected, follow the operating system preference.

If OS changes:

```text
Light → Dark
```

the application must respond.

Do not store the resolved theme as the preference.

Store:

```text
system
```

and resolve it dynamically.

---

# 26. No Theme Flash

Avoid:

```text
white page
    ↓
theme loads
    ↓
dark page
```

Theme should be resolved as early as practical.

The user should not see an obvious flash of the wrong theme.

---

# 27. Responsive Design

The application must be genuinely responsive.

Desktop:

```text
Sidebar + Workspace
```

Tablet:

```text
Collapsed Sidebar + Workspace
```

Mobile:

```text
Topbar
Drawer
Single-column content
```

Do not simply shrink desktop layouts.

---

# 28. Mobile Rules

At approximately:

```text
390px
```

ensure:

* no page-level horizontal overflow
* navigation remains usable
* buttons remain accessible
* cards stack
* charts resize
* modals fit
* tables can scroll horizontally where necessary
* forms remain usable
* text does not become microscopic

Do not hide important information merely to make a screenshot look clean.

---

# 29. Typography

Use the existing project font if it is already appropriate.

Otherwise prefer a modern UI font such as:

```text
Inter
```

Hierarchy:

```text
Page title:
28–32px

Section title:
16–18px

Body:
14px

Small:
12–13px

KPI:
28–32px
```

Do not make everything bold.

---

# 30. Icons

Use one consistent icon library.

Preferred:

```text
Lucide
```

If the project already uses another icon library consistently, continue using it.

Do not mix:

* emojis
* random SVGs
* FontAwesome
* multiple unrelated icon systems

unless already required by the project.

---

# 31. Animation

Keep animation subtle.

Allowed:

* sidebar collapse
* dropdown
* modal
* toast
* hover
* focus
* skeleton
* chart appearance

Preferred duration:

```text
150–250ms
```

Avoid:

* excessive motion
* bouncing
* parallax
* animated gradients
* unnecessary page transitions

---

# 32. Accessibility

Ensure:

* keyboard navigation
* visible focus states
* semantic buttons
* accessible icon-only controls
* readable contrast
* usable forms
* modal focus handling
* Escape closes modal where appropriate
* color is not the only indicator of status

Accessibility applies to both themes.

---

# 33. Visual Iteration Is Required

Do not stop after the first successful build.

The workflow is:

```text
Implement
    ↓
Run
    ↓
Inspect
    ↓
Identify weak areas
    ↓
Improve
    ↓
Run again
    ↓
Inspect again
```

Repeat until the interface genuinely looks polished.

The frontend should be judged by the rendered result, not merely by whether the code compiles.

---

# 34. What to Look for During Iteration

After running the application, inspect:

### Hierarchy

Can the user immediately see:

* what page they are on
* what is important
* what requires attention
* what action they can take

### Density

Is there too much empty space?

Is there too much information?

Does the dashboard feel operational?

### Alignment

Check:

* card edges
* table alignment
* section spacing
* sidebar alignment
* page padding
* button alignment

### Typography

Check:

* headings
* labels
* muted text
* numbers
* table text

### Color

Check:

* primary accent
* status colors
* contrast
* dark-mode surfaces
* disabled states

### Components

Check:

* cards
* buttons
* inputs
* dropdowns
* modals
* tables
* badges

---

# 35. Avoid Generic Admin Template Appearance

The following is NOT sufficient:

```text
Change background
+
Change font
+
Round cards
+
Add shadow
```

The actual:

* information architecture
* navigation
* page composition
* hierarchy
* data density
* component system

must improve.

The result should feel like a purpose-built ISP control plane.

---

# 36. Use the Existing Screenshot as Baseline

The original UI screenshot represents the "before" state.

The final result should clearly improve upon it.

Specifically improve:

* sidebar hierarchy
* navigation grouping
* header
* KPI presentation
* whitespace usage
* dashboard composition
* chart integration
* tables
* status presentation
* visual consistency
* responsive behavior
* theme support

Do not preserve weak visual patterns simply because they already exist.

Preserve functionality, not outdated presentation.

---

# 37. Do Not Build Fake Features

A UI element must correspond to actual functionality.

Do not create:

```text
Export
Import
Network Monitor
Live Sessions
Revenue Analytics
```

just because they look good in the design.

If the functionality does not exist:

* do not create it
* or represent it only when the existing application already has the necessary data/functionality

The goal is to redesign the existing application, not create a fictional product.

---

# 38. Preserve Existing Routes

Do not arbitrarily rename routes.

If navigation must be reorganized visually, map existing routes into the new navigation structure.

Example:

```text
Customers
    → existing customer route

Vouchers
    → existing voucher route

Routers
    → existing router route
```

The visual information architecture can improve without breaking URL behavior.

---

# 39. Validation

After implementation, run the project's existing checks.

At minimum:

```text
Build
Lint
Type checking
```

where applicable.

Then manually inspect:

```text
Dashboard
Customers
Plans
Orders
Vouchers
Routers
Payments
Reports
Settings
```

only for pages that actually exist.

---

# 40. Theme Validation

Explicitly test:

```text
Light
Dark
System
```

Test:

```text
Dashboard
Sidebar
Topbar
Tables
Forms
Modals
Dropdowns
Charts
Status badges
Empty states
Loading states
```

No major component should remain Light-only.

---

# 41. Responsive Validation

Explicitly test approximately:

```text
1440px+
1280px
768px
390px
```

Look for:

* overflow
* broken grid
* clipped text
* oversized cards
* inaccessible buttons
* modal overflow
* table issues
* broken sidebar

---

# 42. Git Discipline

Do not perform destructive Git operations.

Do not:

```text
git reset --hard
git clean -fd
```

unless explicitly instructed.

Do not delete unrelated work.

Do not overwrite user changes blindly.

Before modifying a file, understand whether it contains existing functionality that must be preserved.

---

# 43. Change Scope

Stay focused on frontend/UI/UX.

Do not modify unrelated backend code.

If a frontend issue requires a backend change:

1. First determine whether the issue can be solved entirely in the frontend.
2. Prefer frontend-only solutions.
3. If a backend change is genuinely required, keep it minimal.
4. Do not refactor unrelated backend systems.

---

# 44. Completion Criteria

Do not consider the task complete until:

* [ ] Existing application still builds
* [ ] Existing authentication still works
* [ ] Existing routes still work
* [ ] Existing functionality remains intact
* [ ] Dashboard is significantly more professional
* [ ] Sidebar is grouped and polished
* [ ] Sidebar collapse works
* [ ] Mobile navigation works
* [ ] Topbar is polished
* [ ] KPI cards are coherent
* [ ] Tables are polished
* [ ] Forms are polished
* [ ] Empty states are useful
* [ ] Loading states are polished
* [ ] Error states are readable
* [ ] Light Mode works
* [ ] Dark Mode works
* [ ] System Mode works
* [ ] Theme preference persists
* [ ] No obvious theme flash exists
* [ ] Charts support themes where charts exist
* [ ] Responsive layout works
* [ ] No fake data has been introduced
* [ ] No unnecessary dependencies have been added
* [ ] No framework migration occurred
* [ ] No unnecessary architecture was introduced
* [ ] UI is visually consistent across pages

---

# 45. Final Instruction

Do not treat this as a simple styling task.

Treat PHPNuxBill as an ISP control plane that needs a professional frontend.

The final interface should communicate:

```text
Customer Management
        +
Billing
        +
Subscriptions
        +
Vouchers
        +
Network Operations
        +
System Administration
```

through one coherent visual system.

The result must feel:

```text
Professional
Operational
Modern
Dense but readable
Consistent
Responsive
Accessible
Light/Dark capable
```

while keeping the existing PHPNuxBill functionality intact.

Prioritize real functionality and information hierarchy over decorative design.

Do not stop at "it builds."

Iterate on the rendered UI until it looks production-grade.
