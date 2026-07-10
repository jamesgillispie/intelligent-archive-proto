@extends('twill::layouts.free')

@section('customPageContent')
    <style>
        .imports {
            padding: 40px 0 80px;
            font-size: 14px;
        }

        .imports h1 {
            font-size: 22px;
            margin-bottom: 8px;
        }

        .imports h2 {
            font-size: 16px;
            margin: 40px 0 12px;
        }

        .imports p.intro {
            color: #757575;
            margin-bottom: 24px;
        }

        .imports table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border: 1px solid #e9e9e9;
        }

        .imports th,
        .imports td {
            text-align: left;
            vertical-align: top;
            padding: 10px 14px;
            border-bottom: 1px solid #e9e9e9;
        }

        .imports th {
            font-weight: 600;
            background: #fafafa;
        }

        .imports .num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .imports .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
        }

        .imports .status--completed {
            background: #e4f2e4;
            color: #2b6b2b;
        }

        .imports .status--failed {
            background: #f7e3e3;
            color: #93312e;
        }

        .imports .status--running {
            background: #eef2f7;
            color: #35578a;
        }

        .imports .muted {
            color: #757575;
        }

        .imports .summary {
            display: flex;
            gap: 32px;
            margin-bottom: 8px;
        }

        .imports .summary strong {
            display: block;
            font-size: 22px;
            font-weight: 600;
        }
    </style>

    <div class="imports">
        <h1>Import batches</h1>
        <p class="intro">
            Every NGA Open Data import run, with what it changed. Source records are raw and read-only;
            editorial changes live on the artwork records themselves.
        </p>

        <h2>Primary images</h2>
        <div class="summary">
            <div><strong>{{ $imageSummary['downloaded'] }}</strong> <span class="muted">downloaded</span></div>
            <div><strong>{{ $imageSummary['pending'] }}</strong> <span class="muted">pending</span></div>
            <div><strong>{{ $imageSummary['failed'] }}</strong> <span class="muted">failed</span></div>
        </div>

        <h2>Batches</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Started</th>
                    <th>Status</th>
                    <th class="num">Duration</th>
                    <th class="num">Selected</th>
                    <th class="num">Created</th>
                    <th class="num">Updated</th>
                    <th class="num">Unchanged</th>
                    <th class="num">Duplicates in source</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($batches as $batch)
                    @php
                        $records = collect($batch->report['source_records'] ?? []);
                        $sum = fn(string $key) => $records->sum(fn(array $counts) => $counts[$key] ?? 0);
                        $duration =
                            $batch->started_at && $batch->finished_at
                                ? $batch->finished_at->diffForHumans($batch->started_at, ['syntax' => 1, 'parts' => 2])
                                : null;
                    @endphp
                    <tr>
                        <td>{{ $batch->id }}</td>
                        <td>{{ $batch->started_at?->format('Y-m-d H:i') }}</td>
                        <td><span class="status status--{{ $batch->status }}">{{ $batch->status }}</span></td>
                        <td class="num">{{ $duration ?? '—' }}</td>
                        <td class="num">{{ $batch->report['totals']['objects_selected'] ?? '—' }}</td>
                        <td class="num">{{ $sum('created') }}</td>
                        <td class="num">{{ $sum('updated') }}</td>
                        <td class="num">{{ $sum('unchanged') }}</td>
                        <td class="num">{{ $sum('duplicate_in_source') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="muted" colspan="9">No imports have run yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h2>Flagged images ({{ $imageSummary['failed'] }})</h2>
        @if ($flaggedImages->isEmpty())
            <p class="muted">No image downloads have failed.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Artwork</th>
                        <th>NGA object</th>
                        <th>Image uuid</th>
                        <th>Reason</th>
                        <th>Flagged</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($flaggedImages as $image)
                        <tr>
                            <td>
                                @if ($image->artwork)
                                    <a href="{{ route('twill.artworks.edit', $image->artwork_id) }}">
                                        {{ $image->artwork->title }}
                                    </a>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>{{ $image->artwork?->nga_object_id }}</td>
                            <td>{{ $image->nga_uuid }}</td>
                            <td>{{ $image->failure_reason }}</td>
                            <td>{{ $image->validated_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="muted" style="margin-top: 8px;">
                Retry from the command line: <code>php artisan nga:images --retry-failed</code>
            </p>
        @endif
    </div>
@stop
