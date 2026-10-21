<?php

declare(strict_types=1);

namespace SCM\Quota;

final class QuotaExceededException extends \RuntimeException
{
    public function __construct(
        public readonly string $metric,
        public readonly int $limit,
        public readonly int $current,
    ) {
        parent::__construct(sprintf(
            'Quota dépassé pour "%s" (%d/%d).',
            $metric,
            $current,
            $limit
        ));
    }
}
