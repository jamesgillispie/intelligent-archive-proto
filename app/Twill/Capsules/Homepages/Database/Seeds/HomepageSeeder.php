<?php

namespace App\Twill\Capsules\Homepages\Database\Seeds;

use Illuminate\Database\Seeder;
use App\Twill\Capsules\Homepages\Models\Homepage;
use App\Twill\Capsules\Homepages\Repositories\HomepageRepository;

class HomepageSeeder extends Seeder
{
    /**
     * Create the database record for this singleton module.
     *
     * @return void
     */
    public function run()
    {
        if (Homepage::count() > 0) {
            return;
        }

        app(HomepageRepository::class)->create([
            'title' => 'Homepage',
            'description' => '',
            'published' => true,
        ]);
    }
}
