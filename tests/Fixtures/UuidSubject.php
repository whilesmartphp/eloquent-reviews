<?php

namespace Whilesmart\Reviews\Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Whilesmart\Reviews\Traits\Reviewable;

class UuidSubject extends Model
{
    use HasUuids, Reviewable;

    protected $table = 'revision_subjects';

    protected $guarded = [];
}
