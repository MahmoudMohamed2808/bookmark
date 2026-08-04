<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\StoreBookmarkRequest;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Collection;
use App\Services\ActivityLogService;
use App\Services\BookmarkMetadataService;
use App\Services\DuplicateDetectionService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Tags\Tag;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DuplicateDetectionService $duplicates,
        private readonly BookmarkMetadataService $metadata,
        private readonly ActivityLogService $activityLog,
    ) {}

    /**
     * Show the bookmark dashboard with stats, recent bookmarks and quick lists.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $search = trim((string) $request->query('q'));

        $bookmarks = Bookmark::query()
            ->with(['category', 'tags'])
            ->where('user_id', $user->id)
            ->whereNull('archived_at')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('tags', fn ($tags) => $tags->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return Inertia::render('dashboard', [
            'q' => $search,
            'stats' => $this->stats($user->id),
            'bookmarks' => $bookmarks,
            'pinned' => $this->recentFlags($user->id, 'pinned_at', 5),
            'readLater' => $this->recentFlags($user->id, 'read_later_at', 5),
            'mostVisited' => Bookmark::query()
                ->with(['category', 'tags'])
                ->where('user_id', $user->id)
                ->whereNull('archived_at')
                ->orderByDesc('visits_count')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
            'categories' => Category::query()
                ->where('user_id', $user->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'parent_id', 'name']),
        ]);
    }

    /**
     * Quickly save a new bookmark from the dashboard.
     */
    public function store(StoreBookmarkRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($duplicate = $this->duplicates->findDuplicate($data['url'], $user->id)) {
            Inertia::flash('toast', ['type' => 'info', 'message' => __('That URL is already saved.')]);

            return back();
        }

        $title = $data['title'] ?? null;
        $description = $data['description'] ?? null;
        $favicon = null;

        if ($title === null || $title === '') {
            $metadata = $this->metadata->fetch($data['url']);
            $title = $metadata['title'] ?: $this->hostFromUrl($data['url']);
            $description ??= $metadata['description'];
            $favicon = $metadata['favicon'];
        }

        $bookmark = $user->bookmarks()->create([
            'category_id' => $data['category_id'] ?? null,
            'title' => $title,
            'url' => $data['url'],
            'description' => $description,
            'favicon' => $favicon,
        ]);

        if (! empty($data['tags'])) {
            $bookmark->attachTags($data['tags']);
        }

        $this->activityLog->log($user, 'created', $bookmark);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bookmark added.')]);

        return back();
    }

    /**
     * @return array<string, int>
     */
    private function stats(int $userId): array
    {
        $tagIds = DB::table('taggables')
            ->join('bookmarks', function ($join) use ($userId): void {
                $join->on('taggables.taggable_id', '=', 'bookmarks.id')
                    ->where('taggables.taggable_type', '=', Bookmark::class)
                    ->where('bookmarks.user_id', '=', $userId);
            })
            ->distinct()
            ->pluck('taggables.tag_id');

        return [
            'total' => Bookmark::where('user_id', $userId)->count(),
            'pinned' => Bookmark::where('user_id', $userId)->whereNotNull('pinned_at')->count(),
            'favorites' => Bookmark::where('user_id', $userId)->whereNotNull('favorited_at')->count(),
            'readLater' => Bookmark::where('user_id', $userId)->whereNotNull('read_later_at')->whereNull('archived_at')->count(),
            'archived' => Bookmark::where('user_id', $userId)->whereNotNull('archived_at')->count(),
            'broken' => Bookmark::where('user_id', $userId)->where('is_broken', true)->count(),
            'categories' => Category::where('user_id', $userId)->count(),
            'collections' => Collection::where('user_id', $userId)->count(),
            'tags' => Tag::whereKey($tagIds)->count(),
        ];
    }

    private function recentFlags(int $userId, string $column, int $limit): EloquentCollection
    {
        return Bookmark::query()
            ->with(['category', 'tags'])
            ->where('user_id', $userId)
            ->whereNotNull($column)
            ->whereNull('archived_at')
            ->orderByDesc($column)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    private function hostFromUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return $host ?: $url;
    }
}
