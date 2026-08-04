<?php

use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Collections\Pages\ListCollections;
use App\Models\Category;
use App\Models\Collection;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->actingAs($this->user);
});

it('renders the collections list even when an icon is not a valid heroicon', function () {
    Collection::factory()->for($this->user)->create(['icon' => 'Nemo voluptatem fug']);

    Livewire::test(ListCollections::class)->assertSuccessful();
});

it('renders the categories list even when an icon is not a valid heroicon', function () {
    Category::factory()->for($this->user)->create(['icon' => 'not-an-icon']);

    Livewire::test(ListCategories::class)->assertSuccessful();
});

it('returns the icon only when it is a valid heroicon name', function () {
    expect(Collection::factory()->for($this->user)->create(['icon' => null])->iconSvgName())->toBeNull()
        ->and(Collection::factory()->for($this->user)->create(['icon' => 'Nemo voluptatem fug'])->iconSvgName())->toBeNull()
        ->and(Collection::factory()->for($this->user)->create(['icon' => 'heroicon-o-folder'])->iconSvgName())->toBe('heroicon-o-folder')
        ->and(Collection::factory()->for($this->user)->create(['icon' => 'o-folder'])->iconSvgName())->toBe('o-folder')
        ->and(Category::factory()->for($this->user)->create(['icon' => 'heroicon-o-tag'])->iconSvgName())->toBe('heroicon-o-tag');
});
