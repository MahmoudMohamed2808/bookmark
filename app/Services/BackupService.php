<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupService
{
    public function __construct(private readonly BookmarkExportService $exporter) {}

    /**
     * Generate a backup file and track it with a Backup record.
     */
    public function generate(User $user, string $type): Backup
    {
        if (! in_array($type, ['json', 'csv', 'html'], true)) {
            throw new \InvalidArgumentException("Unsupported backup type [{$type}].");
        }

        $backup = Backup::create([
            'user_id' => $user->id,
            'type' => $type,
            'status' => 'pending',
        ]);

        try {
            $content = match ($type) {
                'json' => $this->exporter->toJson($user),
                'csv' => $this->exporter->toCsv($user),
                'html' => $this->exporter->toHtml($user),
            };

            $filename = sprintf('backup-%d-%s.%s', $user->id, now()->format('Y-m-d-His'), $type);

            Storage::disk('local')->put('backups/'.$filename, $content);

            $backup->forceFill([
                'file_path' => 'backups/'.$filename,
                'status' => 'completed',
            ])->save();
        } catch (Throwable $e) {
            $backup->forceFill([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ])->save();
        }

        return $backup;
    }

    /**
     * Restore a JSON backup, replacing the user's current bookmarks.
     */
    public function restore(Backup $backup): void
    {
        if ($backup->type !== 'json' || $backup->status !== 'completed' || $backup->file_path === null) {
            throw new \InvalidArgumentException('Only completed JSON backups can be restored.');
        }

        $content = Storage::disk('local')->get($backup->file_path);

        if ($content === null) {
            throw new \RuntimeException('Backup file not found.');
        }

        $bookmarks = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $user = $backup->user;

        DB::transaction(function () use ($user, $bookmarks): void {
            $user->bookmarks()->withTrashed()->forceDelete();
            $user->categories()->delete();

            foreach ($bookmarks as $data) {
                $category = $this->findOrCreateCategory($user, $data['category'] ?? null);

                $bookmark = $user->bookmarks()->create([
                    'title' => $data['title'],
                    'url' => $data['url'],
                    'url_hash' => $data['url_hash'],
                    'description' => $data['description'] ?? null,
                    'favicon' => $data['favicon'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'category_id' => $category?->id,
                ]);

                if (isset($data['tags']) && is_array($data['tags'])) {
                    $bookmark->attachTags($data['tags']);
                }
            }
        });
    }

    private function findOrCreateCategory(User $user, ?string $name): mixed
    {
        if ($name === null || $name === '') {
            return null;
        }

        return $user->categories()->firstOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($name)],
            ['name' => $name],
        );
    }
}
