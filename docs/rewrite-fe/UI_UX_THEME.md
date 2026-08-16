# PHPNuxBill — Theme System & Dark/Light Mode

## 1. Objective

PHPNuxBill MUST support three appearance modes:

```text
Light
Dark
System
```

Theme switching harus berlaku untuk seluruh aplikasi, bukan hanya dashboard.

Targetnya adalah kualitas visual control plane modern seperti Cloudflare Dashboard dan aplikasi network management modern.

Dark mode bukan sekadar:

```text
background: white → black
```

Light dan Dark harus memiliki design tokens masing-masing.

---

# 2. Theme Modes

Implement:

```text
Light
Dark
System
```

Behavior:

### Light

Seluruh UI menggunakan light color tokens.

### Dark

Seluruh UI menggunakan dark color tokens.

### System

Mengikuti:

```text
prefers-color-scheme
```

Jika OS berubah dari light → dark, aplikasi mengikuti perubahan tersebut selama user memilih System.

---

# 3. Theme Persistence

User preference harus persistent.

Gunakan mekanisme yang sesuai dengan existing frontend stack.

Preferred:

```text
localStorage
```

Contoh key:

```text
phpnuxbill-theme
```

Value:

```text
light
dark
system
```

Default:

```text
system
```

Jangan menyimpan preference ke database jika tidak diperlukan.

Theme harus tetap sama setelah:

* refresh
* route change
* browser reopen

---

# 4. Theme Switcher

Tambahkan theme switcher yang mudah ditemukan.

Recommended placement:

Topbar → user/profile menu

atau:

Topbar → appearance button

Contoh:

```text
Appearance

○ Light
● Dark
○ System
```

Alternatif compact:

```text
☼
```

Klik membuka:

```text
Light
Dark
System
```

Jangan menggunakan emoji sebagai icon production.

Gunakan icon library yang sudah dipakai project.

Preferred icons:

```text
Sun
Moon
Monitor
```

---

# 5. Avoid Flash of Wrong Theme

Theme harus diterapkan sedini mungkin saat aplikasi boot.

Jangan menghasilkan kondisi:

```text
Dark selected
    ↓
page opens white
    ↓
100ms later becomes dark
```

Hindari Flash Of Unstyled Theme / Flash Of Wrong Theme.

Jika framework memiliki server-side theme initialization, gunakan mekanisme existing.

Jika SPA:

* baca theme preference sebelum render
* gunakan CSS class/data attribute
* jangan menunggu API

---

# 6. Architecture

Theme harus berbasis semantic tokens.

Jangan hardcode:

```text
background: #ffffff
color: #111827
border: #e5e7eb
```

di ratusan component.

Gunakan semantic variables.

Contoh:

```text
--background
--surface
--surface-elevated
--surface-subtle

--foreground
--foreground-secondary
--foreground-muted

--border
--border-subtle

--primary
--primary-hover

--success
--warning
--danger
--info
```

Component menggunakan semantic token:

```text
background: var(--surface)
color: var(--foreground)
border-color: var(--border)
```

bukan hardcoded color.

Jika project menggunakan Tailwind, implementasikan melalui theme variables/configuration sesuai architecture existing.

---

# 7. Light Theme

Base:

```text
Background:
#F5F7FA

Surface:
#FFFFFF

Surface Elevated:
#FFFFFF

Surface Subtle:
#F8FAFC

Foreground:
#0F172A

Foreground Secondary:
#475569

Foreground Muted:
#94A3B8

Border:
#E2E8F0

Border Subtle:
#F1F5F9
```

Primary:

```text
Primary:
#F59E0B

Primary Hover:
#D97706

Primary Soft:
#FFF7E6
```

Status:

```text
Success:
#16A34A

Success Soft:
#DCFCE7

Warning:
#F59E0B

Warning Soft:
#FEF3C7

Danger:
#DC2626

Danger Soft:
#FEE2E2

Info:
#2563EB

Info Soft:
#DBEAFE
```

---

# 8. Dark Theme

Dark mode harus menggunakan dark navy/slate palette.

Jangan menggunakan pure black:

```text
#000000
```

sebagai primary application background.

Recommended:

```text
Background:
#0B1120

Surface:
#111827

Surface Elevated:
#172033

Surface Subtle:
#1E293B

Foreground:
#F8FAFC

Foreground Secondary:
#CBD5E1

Foreground Muted:
#94A3B8

Border:
#263244

Border Subtle:
#1E293B
```

Primary:

```text
Primary:
#F59E0B

Primary Hover:
#FBBF24

Primary Soft:
#3A2A0A
```

Status:

```text
Success:
#22C55E

Success Soft:
#12351F

Warning:
#F59E0B

Warning Soft:
#3A2A0A

Danger:
#EF4444

Danger Soft:
#3B1616

Info:
#3B82F6

Info Soft:
#172554
```

---

# 9. Sidebar

Sidebar dapat memiliki dark appearance pada Light Mode.

Namun harus tetap memiliki dedicated dark-mode state.

Light mode:

