<?php

namespace App\Twill\Capsules\Departments\Models;

use A17\Twill\Models\Revision;

/**
 * @mixin IdeHelperDepartmentRevision
 */
class DepartmentRevision extends Revision
{
    protected $table = 'department_revisions';
}
