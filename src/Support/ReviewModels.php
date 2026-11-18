<?php

namespace Whilesmart\Reviews\Support;

use InvalidArgumentException;
use Whilesmart\Reviews\Models\Review;
use Whilesmart\Reviews\Models\ReviewRevision;

class ReviewModels
{
    public static function review(): string
    {
        return self::resolve('review', Review::class);
    }

    public static function revision(): string
    {
        return self::resolve('revision', ReviewRevision::class);
    }

    private static function resolve(string $key, string $base): string
    {
        $class = config('reviews.models.'.$key, $base);
        if (! is_string($class) || ! is_a($class, $base, true)) {
            throw new InvalidArgumentException('[reviews.models.'.$key.'] must extend '.$base.'.');
        }

        return $class;
    }
}
