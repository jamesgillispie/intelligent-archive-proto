<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/*
 * objects_constituents source records were keyed {objectid}:{constituentid},
 * but NGA ships multiple join rows per pair differing only in role text, so
 * those rows fought over one record and reported "updated" on every import.
 * The key now includes the role: {objectid}:{constituentid}:{role}.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::table('source_records')
            ->where('source_table', 'objects_constituents')
            ->update([
                'source_id' => DB::raw("source_id || ':' || coalesce(payload->>'role', '')"),
            ]);
    }

    public function down(): void
    {
        // Collapse back to pair keys. Siblings that only differ by role would
        // collide with the unique (source_table, source_id) index, so keep the
        // most recently seen row per pair and drop the rest — the exact
        // pre-migration state (one arbitrary winner per pair).
        DB::statement(
            <<<'SQL'
                DELETE FROM source_records a
                USING source_records b
                WHERE a.source_table = 'objects_constituents'
                  AND b.source_table = 'objects_constituents'
                  AND split_part(a.source_id, ':', 1) = split_part(b.source_id, ':', 1)
                  AND split_part(a.source_id, ':', 2) = split_part(b.source_id, ':', 2)
                  AND a.id < b.id
            SQL
            ,
        );

        DB::table('source_records')
            ->where('source_table', 'objects_constituents')
            ->update([
                'source_id' => DB::raw("split_part(source_id, ':', 1) || ':' || split_part(source_id, ':', 2)"),
            ]);
    }
};
