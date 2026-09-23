<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['version', 'title', 'requestor', 'description', 'type', 'approval_status', 'impact', 'release_date'])]
class Changelog extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }
}
