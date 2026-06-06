<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bundle extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'description', 'size', 'file_path', 'application_id', 'channel_id'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function uniqueIds()
    {
        return ['uuid'];
    }
}
