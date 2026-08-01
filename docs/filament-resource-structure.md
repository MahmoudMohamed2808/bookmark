# Filament v5 — Resource Structure

Structure only (no code). Packages that ship their own Filament integration (tags, media, roles/permissions) are noted but not built as custom resources.

---

## UserResource
`App\Models\User`

- **Relation Managers**
  - BookmarksRelationManager
  - CollectionsRelationManager
  - SnippetsRelationManager
- **Custom Pages**
  - ViewUser (profile overview: counts, storage usage, last login)
- **Custom Actions**
  - Impersonate User
  - Force Password Reset
  - Suspend / Unsuspend User
  - Export User's Data (JSON/CSV)

---

## BookmarkResource
`App\Models\Bookmark`

- **Relation Managers**
  - CollectionsRelationManager (belongsToMany)
  - RemindersRelationManager
  - ActivityLogRelationManager (read-only)
- **Custom Pages**
  - ViewBookmark (rich preview: favicon, description, link status, visit stats)
- **Custom Actions**
  - *Row actions:* Fetch Metadata (title/favicon/description), Check Link Health, Mark as Broken/Working, Pin/Unpin, Favorite/Unfavorite, Archive/Unarchive, Restore (trashed), Force Delete
  - *Bulk actions:* Bulk Archive, Bulk Add to Collection, Bulk Add Tags, Bulk Check Links, Bulk Export (JSON/CSV/HTML), Bulk Restore, Bulk Force Delete
  - *Header actions:* Import Bookmarks (browser HTML import), Quick Add (modal form)

---

## CategoryResource
`App\Models\Category`

- **Relation Managers**
  - BookmarksRelationManager
  - ChildCategoriesRelationManager (self-referencing)
- **Custom Pages**
  - Tree (custom page rendering nested categories as a draggable tree)
- **Custom Actions**
  - Reorder (drag & drop sort)
  - Merge Into… (merge one category's bookmarks into another, then delete)

---

## CollectionResource
`App\Models\Collection`

- **Relation Managers**
  - BookmarksRelationManager (belongsToMany, with pivot `sort_order`)
- **Custom Pages**
  - none beyond List/Create/Edit
- **Custom Actions**
  - Reorder Bookmarks (drag & drop within collection)
  - Duplicate Collection
  - Generate Public Share Link *(future feature — placeholder action)*

---

## SnippetResource
`App\Models\Snippet`

*(Code Snippets, Favorite Commands, Terminal Notes — filtered by `type` via tabs)*

- **Relation Managers**
  - none (Category is a simple belongsTo select, no manager needed)
- **Custom Pages**
  - none beyond List/Create/Edit (List uses tabs: Snippets / Commands / Terminal Notes)
- **Custom Actions**
  - Copy Content to Clipboard
  - Duplicate Snippet

---

## ReminderResource
`App\Models\Reminder`

- **Relation Managers**
  - none (belongsTo Bookmark, shown as a column/link instead)
- **Custom Pages**
  - none beyond List/Create/Edit
- **Custom Actions**
  - Mark as Notified
  - Snooze (push `remind_at` forward)

---

## BackupResource
`App\Models\Backup`

- **Relation Managers**
  - none
- **Custom Pages**
  - none beyond List
- **Custom Actions**
  - *Header action:* Generate New Backup (dispatches export job)
  - *Row actions:* Download Backup File, Retry Failed Backup, Delete Backup File

---

## ActivityLogResource
`App\Models\ActivityLog`

- **Relation Managers**
  - none
- **Custom Pages**
  - List only (read-only — no Create/Edit pages registered)
- **Custom Actions**
  - Bulk: Clear Logs Older Than… (date-based bulk delete)

---

## TagResource
`Spatie\Tags\Tag` (via `filament/spatie-laravel-tags-plugin` where possible; custom resource only if you need full tag management beyond the form field)

- **Relation Managers**
  - TaggedBookmarksRelationManager
- **Custom Pages**
  - none
- **Custom Actions**
  - Merge Tags
  - Rename Tag (with cascade to all taggables)

---

## Provided by packages (not custom-built)

- **RoleResource / PermissionResource** — via `bezhansalleh/filament-shield` on top of `spatie/laravel-permission`
- **Media management** — via `spatie/laravel-medialibrary`'s Filament field/plugin, embedded inside Bookmark/Snippet forms rather than its own resource
- **MediaLibraryResource** *(optional)* — only if you want a standalone "all uploaded files" browser page

---

## Dashboard Widgets (not resources, but part of the panel)

- StatsOverviewWidget — total bookmarks, categories, broken links, pending reminders
- MostVisitedBookmarksWidget (table widget)
- CategoryStatisticsChartWidget
- TagStatisticsChartWidget
- BrokenLinksWidget (alert/table of `is_broken = true`)
- UpcomingRemindersWidget
