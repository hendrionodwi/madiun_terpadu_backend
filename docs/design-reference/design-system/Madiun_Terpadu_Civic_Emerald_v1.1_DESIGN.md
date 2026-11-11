---
name: Madiun Terpadu Civic Emerald
version: '1.1'
description: Canonical cross-platform design system for the integrated waste and environmental services of Kota Madiun.
colors:
  primary: '#046A58'
  on-primary: '#FFFFFF'
  primary-soft: '#E6F4F1'
  on-primary-soft: '#0F172A'
  accent: '#D97706'
  on-accent: '#0F172A'
  neutral-ink: '#0F172A'
  text-secondary: '#475569'
  text-muted: '#64748B'
  background: '#F8FAFC'
  surface: '#FFFFFF'
  border: '#DDE5E3'
  success: '#15803D'
  success-surface: '#F0FDF4'
  warning: '#D97706'
  warning-surface: '#FFFBEB'
  error: '#DC2626'
  error-surface: '#FEF2F2'
  information: '#0284C7'
  information-surface: '#F0F9FF'
  operational-background: '#0F172A'
  operational-surface: '#1E293B'
  operational-text: '#F8FAFC'
typography:
  display:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  title:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 26px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  caption:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
  code:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.375rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  full: 9999px
spacing:
  space-1: 0.25rem
  space-2: 0.5rem
  space-3: 0.75rem
  space-4: 1rem
  space-5: 1.25rem
  space-6: 1.5rem
  space-8: 2rem
  space-10: 2.5rem
  space-12: 3rem
  gutter-mobile: 1rem
  gutter-tablet: 1.5rem
  gutter-desktop: 2rem
---

# Madiun Terpadu Civic Emerald v1.1

## 1. Purpose

Madiun Terpadu is the integrated waste and environmental management system for Kota Madiun. Its interface must feel civic, trustworthy, modern, accessible, calm, and human. It serves public information, resident services, field operations, TPS3R management, environmental reporting, and DLH administration.

This document is the single visual source of truth for Stitch projects, Laravel web interfaces, and the Flutter mobile application. Generated screens may adapt their composition to platform constraints, but they must preserve the visual hierarchy and semantic rules defined here.

## 2. Official Roles

Use only these official actors:

1. Warga
2. Petugas Lapangan
3. Pengelola TPS3R
4. Pemrakarsa
5. Admin DLH

Do not invent additional system roles without an explicit product requirement.

## 3. Visual Character

- Use Civic Emerald as the dominant institutional color.
- Use generous but efficient whitespace. Avoid empty areas that separate related operational content excessively.
- Use subtle elevation, restrained gradients, clear grouping, and strong information hierarchy.
- Public pages should feel editorial and welcoming.
- Internal workspaces should prioritize operational clarity, compact controls, responsive data presentation, and efficient navigation.
- Avoid generic AI-dashboard styling, excessive floating cards, decorative glass effects, or ornamental gradients that reduce readability.
- Never use emoji as functional icons.

## 4. Canonical Color Roles

The exact canonical roles are:

- Primary action and institutional anchor: `#046A58`.
- Selected and protective surface: `#E6F4F1`.
- Incentive, pending state, and warning accent: `#D97706`.
- Primary text and operational dark background: `#0F172A`.
- Page background: `#F8FAFC`.
- Card and form surface: `#FFFFFF`.
- Standard border: `#DDE5E3`.
- Success: `#15803D`.
- Error: `#DC2626`.
- Information: `#0284C7`.

Derived hover, pressed, disabled, and tonal shades are allowed when they preserve the canonical semantic role and WCAG 2.1 AA contrast. Do not silently replace a canonical role with a different base color.

Use color as reinforcement, never as the only carrier of meaning. Statuses require readable text and, where helpful, a consistent line icon.

## 5. Typography

- Plus Jakarta Sans is reserved for display text, page headings, section headings, card titles, and prominent institutional labels.
- Inter is used for body text, form controls, navigation, tables, metadata, buttons, and status labels.
- JetBrains Mono is optional and limited to technical identifiers such as ticket codes or order codes.
- Minimum visible text size is 12px.
- Mobile body text should normally be 14–16px.
- Use tabular numerals for monetary values, points, weights, dates, and operational metrics when available.
- Keep headings concise. Avoid oversized headings that displace essential operational content.

