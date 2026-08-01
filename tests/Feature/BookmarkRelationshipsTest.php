<?php

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Snippet;
use App\Models\User;
use App\Models\UserSetting;

it('has all documented relationships', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    $child = Category::factory()->childOf($category)->create();
    $bookmark = Bookmark::factory()->inCategory($category)->create(['user_id' => $user->id]);
    $collection = Collection::factory()->create(['user_id' => $user->id]);
    $snippet = Snippet::factory()->inCategory($category)->create(['user_id' => $user->id]);
    UserSetting::factory()->create(['user_id' => $user->id]);

    $bookmark->attachTags(['laravel', 'docs']);
    $bookmark->collections()->attach($collection->id, ['sort_order' => 1]);

    expect($user->bookmarks)->toHaveCount(1)
        ->and($user->categories)->toHaveCount(2)
        ->and($user->collections)->toHaveCount(1)
        ->and($user->snippets)->toHaveCount(1)
        ->and($user->backups)->toHaveCount(0)
        ->and($user->activityLogs)->toHaveCount(0)
        ->and($user->settings)->not->toBeNull();

    expect($category->user->is($user))->toBeTrue()
        ->and($category->children)->toHaveCount(1)
        ->and($child->parent->is($category))->toBeTrue()
        ->and($category->bookmarks)->toHaveCount(1)
        ->and($category->snippets)->toHaveCount(1);

    expect($bookmark->user->is($user))->toBeTrue()
        ->and($bookmark->category->is($category))->toBeTrue()
        ->and($bookmark->collections)->toHaveCount(1)
        ->and($bookmark->collections->first()->pivot->sort_order)->toBe(1)
        ->and($bookmark->tags->pluck('name')->sort()->values()->all())->toBe(['docs', 'laravel'])
        ->and($bookmark->isPinned())->toBeFalse()
        ->and($bookmark->isFavorited())->toBeFalse()
        ->and($bookmark->isArchived())->toBeFalse();

    expect($collection->user->is($user))->toBeTrue()
        ->and($collection->bookmarks)->toHaveCount(1)
        ->and($snippet->user->is($user))->toBeTrue()
        ->and($snippet->category->is($category))->toBeTrue();
});

it('supports the status flag states', function () {
    $pinned = Bookmark::factory()->pinned()->favorited()->readLater()->archived()->create();

    expect($pinned->isPinned())->toBeTrue()
        ->and($pinned->isFavorited())->toBeTrue()
        ->and($pinned->isInReadLater())->toBeTrue()
        ->and($pinned->isArchived())->toBeTrue();
});

it('supports soft deletes for trash', function () {
    $bookmark = Bookmark::factory()->create();

    $bookmark->delete();

    expect(Bookmark::find($bookmark->id))->toBeNull()
        ->and(Bookmark::withTrashed()->find($bookmark->id))->not->toBeNull();
});
