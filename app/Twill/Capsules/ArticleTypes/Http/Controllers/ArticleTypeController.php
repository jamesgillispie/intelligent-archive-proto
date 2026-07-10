<?php

namespace App\Twill\Capsules\ArticleTypes\Http\Controllers;

use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

class ArticleTypeController extends BaseModuleController
{
    protected $moduleName = 'articleTypes';

    protected function setUpController(): void
    {
        // $this->enableReorder();
        $this->enableEditInModal();
        $this->disablePermalink();
    }
}
