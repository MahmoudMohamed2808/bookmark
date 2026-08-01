<?php

namespace App\Services;

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;
use DOMDocument;
use DOMElement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class BookmarkImportService
{
    public function __construct(private readonly UrlNormalizer $normalizer) {}

    /**
     * Parse a Netscape-format HTML bookmarks file and create records.
     *
     * Folders become nested categories, links become bookmarks.
     */
    public function importFromHtml(UploadedFile $file, User $user): int
    {
        $html = $file->get();

        if ($html === false) {
            return 0;
        }

        $dom = new DOMDocument();

        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML($this->sanitize($html), LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $count = 0;

        foreach ($dom->getElementsByTagName('dl') as $list) {
            if ($this->hasDlAncestor($list)) {
                continue;
            }

            $count += $this->importList($list, $user, null);
        }

        return $count;
    }

    /**
     * Make the Netscape bookmark format safe for the HTML parser:
     * drop the unclosed <p> markers and explicitly close unclosed <dt>/<dd> tags.
     */
    private function sanitize(string $html): string
    {
        $html = preg_replace('/<\/?p[^>]*>/i', '', $html);
        $html = preg_replace('/<dt\s*>/i', '</DT><DT>', $html);
        $html = preg_replace('/<dd\s*>/i', '</DD><DD>', $html);

        return preg_replace('/^<\/DT>|^<\/DD>/i', '', $html) ?? $html;
    }

    private function hasDlAncestor(DOMElement $node): bool
    {
        for ($parent = $node->parentNode; $parent !== null; $parent = $parent->parentNode) {
            if ($parent instanceof DOMElement && $parent->tagName === 'dl') {
                return true;
            }
        }

        return false;
    }

    private function importList(DOMElement $list, User $user, ?Category $parent): int
    {
        $count = 0;

        foreach ($list->childNodes as $node) {
            if (! $node instanceof DOMElement || $node->tagName !== 'dt') {
                continue;
            }

            $anchor = $this->firstElementChild($node);

            if ($anchor === null) {
                continue;
            }

            if ($anchor->tagName === 'h3') {
                $category = $this->createCategory($anchor->textContent, $user, $parent);
                $nestedList = $this->nextList($node);

                if ($nestedList !== null) {
                    $count += $this->importList($nestedList, $user, $category ?? $parent);
                }
            } elseif ($anchor->tagName === 'a') {
                $count += $this->createBookmark($anchor, $user, $parent);
            }
        }

        return $count;
    }

    private function firstElementChild(DOMElement $node): ?DOMElement
    {
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                return $child;
            }
        }

        return null;
    }

    /**
     * Find the nested <dl> that follows a folder <dt>.
     */
    private function nextList(DOMElement $node): ?DOMElement
    {
        for ($sibling = $node->nextSibling; $sibling !== null; $sibling = $sibling->nextSibling) {
            if ($sibling instanceof DOMElement && $sibling->tagName === 'dl') {
                return $sibling;
            }
        }

        return null;
    }

    private function createCategory(string $name, User $user, ?Category $parent): ?Category
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        return Category::create([
            'user_id' => $user->id,
            'parent_id' => $parent?->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
        ]);
    }

    private function createBookmark(DOMElement $anchor, User $user, ?Category $category): int
    {
        $url = $anchor->getAttribute('href');

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return 0;
        }

        Bookmark::create([
            'user_id' => $user->id,
            'category_id' => $category?->id,
            'title' => trim($anchor->textContent) ?: $url,
            'url' => $url,
            'url_hash' => $this->normalizer->hash($url),
            'favicon' => $anchor->getAttribute('icon') ?: null,
        ]);

        return 1;
    }
}
