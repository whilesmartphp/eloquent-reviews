<?php

namespace Whilesmart\Reviews\Enums;

enum ReviewStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case FLAGGED = 'flagged';
}
