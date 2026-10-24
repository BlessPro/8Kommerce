<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    protected $fillable = ['user_id', 'name', 'normalized_name'];

    public static function normalize(string $name): string
    {
        return Str::of($name)->squish()->lower()->toString();
    }

    public static function remember(int $userId, ?string $name): ?self
    {
        $name = Str::of((string) $name)->squish()->limit(80, '')->toString();
        if ($name === '') {
            return null;
        }

        return self::firstOrCreate(
            ['user_id' => $userId, 'normalized_name' => self::normalize($name)],
            ['name' => $name],
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
