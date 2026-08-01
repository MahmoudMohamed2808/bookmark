# Public-Facing Pages

Since this is a **personal** tool (single user, not a SaaS), the "public" surface is small on purpose — there's no pricing page, no marketing site, no public user directory. What's genuinely public is: the pages Fortify requires before login, and the one truly public feature on the roadmap (Share Links / RSS). Everything else in the app lives behind auth and isn't covered here.

---

## Welcome / Landing Page
`GET /` — `resources/js/pages/welcome.tsx`

- If already authenticated → redirect straight to `/dashboard`, don't show a landing page to yourself.
- Otherwise: a minimal branded splash (logo, tagline, Login/Register buttons) rather than a full marketing page — you don't need a features grid to sell yourself on your own app.
- **Nice tweaks:** respect system dark/light mode by default; subtle background pattern using favicons of well-known dev sites (GitHub, Laravel, PHP.net) as decoration, purely aesthetic.

---

## Login Page
`GET /login` — `resources/js/pages/auth/login.tsx`

- Standard Fortify email/password + "remember me".
- **Nice tweaks:**
  - Autofocus the email field on load.
  - `⌘/Ctrl + Enter` submits the form from either field.
  - If 2FA is enabled account-wide, skip straight to it after a correct password (no extra click).

---

## Register Page
`GET /register` — `resources/js/pages/auth/register.tsx`

- **Recommendation:** since this is a personal, single-user tool, consider gating registration behind an `ALLOW_REGISTRATION=false` env flag once your own account exists — otherwise anyone who finds the URL can create an account. Filament admin can still create additional users manually if you ever need a second one (e.g. spouse, teammate).
- **Nice tweaks:** if registration is closed, redirect this route to `/login` with a friendly flash message instead of a raw 403.

---

## Forgot Password / Reset Password Pages
`GET /forgot-password`, `GET /reset-password/{token}` — `resources/js/pages/auth/forgot-password.tsx`, `reset-password.tsx`

- Standard Fortify flow, kept intentionally plain.
- **Nice tweaks:** password strength meter on the reset form; auto-focus new password field.

---

## Email Verification Notice
`GET /verify-email` — `resources/js/pages/auth/verify-email.tsx`

- **Nice tweaks:** resend button with a visible 60-second cooldown timer instead of letting it be spammed.

---

## Two-Factor Challenge Page
`GET /two-factor-challenge` — `resources/js/pages/auth/two-factor-challenge.tsx`

*(ships with the Fortify starter kit)*

- **Nice tweaks:**
  - "Remember this device for 30 days" checkbox to avoid re-entering a code every login on your own machine.
  - Toggle link to switch between authenticator code and recovery code input.

---

## Public Share Link View *(future feature)*
`GET /s/{token}` — `resources/js/pages/public/shared-bookmark.tsx`

- Read-only page showing a single shared bookmark or collection: title, description, favicon, tags — no edit controls, no owner info beyond what you choose to show.
- **Nice tweaks:**
  - "Expires in 3 days" banner if the share link has a TTL.
  - "Save this to your own library" CTA that, if the visitor is logged in, one-clicks a copy into their bookmarks.
  - No visit-by-visit tracking beyond a simple counter — don't build an analytics dashboard for anonymous visitors.

---

## Public RSS Feed *(future feature)*
`GET /feed/{token}.xml` — not a page, a raw XML response (no Inertia component)

- Optional per-collection public feed so a curated collection (e.g. "Laravel News I liked") can be subscribed to in a feed reader.
- **Nice tweaks:** cache the generated XML for a few minutes so re-subscribing readers don't hammer the DB on every poll.

---

## Error Pages
`404`, `403`, `419` (session/CSRF expired), `500` — `resources/js/pages/errors/*.tsx`

- **Nice tweaks:**
  - `419` specifically: auto-refresh the CSRF token and silently retry the last action once, rather than showing a scary error for what's usually just an idle tab.
  - `404`: single "Back to Dashboard" button, no dead-end.

---

## Maintenance Mode Page
`php artisan down` fallback view — `resources/views/errors/503.blade.php`

- Doesn't use Inertia (rendered before the app boots), so it stays a plain Blade view.
- **Nice tweaks:** short "back shortly" message, and if you set a `retry` header via `artisan down --retry=60`, browsers will auto-reload.