```text
Sidebar:
#FFFFFF

Sidebar foreground:
#0F172A

Sidebar border:
#E2E8F0

Active:
#FFF7E6

Active foreground:
#D97706
```

Dark mode:

```text
Sidebar:
#0B1120

Sidebar foreground:
#CBD5E1

Sidebar border:
#1E293B

Active:
#1E293B

Active foreground:
#FBBF24
```

Jangan membuat active navigation menjadi orange block yang terlalu terang.

Gunakan subtle highlight.

---

# 10. Topbar

Light:

```text
background: #FFFFFF
border: #E2E8F0
```

Dark:

```text
background: #111827
border: #263244
```

Topbar harus tetap memiliki separation dari main content.

Gunakan border daripada heavy shadow.

---

# 11. Cards

Light:

```text
background:
#FFFFFF

border:
#E2E8F0

shadow:
subtle
```

Dark:

```text
background:
#111827

border:
#263244

shadow:
minimal
```

Jangan menggunakan shadow besar dalam Dark Mode.

Dark mode lebih mengandalkan:

* surface contrast
* border
* elevation through tone

---

# 12. KPI Cards

KPI card harus tetap readable.

Light:

```text
Label:
#64748B

Value:
#0F172A

Secondary:
#64748B
```

Dark:

```text
Label:
#94A3B8

Value:
#F8FAFC

Secondary:
#CBD5E1
```

Status color tidak boleh berubah menjadi terlalu saturated.

---

# 13. Tables

Light:

```text
Header:
#F8FAFC

Row:
#FFFFFF

Hover:
#F8FAFC

Border:
#E2E8F0
```

Dark:

```text
Header:
#172033

Row:
#111827

Hover:
#172033

Border:
#263244
```

Table text:

Dark:

```text
Primary:
#F8FAFC

Secondary:
#CBD5E1

Muted:
#94A3B8
```

---

# 14. Forms

Input Light:

```text
background: #FFFFFF
border: #CBD5E1
text: #0F172A
placeholder: #94A3B8
```

Input Dark:

```text
background: #111827
border: #334155
text: #F8FAFC
placeholder: #64748B
```

Focus:

Light:

```text
border: primary
ring: subtle amber
```

Dark:

```text
border: primary
ring: subtle amber
```

Do not use bright full-width orange glow.

---

# 15. Dropdown

Light:

```text
background: #FFFFFF
border: #E2E8F0
```

Dark:

```text
background: #172033
border: #334155
```

Hover:

Light:

```text
#F8FAFC
```

Dark:

```text
#1E293B
```

---

# 16. Modal

Light:

```text
background:
#FFFFFF
```

Dark:

```text
background:
#111827
```

Overlay:

Light:

```text
rgba(15, 23, 42, 0.35)
```

Dark:

```text
rgba(0, 0, 0, 0.55)
```

Modal should remain clearly separated from page background.

---

# 17. Toast

Toast must work in both modes.

Light:

```text
surface: #FFFFFF
border: #E2E8F0
```

Dark:

```text
surface: #172033
border: #334155
```

Status icon uses semantic status color.

---

# 18. Empty States

Light:

* primary text dark
* secondary text slate
* subtle icon

Dark:

* primary text light
* secondary text muted
* icon uses muted slate

Never use low-contrast gray text in dark mode.

---

# 19. Skeleton

Light:

```text
base:
#E2E8F0

highlight:
#F8FAFC
```

Dark:

```text
base:
#1E293B

highlight:
#263244
```

Skeleton must not flash white while Dark Mode is active.

---

# 20. Charts

Charts MUST support both themes.

Do not leave charts using fixed light-mode colors.

Chart adaptation:

### Light

Grid:

```text
#E2E8F0
```

Text:

```text
#64748B
```

### Dark

Grid:

```text
#263244
```

Text:

```text
#94A3B8
```

Chart background should inherit the card surface.

---

# 21. Chart Colors

Use limited semantic colors.

Primary series:

```text
Amber
```

Secondary:

```text
Blue
```

Success:

```text
Green
```

Danger:

```text
Red
```

Do not create rainbow charts.

If multiple series are required, maintain sufficient contrast in both themes.

---

# 22. Status Badges

Status badge background must adapt to theme.

Example:

### Active

Light:

```text
background: #DCFCE7
text: #166534
```

Dark:

```text
background: #12351F
text: #86EFAC
```

### Pending

Light:

```text
background: #FEF3C7
text: #92400E
```

Dark:

```text
background: #3A2A0A
text: #FCD34D
```

### Overdue

Light:

```text
background: #FEE2E2
text: #991B1B
```

Dark:

```text
background: #3B1616
text: #FCA5A5
```

---

# 23. Settings Page

Add an Appearance section if a settings page exists.

Example:

```text
Appearance

Choose how PHPNuxBill looks.

Theme

○ Light
○ Dark
● System
```

If there is no suitable settings page, the theme selector may live in the profile dropdown.

Do not create a whole new settings architecture only for this.

---

# 24. Profile Menu

Recommended:

