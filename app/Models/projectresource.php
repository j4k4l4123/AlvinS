<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectResource extends Model
{
    protected $fillable = [
        'task_name',
        'task_code',
        'resource_name',
        'resource_category',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'notes',
    ];
}
