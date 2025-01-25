<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Bundle extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'description', 'size', 'file_path', 'application_id'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function uniqueIds()
    {
        return ['uuid'];
    }
}
