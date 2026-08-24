# Design

## Theme

Professional SaaS admin dashboard, Arabic RTL, official/corporate tone. Light theme with a deep-navy sidebar. Restrained color strategy: one primary blue carries actions and states; everything else neutral.

Physical scene: an admin at a desk on a laptop in an office, plus a visitor on a phone scanning a QR. The office screen needs calm density; the phone screen needs instant reassurance.

## Color Palette

| Token | Value | Usage |
|---|---|---|
| `--primary` | `#2B5DF0` | Primary actions, active nav, links, charts |
| `--primary-dark` | `#1D44C4` | Hover/active states of primary |
| `--primary-soft` | `#E8EEFF` | Tinted backgrounds for icons/badges |
| `--sidebar-bg` | `#0B1530` | Sidebar surface (deep navy) |
| `--sidebar-text` | `#C6D2EC` | Sidebar body text |
| `--bg` | `#F4F6FA` | Page background (calm cool neutral) |
| `--surface` | `#FFFFFF` | Cards, topbar |
| `--ink` | `#17203A` | Headings/primary text |
| `--text` | `#33405C` | Body text (≥4.5:1 on white) |
| `--muted` | `#5B6B89` | Secondary text (≥4.5:1 on white) |
| `--border` | `#E4E9F2` | Hairlines between surfaces |
| `--success` | `#16A34A` | Active status, success states |
| `--danger` | `#DC2626` | Destructive actions, disabled status |
| `--warning` | `#D97706` | Warnings, pending |
| `--info` | `#0891B2` | Informational accents |

Status badges: active → green tint, inactive → gray tint, disabled → red tint. Always with a status dot + text.

## Typography

- **Family**: Cairo (local woff2, weights 400/500/600/700/800). Applied globally.
- **Scale**: 12 / 13 / 14 (base) / 16 / 18 / 20 / 24 / 28 / 32. Fixed rem scale — no fluid clamp in product UI.
- **Headings**: Cairo 700–800, tight but not cramped; `text-wrap: balance` on h1–h3.
- **Body**: 14px base, line-height 1.6; data tables 13.5px, denser.
- **Digits/IDs**: keep `direction: ltr` for doc numbers, phones, IPs.

## Radius / Shadow / Motion

- Radius: 8px (inputs/buttons), 10px (small chips), 14px (cards), 18px (modals/auth card). No pills except badges/tags.
- Shadow: single soft shadow `0 1px 2px rgba(15,30,60,.05), 0 8px 24px -12px rgba(15,30,60,.10)` on cards; never border+wide-shadow together.
- Motion: 150–250ms ease-out for hovers/transitions; drawer 260ms; toasts slide-in 220ms. `prefers-reduced-motion` → instant.

## Components

- **Buttons**: one shape (radius 10px, 44px height default), 5 variants: primary (filled blue), secondary (light gray), danger (red tint → solid on hover), ghost (transparent, border on hover), icon-square (40px). Loading state = spinner + label swap. Disabled = 50% opacity, no pointer.
- **Inputs**: 44px height, 12px radius... (see tokens above: 8px), label above, placeholder ≥4.5:1, focus ring = 3px `--primary-soft` + 1.5px primary border. Error state: red border + message below. Password fields get a show/hide eye button.
- **Cards**: white surface, 14px radius, hairline border, soft shadow. Headers: title + icon chip + optional action.
- **Tables**: thead 12.5px bold muted on `#F8FAFD`; rows 13.5px, hover tint; actions as compact icon buttons; wrap in `overflow-x:auto` with min-width on desktop only; mobile converts to stacked cards (via `.table-card` toggle at ≤768px).
- **Sidebar**: 268px fixed on desktop (sticky, full height); drawer on mobile (translate + backdrop). Active item = primary soft background on navy + primary right-edge indicator (2px, not a side stripe >1px... use a filled rounded pill background instead). Logout separated at bottom.
- **Topbar**: 64px sticky white, page title + breadcrumb right, actions left (bell, user menu, mobile burger).
- **Modals**: centered, 18px radius, backdrop blur(2px) + dark; confirm modal = icon circle + title + message + cancel/confirm buttons; QR modal shows QR on white canvas area.
- **Toasts**: fixed top-left (RTL start), white surface with colored icon chip + border tint, auto-dismiss 4s, slide-in.
- **Empty states**: centered icon in tinted circle + title + hint + optional CTA.
- **Skeleton**: shimmer blocks for loading (used on dashboard chart area before JS renders).

## Layout

- Admin shell: sidebar (fixed width) + main column (topbar + scrollable content, max-width 1400px centered).
- Content padding: 24px desktop / 16px mobile; section gaps 20–24px.
- Dashboard: stat cards grid `repeat(auto-fit, minmax(220px, 1fr))`; charts row = 2/3 + 1/3; latest tables below.
- Forms: single column up to 640px; two-column grid on md+.

## Charts

Chart.js 4 (local vendor, CSP-safe). Line/bar chart for verifications last 7 days; doughnut for document status distribution. Colors from palette; Arabic fonts; tooltips RTL. Empty data → professional empty state, never fake data.
