<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Book extends Model
{
    use HasFactory;

    CONST TYPE_PAPERBACK = 1;
    CONST TYPE_HARDCOVER = 2;
    CONST TYPE_SPIRAL_BOUND = 3;
    CONST TYPE_LEATHER_BOUND = 4;
    CONST TYPE_EBOOK = 5;
    CONST TYPE_AUDIOBOOK = 6;

    public static $types = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'type',
        'category_id',
        'manufacturer_id',
        'summary',
        'price',
        'in_stock',
    ];
    
    public static function initializeTypes()
    {
        self::$types = [
            [
                'id' => self::TYPE_PAPERBACK,
                'name' => 'Paperback',
            ],
            [
                'id' => self::TYPE_HARDCOVER,
                'name' => 'Hardcover',
            ],
            [
                'id' => self::TYPE_SPIRAL_BOUND,
                'name' => 'Spiral bound',
            ],
            [
                'id' => self::TYPE_LEATHER_BOUND,
                'name' => 'Leather bound',
            ],
            [
                'id' => self::TYPE_EBOOK,
                'name' => 'E-book',
            ],
            [
                'id' => self::TYPE_AUDIOBOOK,
                'name' => 'Audio book',
            ],
        ];
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * @return HasOne
     */
    public function photo(): HasOne
    {
        return $this->hasOne(Photo::class);
    }
}
