<?php

declare(strict_types=1);

namespace Nanaweb\WherebyApi\Data;

readonly class MeetingRequest
{
    public function __construct(
        public \DateTimeImmutable $endDate,
        public ?bool $isLocked = null,
        public ?string $roomNamePattern = null,
        public ?string $roomNamePrefix = null,
        public ?string $roomMode = null,
        public ?array $fields = null,
        // TODO recording, streaming
        public ?string $templateType = null,
    ) {
    }
}
