@props(['filters', 'filterOptions'])

<form class="border-dark-soft flex flex-wrap items-end gap-12 border-b pb-20" method="GET"
    action="{{ route('archive.artworks.index') }}">
    <label class="flex flex-col gap-4">
        <span class="f-ui-01 opacity-60">Search</span>
        <input class="f-ui-01 border-dark-soft border bg-transparent px-10 py-6" name="q" type="search"
            value="{{ $filters['q'] ?? '' }}" placeholder="Title or artist" />
    </label>

    <label class="flex flex-col gap-4">
        <span class="f-ui-01 opacity-60">Artist</span>
        <select class="f-ui-01 border-dark-soft border bg-transparent px-10 py-6" name="artist">
            <option value="">All artists</option>
            @foreach ($filterOptions['artists'] as $artist)
                <option value="{{ $artist->id }}" @selected(($filters['artist'] ?? null) == $artist->id)>
                    {{ $artist->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="flex flex-col gap-4">
        <span class="f-ui-01 opacity-60">Classification</span>
        <select class="f-ui-01 border-dark-soft border bg-transparent px-10 py-6" name="classification">
            <option value="">All</option>
            @foreach ($filterOptions['classifications'] as $classification)
                <option value="{{ $classification->id }}" @selected(($filters['classification'] ?? null) == $classification->id)>
                    {{ $classification->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="flex flex-col gap-4">
        <span class="f-ui-01 opacity-60">Department</span>
        <select class="f-ui-01 border-dark-soft border bg-transparent px-10 py-6" name="department">
            <option value="">All</option>
            @foreach ($filterOptions['departments'] as $department)
                <option value="{{ $department->id }}" @selected(($filters['department'] ?? null) == $department->id)>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="flex flex-col gap-4">
        <span class="f-ui-01 opacity-60">Medium</span>
        <select class="f-ui-01 border-dark-soft border bg-transparent px-10 py-6" name="medium">
            <option value="">All</option>
            @foreach ($filterOptions['mediums'] as $medium)
                <option value="{{ $medium->id }}" @selected(($filters['medium'] ?? null) == $medium->id)>
                    {{ Str::limit($medium->name, 40) }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="flex flex-col gap-4">
        <span class="f-ui-01 opacity-60">From year</span>
        <input class="f-ui-01 border-dark-soft w-[90px] border bg-transparent px-10 py-6" name="year_from"
            type="number" value="{{ $filters['year_from'] ?? '' }}" min="1000" max="2100" />
    </label>

    <label class="flex flex-col gap-4">
        <span class="f-ui-01 opacity-60">To year</span>
        <input class="f-ui-01 border-dark-soft w-[90px] border bg-transparent px-10 py-6" name="year_to" type="number"
            value="{{ $filters['year_to'] ?? '' }}" min="1000" max="2100" />
    </label>

    <button class="f-ui-01 border border-black px-16 py-6" type="submit">Filter</button>

    @if (array_filter($filters))
        <a class="f-ui-01 underline opacity-60" href="{{ route('archive.artworks.index') }}">Clear</a>
    @endif
</form>
