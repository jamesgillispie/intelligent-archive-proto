<?php

namespace App\Models\Behaviors;

/**
 * Field provenance for canonical archive models (CLAUDE.md: distinguish
 * source-imported fields from editorial overrides).
 *
 * Convention: for every field name in $sourceMappedFields the table has a
 * display column `{field}` (edited in Twill) and a `source_{field}` column
 * (written only by the import pipeline). A field counts as overridden when
 * its display value no longer matches the imported value; re-imports refresh
 * `source_{field}` always, and the display value only while not overridden —
 * an editor's work is never clobbered by a refresh.
 */
trait HasSourceFields
{
    /** @return array<int, string> */
    public function sourceMappedFields(): array
    {
        return $this->sourceMappedFields ?? [];
    }

    public function isOverridden(string $field): bool
    {
        $source = $this->getAttribute("source_{$field}");

        return $source !== null && $this->getAttribute($field) !== $source;
    }

    /**
     * Apply a freshly imported value: source column always updates, the
     * display column follows only when the editor has not overridden it.
     */
    public function applySourceValue(string $field, ?string $value): void
    {
        $previousSource = $this->getAttribute("source_{$field}");
        $current = $this->getAttribute($field);

        $this->setAttribute("source_{$field}", $value);

        if ($current === null || $current === $previousSource) {
            $this->setAttribute($field, $value);
        }
    }

    /**
     * Per-field provenance for display (source citation panel, CMS partial).
     *
     * @return array<string, array{value: ?string, source_value: ?string, overridden: bool}>
     */
    public function fieldProvenance(): array
    {
        $provenance = [];

        foreach ($this->sourceMappedFields() as $field) {
            $provenance[$field] = [
                'value' => $this->getAttribute($field),
                'source_value' => $this->getAttribute("source_{$field}"),
                'overridden' => $this->isOverridden($field),
            ];
        }

        return $provenance;
    }
}
