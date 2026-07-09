<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Support\EnvironmentValidationService;
use App\Services\Support\EnvironmentValidationException;

class ValidateEnvironment extends Command
{
    protected $signature = 'env:validate
        {--context=* : Contexts to validate (defaults to all contexts active for the current runtime)}';

    protected $description = 'Verify required environment variables are present (names only, never values)';

    public function handle(EnvironmentValidationService $validator): int
    {
        $contexts = $this->option('context') ?: $validator->runtimeContexts();

        try {
            $report = $validator->report($contexts);
        } catch (EnvironmentValidationException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Variable', 'Status'],
            collect($report)
                ->map(
                    fn(bool $present, string $name) => [
                        $name,
                        $present ? '<info>present</info>' : '<error>missing</error>',
                    ],
                )
                ->values()
                ->all(),
        );

        $missing = array_keys(array_filter($report, fn(bool $present) => !$present));

        if ($missing !== []) {
            $this->error('Missing: ' . implode(', ', $missing));

            return self::FAILURE;
        }

        $this->info(
            sprintf('All %d required variables present for contexts: %s.', count($report), implode(', ', $contexts)),
        );

        return self::SUCCESS;
    }
}
