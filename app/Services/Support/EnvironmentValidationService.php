<?php

namespace App\Services\Support;

/**
 * Validates that required environment-backed config values are present.
 *
 * Reports variable NAMES only — never values (CLAUDE.md secrets rule).
 * Reads through config() so it works with cached config; the env var name
 * is documentation for the operator, the config key is what is checked.
 */
class EnvironmentValidationService
{
    /**
     * Validation contexts. Each maps config key => env variable name.
     *
     * @var array<string, array<string, string>>
     */
    protected array $contexts = [
        'base' => [
            'app.key' => 'APP_KEY',
            'app.url' => 'APP_URL',
            'database.connections.pgsql.database' => 'DB_DATABASE',
            'database.connections.pgsql.username' => 'DB_USERNAME',
        ],
        'nga-import' => [
            'nga.open_data_path' => 'NGA_OPEN_DATA_PATH',
            'nga.import.limit' => 'NGA_IMPORT_LIMIT',
            'nga.import.classification' => 'NGA_IMPORT_CLASSIFICATION',
        ],
        'nga-fetch' => [
            'nga.fetch.repo' => 'NGA_FETCH_REPO',
            'nga.fetch.branch' => 'NGA_FETCH_BRANCH',
            'nga.fetch.raw_base_url' => 'NGA_FETCH_RAW_BASE_URL',
            'nga.fetch.api_base_url' => 'NGA_FETCH_API_BASE_URL',
            'nga.fetch.snapshots_path' => 'NGA_FETCH_SNAPSHOTS_PATH',
        ],
        'nexus' => [
            'nexus.driver' => 'NEXUS_DRIVER',
            'nexus.index_name' => 'NEXUS_INDEX_NAME',
        ],
        'nexus-live' => [
            'nexus.api_key' => 'NEXUS_API_KEY',
            'nexus.endpoint' => 'NEXUS_ENDPOINT',
        ],
        'redis-iris' => [
            'redis_iris.driver' => 'REDIS_IRIS_DRIVER',
            'redis_iris.key_prefix' => 'REDIS_IRIS_KEY_PREFIX',
        ],
        'redis-iris-live' => [
            'redis_iris.url' => 'REDIS_IRIS_URL',
            'redis_iris.api_key' => 'REDIS_IRIS_API_KEY',
        ],
        'memory' => [
            'memory.consent_version' => 'MEMORY_CONSENT_VERSION',
            'memory.anonymous_explore_rate_limit' => 'MEMORY_ANON_EXPLORE_RATE_LIMIT',
            'memory.anonymous_context_ttl_minutes' => 'MEMORY_ANON_CONTEXT_TTL_MINUTES',
        ],
        'trails' => [
            'trails.max_per_user' => 'TRAILS_MAX_PER_USER',
        ],
    ];

    /**
     * Contexts that apply to the current runtime, derived from driver config.
     *
     * @return array<int, string>
     */
    public function runtimeContexts(): array
    {
        $contexts = ['base', 'nga-import', 'nexus', 'redis-iris', 'memory', 'trails'];

        if (config('nexus.driver') === 'live') {
            $contexts[] = 'nexus-live';
        }

        if (config('redis_iris.driver') === 'live') {
            $contexts[] = 'redis-iris-live';
        }

        return $contexts;
    }

    /**
     * @return array<int, string> known context names
     */
    public function availableContexts(): array
    {
        return array_keys($this->contexts);
    }

    /**
     * Env variable names (never values) missing for the given contexts.
     *
     * @param  array<int, string>  $contexts
     * @return array<int, string>
     */
    public function missing(array $contexts): array
    {
        $missing = [];

        foreach ($this->requirements($contexts) as $configKey => $envName) {
            $value = config($configKey);

            if ($value === null || $value === '') {
                $missing[] = $envName;
            }
        }

        return $missing;
    }

    /**
     * Presence map for reporting: env variable name => present?
     *
     * @param  array<int, string>  $contexts
     * @return array<string, bool>
     */
    public function report(array $contexts): array
    {
        $report = [];

        foreach ($this->requirements($contexts) as $configKey => $envName) {
            $value = config($configKey);

            $report[$envName] = !($value === null || $value === '');
        }

        return $report;
    }

    /**
     * @param  array<int, string>  $contexts
     *
     * @throws EnvironmentValidationException when any required variable is missing
     */
    public function validate(array $contexts): void
    {
        $missing = $this->missing($contexts);

        if ($missing !== []) {
            throw EnvironmentValidationException::missingVariables($missing);
        }
    }

    /**
     * @param  array<int, string>  $contexts
     * @return array<string, string> config key => env variable name
     */
    protected function requirements(array $contexts): array
    {
        $requirements = [];

        foreach ($contexts as $context) {
            if (!array_key_exists($context, $this->contexts)) {
                throw EnvironmentValidationException::unknownContext($context, $this->availableContexts());
            }

            $requirements += $this->contexts[$context];
        }

        return $requirements;
    }
}
