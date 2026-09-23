<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_id', 'title', 'scheduled_at', 'agenda'])]
class Kickoff extends Model
{
    //
}