## 6. Spacing, Radius, and Elevation

- Use a 4px spacing rhythm.
- Standard card padding is 16px on mobile and 20–24px on larger viewports.
- Use 6px radius for compact controls, 8px for standard controls, 12px for cards and inputs, and 16px for prominent containers or sheets.
- Use full radius only for badges, avatars, compact segmented indicators, and pills.
- Prefer thin borders and subtle shadows. Cards should not appear to float excessively.
- Related elements use `gap`; avoid arbitrary margins between siblings.

## 7. Iconography

- Use one consistent Lucide-style line icon family.
- Default icon bounding box is 24×24px with approximately 1.75px stroke.
- Small supporting icons may use 16–20px while preserving the same stroke character.
- Pair unfamiliar operational icons with visible labels.
- Do not mix emoji, filled cartoon icons, Material Symbols, and Lucide icons in the same interface.

## 8. Responsive Foundation

### Mobile: 360–430px

- Use a single-column content flow.
- Respect device safe areas.
- Minimum interactive target is 48×48dp.
- Use bottom navigation for the primary Warga and Petugas experiences.
- Convert dense tables into cards, priority rows, step lists, or horizontal-scroll regions.
- Use bottom sheets for short contextual choices and full pages for complex forms.

### Tablet: 768–1024px

- Use balanced one- or two-column grids.
- Internal navigation may use a collapsed rail or drawer.
- Preserve readable form widths instead of stretching inputs across the full viewport.

### Desktop: 1280px and above

- Public content uses a centered editorial container.
- Internal workspaces may use a 260px expanded sidebar and a 72px collapsed rail.
- Use complete tables only when the data benefits from comparison across columns.
- Keep sticky actions and filters visible when they materially support long operational workflows.

## 9. Public Portal Pattern

The public Laravel portal is accessible without login and may contain:

- municipal environmental announcements;
- service schedules and route information suitable for public access;
- TPS3R and waste-processing information;
- waste-sorting education;
- public facility information;
- marketplace catalogue browsing;
- links to authentication for personalized or transactional actions.

Purchasing, checkout, personal activity, complaints, point history, and account management require authentication when personal data or transactions are involved.

Public pages use a clear header, accessible mobile menu, contextual calls to action, editorial sections, concise statistics, and a structured civic footer.

## 10. Internal Web Workspace Pattern

Admin DLH, Pengelola TPS3R, and Pemrakarsa workspaces use:

- a role-aware collapsible sidebar;
- a compact top or context bar;
- page titles with concise supporting descriptions;
- filter and search controls close to the affected data;
- responsive tables or card lists;
- explicit status indicators;
- confirmation for destructive or finalizing actions;
- loading, empty, validation, success, and failure states.

Do not overload dashboards with complete forms. Dashboards summarize conditions and provide shortcuts; detailed work belongs on dedicated pages.

## 11. Warga Mobile Navigation

Use exactly five primary destinations:

1. Beranda
2. Aktivitas
3. Marketplace
4. Pengaduan
5. Profil

The selected item uses Civic Emerald with a soft emerald indicator. Badges use semantic colors and readable counts. Labels remain visible; do not rely on icons alone.

## 12. Petugas Lapangan Mobile Navigation

Use exactly five operational destinations:

1. Beranda
2. Tugas
3. Setoran
4. Riwayat
5. Profil

Petugas screens prioritize outdoor legibility, large controls, clear sequencing, offline awareness, and concise operational language. A dark operational navigation surface may use `#0F172A` with `#F8FAFC` content and Civic Emerald selection indicators.

## 13. Mobile Startup and Authentication

- Application launch begins with a brief branded splash state while local session state is checked.
- First-time users may receive concise onboarding.
- Onboarding is not shown repeatedly after completion.
- Returning authenticated users enter their role-appropriate home directly.
- Users without an active session continue to login.
- Login, registration, forgotten-password recovery, and authenticated password changes are distinct flows.
- Authentication screens do not display the role workspace bottom navigation.

## 14. Core Component Rules

### Buttons

- Primary buttons use Civic Emerald with white text.
- Secondary buttons use a soft or white surface with a visible border.
- Destructive actions use Error red and require confirmation when data loss or finalization is possible.
- Button labels start with a clear action verb.
- Provide disabled and loading states without changing layout width.

