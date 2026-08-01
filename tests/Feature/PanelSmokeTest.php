<?php

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\Backups\BackupResource;
use App\Filament\Resources\Backups\Pages\ListBackups;
use App\Filament\Resources\Bookmarks\BookmarkResource;
use App\Filament\Resources\Bookmarks\Pages\CreateBookmark;
use App\Filament\Resources\Bookmarks\Pages\EditBookmark;
use App\Filament\Resources\Bookmarks\Pages\ListBookmarks;
use App\Filament\Resources\Bookmarks\Pages\ViewBookmark;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Collections\CollectionResource;
use App\Filament\Resources\Collections\Pages\CreateCollection;
use App\Filament\Resources\Collections\Pages\EditCollection;
use App\Filament\Resources\Reminders\Pages\CreateReminder;
use App\Filament\Resources\Reminders\ReminderResource;
use App\Filament\Resources\Snippets\Pages\CreateSnippet;
use App\Filament\Resources\Snippets\Pages\EditSnippet;
use App\Filament\Resources\Snippets\SnippetResource;
use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Reminder;
use App\Models\Snippet;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->actingAs($this->user);
});

it('renders the admin dashboard for authenticated users', function () {
    $this->get('/admin')->assertSuccessful();
});

it('renders every resource list page', function () {
    Livewire::test(ListBookmarks::class)->assertSuccessful();
    Livewire::test(ListActivityLogs::class)->assertSuccessful();
    Livewire::test(ListBackups::class)->assertSuccessful();
});

it('renders the users list and shows the current user', function () {
    Livewire::test(\App\Filament\Resources\Users\Pages\ListUsers::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$this->user]);
});

it('renders create pages', function () {
    Livewire::test(CreateUser::class)->assertSuccessful();
    Livewire::test(CreateBookmark::class)->assertSuccessful();
    Livewire::test(CreateCategory::class)->assertSuccessful();
    Livewire::test(CreateCollection::class)->assertSuccessful();
    Livewire::test(CreateSnippet::class)->assertSuccessful();
    Livewire::test(CreateReminder::class)->assertSuccessful();
    Livewire::test(CreateTag::class)->assertSuccessful();
});

it('renders edit pages for existing records', function () {
    $bookmark = Bookmark::factory()->create(['user_id' => $this->user->id]);
    $category = Category::factory()->create(['user_id' => $this->user->id]);
    $collection = Collection::factory()->create(['user_id' => $this->user->id]);
    $snippet = Snippet::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(EditUser::class, ['record' => $this->user->id])->assertSuccessful();
    Livewire::test(EditBookmark::class, ['record' => $bookmark->id])->assertSuccessful();
    Livewire::test(EditCategory::class, ['record' => $category->id])->assertSuccessful();
    Livewire::test(EditCollection::class, ['record' => $collection->id])->assertSuccessful();
    Livewire::test(EditSnippet::class, ['record' => $snippet->id])->assertSuccessful();
});

it('renders the bookmark view page with its relation managers', function () {
    $bookmark = Bookmark::factory()->create(['user_id' => $this->user->id]);

    Reminder::factory()->create([
        'bookmark_id' => $bookmark->id,
        'remind_at' => now()->addDay(),
    ]);

    ActivityLog::factory()->create([
        'user_id' => $this->user->id,
        'bookmark_id' => $bookmark->id,
        'action' => 'created',
    ]);

    Livewire::test(ViewBookmark::class, ['record' => $bookmark->id])
        ->assertSuccessful();
});

it('shows bookmarks in the table with custom actions', function () {
    $bookmark = Bookmark::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(ListBookmarks::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$bookmark])
        ->assertTableActionExists('fetchMetadata')
        ->assertTableActionExists('checkHealth')
        ->assertTableActionExists('togglePin')
        ->assertTableActionExists('toggleFavorite')
        ->assertTableActionExists('toggleReadLater')
        ->assertTableActionExists('toggleArchive')
        ->assertTableBulkActionExists('archiveBulk')
        ->assertTableBulkActionExists('addToCollectionBulk')
        ->assertTableBulkActionExists('addTagsBulk')
        ->assertTableBulkActionExists('checkLinksBulk');
});

it('creates a bookmark through the create page form', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(CreateBookmark::class)
        ->fillForm([
            'title' => 'Laravel docs',
            'url' => 'https://laravel.com/docs',
            'category_id' => $category->id,
            'pinned_at' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('bookmarks', [
        'user_id' => $this->user->id,
        'title' => 'Laravel docs',
        'url' => 'https://laravel.com/docs',
    ]);

    $this->assertNotNull(Bookmark::first()->pinned_at);
});

it('restricts the backup and activity log resources to listing', function () {
    expect(BackupResource::canCreate())->toBeFalse();
    expect(ActivityLogResource::canCreate())->toBeFalse();

    expect(BackupResource::getPages())->not->toHaveKey('create');
    expect(ActivityLogResource::getPages())->not->toHaveKey('create');
});
