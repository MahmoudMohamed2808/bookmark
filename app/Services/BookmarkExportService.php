<?php

namespace App\Services;

use App\Models\Bookmark;
use App\Models\User;
use Spatie\Tags\Tag;

class BookmarkExportService
{
    /**
     * Build a JSON export of a user's bookmarks with their categories and tags.
     */
    public function toJson(User $user): string
    {
        return json_encode($this->bookmarks($user), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '';
    }

    /**
     * Build a CSV export of a user's bookmarks.
     */
    public function toCsv(User $user): string
    {
        $stream = fopen('php://temp', 'w+');

        fputcsv($stream, ['title', 'url', 'description', 'category', 'tags', 'notes', 'created_at']);

        foreach ($this->bookmarks($user) as $bookmark) {
            fputcsv($stream, [
                $bookmark['title'],
                $bookmark['url'],
                $bookmark['description'],
                $bookmark['category'],
                implode(',', $bookmark['tags']),
                $bookmark['notes'],
                $bookmark['created_at'],
            ]);
        }

        rewind($stream);

        $csv = stream_get_contents($stream);

        fclose($stream);

        return $csv === false ? '' : $csv;
    }

    /**
     * Build a Netscape-format HTML export of a user's bookmarks.
     */
    public function toHtml(User $user): string
    {
        $lines = [
            '<!DOCTYPE NETSCAPE-Bookmark-file-1>',
            '<!-- This is an automatically generated file. -->',
            '<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">',
            '<TITLE>Bookmarks</TITLE>',
            '<H1>Bookmarks</H1>',
            '<DL><p>',
        ];

        foreach ($this->bookmarks($user) as $bookmark) {
            $lines[] = sprintf(
                '    <DT><A HREF="%s"%s>%s</A>',
                e($bookmark['url']),
                $bookmark['favicon'] ? sprintf(' ICON="%s"', e($bookmark['favicon'])) : '',
                e($bookmark['title']),
            );

            if ($bookmark['description']) {
                $lines[] = '    <DD>'.e($bookmark['description']);
            }
        }

        $lines[] = '</DL><p>';

        return implode("\n", $lines)."\n";
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bookmarks(User $user): array
    {
        return $user->bookmarks()
            ->with(['category', 'tags'])
            ->latest()
            ->get()
            ->map(fn (Bookmark $bookmark): array => [
                'title' => $bookmark->title,
                'url' => $bookmark->url,
                'url_hash' => $bookmark->url_hash,
                'description' => $bookmark->description,
                'favicon' => $bookmark->favicon,
                'notes' => $bookmark->notes,
                'category' => $bookmark->category?->name,
                'tags' => $bookmark->tags->map(fn (Tag $tag): string => (string) $tag->name)->all(),
                'created_at' => $bookmark->created_at?->toIso8601String(),
            ])
            ->all();
    }
}
