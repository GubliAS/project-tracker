<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_id', 'name', 'email', 'role', 'influence'])]
class Stakeholder extends Model
{
    //
}
