<?php

namespace App\Twill\Capsules\Departments\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use App\Twill\Capsules\Departments\Models\Department;

class DepartmentRepository extends ModuleRepository
{
    use HandleSlugs;
    use HandleRevisions;

    public function __construct(Department $model)
    {
        $this->model = $model;
    }
}
