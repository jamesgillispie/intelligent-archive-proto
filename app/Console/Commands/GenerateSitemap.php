<?php

namespace App\Console\Commands;

use App\Tools\BuildSitemap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sitemap {--clear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml and indexes';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if ($this->option('clear') === true) {
            $cache = Cache::store('database');
            if (method_exists($cache, 'flush')) {
                $cache->flush();
            }
        }

        $this->info('Generating sitemap.xml and indexes...');

        (new BuildSitemap())->build();

        $this->info('Sitemap generated successfully.');
    }
}
