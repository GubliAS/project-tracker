<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_id', 'sprint_id', 'title', 'description', 'priority', 'status'])]
class BacklogItem extends Model
{
    //
}
