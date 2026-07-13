<?php

namespace App\Services\Nexus\Contracts;

use App\Services\Nexus\Artifacts\ArtworkDocument;

/**
 * Write side of the archive knowledge layer (Pinecone Nexus).
 *
 * Vendor API rule (CLAUDE.md): interface-level only — no request/response
 * shapes, SDK calls, or endpoint assumptions until official vendor docs
 * are provided in a session. Called only by queued jobs in
 * app/Jobs/Indexing; application code never talks to the vendor directly.
 */
interface NexusIndexer
{
    /**
     * Create or replace the indexed document for one artwork. Idempotent:
     * the same document twice leaves one indexed unit.
     */
    public function upsertArtwork(ArtworkDocument $document): void;

    /**
     * Drop an artwork from the index (unpublished, no longer displayable,
     * or deleted). Safe to call for artworks that were never indexed.
     */
    public function removeArtwork(int $artworkId): void;
}
