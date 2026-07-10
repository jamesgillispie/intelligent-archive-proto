<?php

namespace App\Twill\Capsules\AboutLandings\Database\Seeds;

use Illuminate\Database\Seeder;
use App\Twill\Capsules\AboutLandings\Models\AboutLanding;
use App\Twill\Capsules\AboutLandings\Repositories\AboutLandingRepository;

class AboutLandingSeeder extends Seeder
{
    /**
     * Create the database record for this singleton module.
     *
     * @return void
     */
    public function run()
    {
        if (AboutLanding::count() > 0) {
            return;
        }

        app(AboutLandingRepository::class)->create([
            'title' => 'About',
            'published' => true,
        ]);
    }
}
