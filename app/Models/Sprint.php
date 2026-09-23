<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_id', 'name', 'start_date', 'end_date', 'status'])]
class Sprint extends Model
{
    //
}
