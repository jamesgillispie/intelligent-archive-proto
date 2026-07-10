<?php

namespace App\Support\IdeHelper;

use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasTranslation;
use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Barryvdh\LaravelIdeHelper\Contracts\ModelHookInterface;
use Illuminate\Database\Eloquent\Model;


class TwillModelHook implements ModelHookInterface
{
    public function run(ModelsCommand $command, Model $model): void
    {
        $this->handleTranslations($command, $model);
        $this->handleSlugs($command, $model);
    }

    private function handleTranslations(ModelsCommand $command, Model $model): void
    {
        if (in_array(HasTranslation::class, class_uses_recursive($model))) {
            $translatedAttributes = method_exists($model, 'getTranslatedAttributes') ? $model->getTranslatedAttributes() : [];
            foreach ($translatedAttributes as $attribute) {
                $command->setProperty($attribute, 'string', true, true);
            }
        }
    }

    private function handleSlugs(ModelsCommand $command, Model $model): void
    {
        if (in_array(HasSlug::class, class_uses_recursive($model))) {
            $command->setProperty('slug', 'string', true, true);
        }
    }
}
