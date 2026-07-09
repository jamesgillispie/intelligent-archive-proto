@php
    /** @var \App\Twill\Capsules\Artworks\Models\Artwork|null $item */
    $artwork = $item ?? null;
@endphp

@if ($artwork?->exists)
    <div class="input">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 6px 8px;">Field</th>
                    <th style="text-align: left; padding: 6px 8px;">Imported value</th>
                    <th style="text-align: left; padding: 6px 8px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($artwork->fieldProvenance() as $field => $provenance)
                    <tr style="border-top: 1px solid #e5e5e5;">
                        <td style="padding: 6px 8px; white-space: nowrap;">{{ Str::headline($field) }}</td>
                        <td style="padding: 6px 8px;">{{ $provenance['source_value'] ?? '—' }}</td>
                        <td style="padding: 6px 8px;">
                            {{ $provenance['overridden'] ? 'Overridden editorially' : 'As imported' }}
                        </td>
                    </tr>
                @endforeach
                <tr style="border-top: 1px solid #e5e5e5;">
                    <td style="padding: 6px 8px;">NGA object ID</td>
                    <td style="padding: 6px 8px;">{{ $artwork->nga_object_id }}</td>
                    <td style="padding: 6px 8px;">Identity — never editable</td>
                </tr>
                <tr style="border-top: 1px solid #e5e5e5;">
                    <td style="padding: 6px 8px;">Last synced</td>
                    <td style="padding: 6px 8px;">{{ $artwork->last_synced_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td style="padding: 6px 8px;">Import pipeline</td>
                </tr>
            </tbody>
        </table>
    </div>
@else
    <p>Source values appear once the record has been imported.</p>
@endif
