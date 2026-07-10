<?php

namespace App\Twill\Capsules\ContactLandings\Database\Seeds;

use Illuminate\Database\Seeder;
use App\Twill\Capsules\ContactLandings\Models\ContactLanding;
use App\Twill\Capsules\ContactLandings\Repositories\ContactLandingRepository;

class ContactLandingSeeder extends Seeder
{
    /**
     * Create the database record for this singleton module.
     *
     * @return void
     */
    public function run()
    {
        if (ContactLanding::count() > 0) {
            return;
        }

        app(ContactLandingRepository::class)->create([
            'title' => 'Contact us',
            'page_title' => 'Contact us',
            'published' => true,
        ]);
    }
}