```text
Administrator
administrator@example.com

──────────────────

Appearance
  ☼ Light
  ◐ Dark
  ◉ System

──────────────────

Account
Logout
```

The exact existing account information must be preserved.

---

# 25. Theme Transition

Theme switching can use a short transition.

Allowed:

```text
150–200ms
```

Transition only visual properties such as:

* background
* border
* color

Do not create slow animation.

Do not animate every component independently.

Avoid noticeable flicker.

---

# 26. Accessibility

Both themes MUST satisfy readable contrast.

Especially:

* body text
* muted text
* placeholder
* disabled controls
* table text
* status badge
* button text
* chart labels

Do not use:

```text
dark gray text
```

on:

```text
dark gray background
```

just because it aesthetically looks subtle.

Readability has priority.

---

# 27. Mobile

Theme switcher must remain accessible on mobile.

If topbar is constrained:

```text
Profile
    ↓
Appearance
    ↓
Light / Dark / System
```

Do not sacrifice navigation space just to expose all three options permanently.

---

# 28. Theme-aware Logo

PHPNuxBill branding should remain readable in both themes.

If existing logo is dark-only:

* create/use a light-compatible version if already available
* otherwise use text/logo treatment that remains readable

Do not distort or recolor the brand unnecessarily.

---

# 29. No Hardcoded Theme-Specific Colors in Components

Avoid:

```text
bg-white
text-gray-900
border-gray-200
```

when those values prevent proper Dark Mode behavior.

Prefer semantic classes/tokens.

If Tailwind is being used, use the project's existing dark-mode strategy.

For example:

```text
bg-background
text-foreground
border-border
```

or equivalent implementation.

Do not blindly add `dark:` to hundreds of components if a token-based solution is cleaner.

---

# 30. Existing Stack

Determine the project's current implementation first.

If the project already has:

```text
Tailwind dark mode
CSS variables
theme provider
theme context
```

extend it.

Do NOT create a second theme system.

If there is no theme architecture, implement the smallest maintainable system appropriate for the existing stack.

---

# 31. Theme State

The application should conceptually behave as:

```text
ThemePreference
    │
    ├── light
    │
    ├── dark
    │
    └── system
             │
             ├── OS light → Light
             └── OS dark  → Dark
```

The stored preference is the user's preference, not the resolved theme.

Example:

```text
stored:
system

resolved:
dark
```

If OS changes:

```text
dark → light
```

the application follows automatically.

---

# 32. Definition of Done

Theme implementation is complete only when:

* [ ] Light mode works
* [ ] Dark mode works
* [ ] System mode works
* [ ] Theme preference persists
* [ ] Refresh preserves preference
* [ ] Route changes preserve preference
* [ ] System mode follows OS preference
* [ ] No flash of wrong theme
* [ ] Sidebar adapts
* [ ] Topbar adapts
* [ ] Cards adapt
* [ ] KPI cards adapt
* [ ] Tables adapt
* [ ] Forms adapt
* [ ] Dropdowns adapt
* [ ] Modals adapt
* [ ] Toasts adapt
* [ ] Empty states adapt
* [ ] Skeletons adapt
* [ ] Charts adapt
* [ ] Status badges adapt
* [ ] Profile menu adapts
* [ ] Theme switcher is accessible
* [ ] Mobile supports theme switching
* [ ] Contrast remains readable
* [ ] No fake data introduced
* [ ] Existing functionality remains intact
* [ ] Existing API remains intact
* [ ] Existing authentication remains intact
* [ ] Existing routes remain intact

---

# 33. Agent Instruction

This file is mandatory for both implementation agents.

`CLAUDE_DEEPSEEK.md`:

Add this instruction:

```text
Read UI_UX_THEME.md before implementing the UI shell.
Theme support is part of the core UI redesign, not an optional enhancement.
Implement the theme architecture together with the application shell.
```

`CLAUDE_GEMINI.md`:

Add this instruction:

```text
Read UI_UX_THEME.md before visual QA.
Test Light, Dark, and System modes during visual review.
Do not consider the UI complete if any major component remains visually broken in Dark Mode.
```

---

# 34. Final Quality Target

The application should feel coherent in:

```text
LIGHT

┌──────────────────────────────────────────┐
│ PHPNuxBill                  Search Admin  │
├────────────┬─────────────────────────────┤
│ Dashboard  │ KPI     KPI     KPI     KPI │
│ Customers  │                             │
│ Plans      │ Revenue       Customers     │
│ Routers    │                             │
│ Vouchers   │ Payments      Activity      │
└────────────┴─────────────────────────────┘
```

and:

```text
DARK

┌──────────────────────────────────────────┐
│ PHPNuxBill                  Search Admin  │
├────────────┬─────────────────────────────┤
│ Dashboard  │ KPI     KPI     KPI     KPI │
│ Customers  │                             │
│ Plans      │ Revenue       Customers     │
│ Routers    │                             │
│ Vouchers   │ Payments      Activity      │
└────────────┴─────────────────────────────┘
```

The structure remains identical.

Only the visual language changes.

The user must never feel that Dark Mode is an afterthought.

Dark Mode is a first-class product experience.
