# Bookmark Manager — Final Feature List

**Stack:** Laravel 13 · React + Inertia (official starter kit, Fortify auth) · Filament v5 (admin) · spatie/laravel-medialibrary · spatie/laravel-tags (+ Filament plugins)

Notes on consolidation from the original list:
- **Favorites / Pin / Read Later / Archive** are modeled as status flags (e.g. `pinned_at`, `favorited_at`, `read_later_at`, `archived_at` timestamps) on the Bookmark model, not separate features.
- **Tags** are handled entirely by `spatie/laravel-tags` + its Filament plugin — no custom tag CRUD needed.
- **Trash** is native Laravel soft deletes — no custom implementation needed.
- **Admin** section is Filament v5, not custom-built.
- **File/Image attachments** are handled by `spatie/laravel-medialibrary` — no custom upload system needed.
- **Categories vs Collections**: Categories = required, nested, structural (nested set via adjacency list). Collections = optional, manual, cross-cutting groupings.

---

## Phase 1 — Core Foundation
- [ ] User Authentication (Fortify, via starter kit)
- [ ] Dashboard
- [ ] Bookmark CRUD
- [ ] Categories (nested)
- [ ] Tags (spatie/laravel-tags)
- [ ] Favorites (status flag)
- [ ] Pin Bookmarks (status flag)
- [ ] Archive (status flag)
- [ ] Trash (soft delete)
- [ ] Notes
- [ ] Search (basic query)
- [ ] Recent Bookmarks

## Phase 2 — URL Intelligence & Quick Capture
- [ ] URL Validation
- [ ] Auto Fetch Title
- [ ] Auto Fetch Favicon
- [ ] Auto Fetch Description
- [ ] Duplicate Detection (URL normalization)
- [ ] Quick Add Bookmark
- [ ] Keyboard Shortcuts
- [ ] Copy URL
- [ ] Share Bookmark

## Phase 3 — Developer Features (differentiator, don't push late)
- [ ] Code Snippets
- [ ] Documentation Links
- [ ] GitHub Repositories
- [ ] Package Library
- [ ] API References
- [ ] Laravel Resources
- [ ] Favorite Commands
- [ ] Terminal Notes

## Phase 4 — Organization & Files
- [ ] Collections (manual, cross-cutting)
- [ ] Nested Categories (adjacency list)
- [ ] Custom Icons
- [ ] Custom Colors
- [ ] Drag & Drop Sorting
- [ ] Attach Images / Upload Files (spatie/laravel-medialibrary)
- [ ] Markdown Notes
- [ ] Advanced Filters
- [ ] Bulk Actions

## Phase 5 — Import / Export / Backup
- [ ] Browser Import (Netscape HTML bookmark format)
- [ ] HTML Export
- [ ] JSON Export
- [ ] CSV Export
- [ ] Backup & Restore

## Phase 6 — Reliability & Review
- [ ] Detect Broken Links (scheduled job)
- [ ] Bookmark Health Check
- [ ] Reminder Bookmarks
- [ ] Expiring Links
- [ ] Scheduled Review (notifications)
- [ ] Read Later (status flag, covered in Phase 1 model but surfaced here as a view/workflow)

## Phase 7 — Analytics & Activity
- [ ] Visit Counter
- [ ] Last Opened
- [ ] Recently Visited
- [ ] Most Visited
- [ ] Category Statistics
- [ ] Tag Statistics
- [ ] Activity Log

## Phase 8 — Settings & Polish
- [ ] Dark/Light Mode
- [ ] Theme Colors
- [ ] Language (EN/AR)
- [ ] Profile Settings

## Phase 9 — Admin (Filament v5)
- [ ] Roles & Permissions (spatie/laravel-permission + Filament)
- [ ] User Management
- [ ] System Logs
- [ ] Application Settings

## Future / Post-MVP
- [ ] Browser Extension
- [ ] Mobile App API (build API-first from Phase 1 so this isn't a retrofit)
- [ ] AI Tag Suggestions
- [ ] AI Bookmark Summary
- [ ] OCR for Images
- [ ] Full-Text Search (Scout + Meilisearch/Typesense — only when LIKE search hurts)
- [ ] Public Share Links
- [ ] QR Code Generator
- [ ] RSS Feed Support
- [ ] Collections Templates

---

*Ready for database schema design whenever you want to move forward.*
