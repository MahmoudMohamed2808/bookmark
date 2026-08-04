import { Form, Head, router } from '@inertiajs/react';
import {
    Link2,
    Clock,
    Eye,
    Heart,
    Pin,
    Plus,
    Search,
    SearchX,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import type { ComponentType } from 'react';
import DashboardController from '@/actions/App/Http/Controllers/DashboardController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';

type DashboardStats = {
    total: number;
    pinned: number;
    favorites: number;
    readLater: number;
    archived: number;
    broken: number;
    categories: number;
    collections: number;
    tags: number;
};

type BookmarkItem = {
    id: number;
    title: string;
    url: string;
    description: string | null;
    favicon: string | null;
    pinned_at: string | null;
    favorited_at: string | null;
    read_later_at: string | null;
    archived_at: string | null;
    is_broken: boolean;
    visits_count: number;
    category: { id: number; name: string } | null;
    tags: { id: number; name: string }[];
};

type CategoryItem = {
    id: number;
    parent_id: number | null;
    name: string;
};

type PageProps = {
    q: string;
    stats: DashboardStats;
    bookmarks: BookmarkItem[];
    pinned: BookmarkItem[];
    readLater: BookmarkItem[];
    mostVisited: BookmarkItem[];
    categories: CategoryItem[];
};

export default function Dashboard({
    q,
    stats,
    bookmarks,
    pinned,
    readLater,
    mostVisited,
    categories,
}: PageProps) {
    const [search, setSearch] = useState(q);

    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== q) {
                router.get(
                    dashboard(),
                    { q: search },
                    { preserveState: true, replace: true },
                );
            }
        }, 300);

        return () => clearTimeout(timer);
    }, [search, q]);

    return (
        <>
            <Head title="Dashboard" />

            <div className="space-y-6">
                <Heading
                    title="Dashboard"
                    description="Your bookmark library at a glance"
                />

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label="Total bookmarks"
                        value={stats.total}
                        icon={Link2}
                    />
                    <StatCard label="Pinned" value={stats.pinned} icon={Pin} />
                    <StatCard
                        label="Favorites"
                        value={stats.favorites}
                        icon={Heart}
                    />
                    <StatCard
                        label="Read later"
                        value={stats.readLater}
                        icon={Clock}
                    />
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick add</CardTitle>
                                <CardDescription>
                                    Paste a URL and save it in one step
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Form
                                    {...DashboardController.store.form()}
                                    resetOnSuccess
                                    className="space-y-3"
                                >
                                    {({ errors, processing }) => (
                                        <>
                                            <div className="flex flex-col gap-3 sm:flex-row">
                                                <div className="grid flex-1 gap-2">
                                                    <Label htmlFor="url">
                                                        URL
                                                    </Label>
                                                    <Input
                                                        id="url"
                                                        name="url"
                                                        type="url"
                                                        placeholder="https://example.com/article"
                                                        autoFocus
                                                        required
                                                    />
                                                    <InputError
                                                        className="mt-1"
                                                        message={errors.url}
                                                    />
                                                </div>
                                                <Button
                                                    className="sm:self-end"
                                                    disabled={processing}
                                                >
                                                    {processing ? (
                                                        <Spinner className="size-4" />
                                                    ) : (
                                                        <Plus />
                                                    )}
                                                    Add bookmark
                                                </Button>
                                            </div>
                                            <div className="flex flex-col gap-3 sm:flex-row">
                                                <div className="grid flex-1 gap-2">
                                                    <Label htmlFor="title">
                                                        Title{' '}
                                                        <span className="font-normal text-muted-foreground">
                                                            (optional,
                                                            auto-fetched)
                                                        </span>
                                                    </Label>
                                                    <Input
                                                        id="title"
                                                        name="title"
                                                        type="text"
                                                        placeholder="Article title"
                                                    />
                                                </div>
                                                <div className="grid gap-2 sm:w-56">
                                                    <Label htmlFor="category_id">
                                                        Category{' '}
                                                        <span className="font-normal text-muted-foreground">
                                                            (optional)
                                                        </span>
                                                    </Label>
                                                    <select
                                                        id="category_id"
                                                        name="category_id"
                                                        className="flex h-9 w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                        defaultValue=""
                                                    >
                                                        <option value="">
                                                            No category
                                                        </option>
                                                        {flattenCategories(
                                                            categories,
                                                        ).map((category) => (
                                                            <option
                                                                key={
                                                                    category.id
                                                                }
                                                                value={
                                                                    category.id
                                                                }
                                                            >
                                                                {category.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </div>
                                            </div>
                                        </>
                                    )}
                                </Form>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between gap-4">
                                    <div>
                                        <CardTitle>
                                            {q
                                                ? 'Search results'
                                                : 'Recent bookmarks'}
                                        </CardTitle>
                                        <CardDescription>
                                            {q
                                                ? `${bookmarks.length} result${
                                                      bookmarks.length === 1
                                                          ? ''
                                                          : 's'
                                                  } for “${q}”`
                                                : 'Latest additions to your library'}
                                        </CardDescription>
                                    </div>
                                    <div className="relative w-full max-w-56">
                                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                        <Input
                                            value={search}
                                            onChange={(event) =>
                                                setSearch(event.target.value)
                                            }
                                            placeholder="Search your library…"
                                            className="pl-9"
                                        />
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {bookmarks.length === 0 ? (
                                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-12 text-center">
                                        <SearchX className="size-8 text-muted-foreground" />
                                        <p className="mt-2 text-sm font-medium">
                                            {q
                                                ? 'No bookmarks match your search.'
                                                : 'Your library is empty.'}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {q
                                                ? 'Try a different query.'
                                                : 'Add your first bookmark above.'}
                                        </p>
                                    </div>
                                ) : (
                                    <div className="space-y-2">
                                        {bookmarks.map((bookmark) => (
                                            <BookmarkRow
                                                key={bookmark.id}
                                                bookmark={bookmark}
                                            />
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <QuickList
                            title="Pinned"
                            icon={Pin}
                            bookmarks={pinned}
                            emptyText="Nothing pinned yet"
                        />
                        <QuickList
                            title="Read later"
                            icon={Clock}
                            bookmarks={readLater}
                            emptyText="Nothing in read later"
                        />
                        <QuickList
                            title="Most visited"
                            icon={Eye}
                            bookmarks={mostVisited}
                            emptyText="No visits recorded yet"
                        />
                    </div>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};

function StatCard({
    label,
    value,
    icon: Icon,
}: {
    label: string;
    value: number;
    icon: ComponentType<{ className?: string }>;
}) {
    return (
        <Card className="py-4">
            <CardContent className="flex items-center gap-3 px-4">
                <span className="flex size-9 shrink-0 items-center justify-center rounded-md bg-secondary text-secondary-foreground">
                    <Icon className="size-4" />
                </span>
                <div>
                    <p className="text-2xl leading-none font-semibold">
                        {value}
                    </p>
                    <p className="mt-1 text-xs text-muted-foreground">
                        {label}
                    </p>
                </div>
            </CardContent>
        </Card>
    );
}

function QuickList({
    title,
    icon: Icon,
    bookmarks,
    emptyText,
}: {
    title: string;
    icon: ComponentType<{ className?: string }>;
    bookmarks: BookmarkItem[];
    emptyText: string;
}) {
    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <CardTitle className="flex items-center gap-2 text-base">
                        <Icon className="size-4 text-muted-foreground" />
                        {title}
                    </CardTitle>
                    <Badge variant="secondary">{bookmarks.length}</Badge>
                </div>
            </CardHeader>
            <CardContent>
                {bookmarks.length === 0 ? (
                    <p className="text-sm text-muted-foreground">{emptyText}</p>
                ) : (
                    <div className="space-y-2">
                        {bookmarks.map((bookmark) => (
                            <BookmarkRow
                                key={bookmark.id}
                                bookmark={bookmark}
                            />
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function BookmarkRow({ bookmark }: { bookmark: BookmarkItem }) {
    return (
        <div className="flex items-start gap-3 rounded-lg border p-3">
            {bookmark.favicon ? (
                <img
                    src={bookmark.favicon}
                    alt=""
                    loading="lazy"
                    className="mt-0.5 size-6 shrink-0 rounded-sm object-contain"
                />
            ) : (
                <span className="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-sm bg-secondary">
                    <Link2 className="size-3.5" />
                </span>
            )}
            <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <a
                        href={bookmark.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="truncate text-sm font-medium hover:underline"
                    >
                        {bookmark.title}
                    </a>
                    {bookmark.pinned_at && (
                        <Pin className="size-3.5 text-primary" />
                    )}
                    {bookmark.favorited_at && (
                        <Heart className="size-3.5 fill-red-500 text-red-500" />
                    )}
                    {bookmark.is_broken && (
                        <Badge variant="destructive">Broken</Badge>
                    )}
                </div>
                <p className="truncate text-xs text-muted-foreground">
                    {bookmark.url}
                </p>
                {(bookmark.description !== null ||
                    bookmark.category !== null ||
                    bookmark.tags.length > 0) && (
                    <div className="mt-1.5 flex flex-wrap items-center gap-1.5">
                        {bookmark.category && (
                            <Badge variant="secondary">
                                {bookmark.category.name}
                            </Badge>
                        )}
                        {bookmark.tags.map((tag) => (
                            <Badge key={tag.id} variant="outline">
                                {tag.name}
                            </Badge>
                        ))}
                    </div>
                )}
            </div>
            {bookmark.visits_count > 0 && (
                <span className="flex shrink-0 items-center gap-1 text-xs text-muted-foreground">
                    <Eye className="size-3.5" />
                    {bookmark.visits_count}
                </span>
            )}
        </div>
    );
}

function flattenCategories(
    categories: CategoryItem[],
): { id: number; label: string }[] {
    const byParent = new Map<number | null, CategoryItem[]>();

    for (const category of categories) {
        const children = byParent.get(category.parent_id) ?? [];
        children.push(category);
        byParent.set(category.parent_id, children);
    }

    const flat: { id: number; label: string }[] = [];

    const walk = (parent: number | null, depth: number): void => {
        for (const category of byParent.get(parent) ?? []) {
            flat.push({
                id: category.id,
                label: `${'— '.repeat(depth)}${category.name}`,
            });
            walk(category.id, depth + 1);
        }
    };

    walk(null, 0);

    return flat;
}
