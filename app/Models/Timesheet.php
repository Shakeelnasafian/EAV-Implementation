<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timesheet extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'task_name',
        'date',
        'hours',
        'user_id',
        'project_id',
    ];

    /**
     * The user who logged the timesheet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The project the timesheet is associated with.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
