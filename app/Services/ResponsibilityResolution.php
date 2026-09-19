<?php

namespace App\Services;

use App\Models\User;

class ResponsibilityResolution
{
    public const Resolved = 'resolved';

    public const NoResponsibleUser = 'no_responsible_user';

    public const AmbiguousResponsibility = 'ambiguous_responsibility';

    public function __construct(
        public readonly ?User $user,
        public readonly string $status,
        public readonly string $source,
        public readonly ?string $reason = null,
    ) {}

    public function isResolved(): bool
    {
        return $this->status === self::Resolved && $this->user !== null;
    }
}