### Inputs and Selects

- Standard control height is at least 44px on desktop and 48dp on mobile.
- Labels remain visible above controls; placeholders do not replace labels.
- Provide helper text, validation messages, and visible focus indicators.
- Select controls use the same height, radius, and label treatment as text inputs.

### Cards

- Use cards to group one meaningful topic or action, not every individual label.
- Prefer a title, essential value or status, concise supporting information, and at most one primary action.
- Avoid deeply nested cards.

### Tables and Lists

- Desktop tables use clear headers, aligned numerical data, restrained row dividers, and visible row actions.
- Mobile transforms tables according to information priority.
- Search, filters, and pagination must remain understandable at smaller widths.

### Statuses

- Use concise Indonesian labels such as Menunggu, Diproses, Selesai, Ditolak, Dibatalkan, and Perlu Perbaikan.
- Status colors must be paired with text.
- Do not invent operational certainty that the backend cannot provide.

## 15. System States

Every substantial data screen should account for:

- initial loading with skeletons that preserve layout;
- button-level loading for mutations;
- empty state with a relevant next action;
- validation messages close to the affected field;
- recoverable network failure with retry;
- success feedback without blocking continued work unnecessarily;
- offline queue and synchronization awareness for Petugas Lapangan when supported;
- permission explanations before location, camera, or file access.

## 16. Domain and Financial Rules

- Product prices are displayed independently in Rupiah, for example `Rp25.000`.
- Warga rewards are displayed independently in points, for example `18.250 Poin`.
- A monetary estimate may appear only as secondary information, for example `Estimasi nilai: ±Rp18.250 berdasarkan rate DLH`.
- Never merge points and Rupiah into one balance.
- Checkout presents `Gunakan Poin` and `Pembayaran Online melalui Midtrans` as separate methods.
- Midtrans is a payment gateway, not a Warga point balance or TPS3R savings balance.
- Use Indonesian number formatting: `12,5 kg`, `18.250 Poin`, and `Rp25.000`.
- Use WIB for operational time where relevant.
- Mark sample people, routes, vehicles, facilities, and metrics as `Data Simulasi` when appropriate.

## 17. Operational Integrity

- Weighing results are entered or verified by authorized personnel.
- Route and pickup statuses are updated by Petugas Lapangan or authorized operational users.
- Do not claim unsupported automatic sensing, automatic capacity detection, AI monitoring, live vehicle telemetry, QR-based report identity, or guaranteed third-party messaging integration.
- Notifications are described as application notifications unless a separate integration is explicitly implemented.
- Maps support location, route, and service context but must not imply a live position when only static or periodically updated data exists.

## 18. Accessibility

- Meet WCAG 2.1 AA contrast for normal text and interface components.
- Maintain visible keyboard focus on web interfaces.
- Provide semantic labels for icons and controls.
- Never communicate state by color alone.
- Respect screen-reader order and logical heading hierarchy.
- Avoid motion that is essential to understanding, and respect reduced-motion preferences.
- Error messages explain the problem and the corrective action.

## 19. Implementation Boundary

Generated Stitch HTML is a design reference, not production-ready application code.

- Laravel uses Tailwind CSS v4 with CSS-first `@theme` and `@import "tailwindcss"`.
- Do not generate a Tailwind v3 CommonJS configuration as the primary setup.
- Flutter maps the same semantic roles into `ThemeData`, `ColorScheme`, `TextTheme`, spacing constants, radius constants, and component themes.
- Prefer existing Laravel, Livewire, Flux, and Flutter components when they preserve this design language.
- Implementation may adapt exact layout measurements to real data, accessibility, framework behavior, and responsive constraints.

## 20. Generation Checklist

Before accepting a generated screen, verify:

1. The interface uses Indonesian copy.
2. The canonical color roles are recognizable.
3. Text is not smaller than 12px.
4. Mobile touch targets are at least 48×48dp.
5. Icons follow one line-icon family.
6. Navigation matches the current role.
7. Points, Rupiah, and Midtrans remain separate.
8. Loading, empty, error, and success implications are considered.
9. Unsupported operational claims are absent.
10. The screen is responsive and remains useful with realistic data.
