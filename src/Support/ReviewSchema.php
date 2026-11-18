<?php

namespace Whilesmart\Reviews\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReviewSchema
{
    public static function useStringIdentifiers(): void
    {
        Schema::table(config('reviews.table', 'reviews'), function (Blueprint $table) {
            $table->string('reviewable_id')->change();
            $table->string('reviewer_id')->nullable()->change();
        });
    }
}
