<?php

declare(strict_types=1);

namespace Nanaweb\WherebyApi\Data;

readonly class MeetingResponse
{
    public function __construct(
        public string $meetingId = '',
        public string $roomName = '',
        public string $roomUrl = '',
        public \DateTimeImmutable $startDate = new \DateTimeImmutable(),
        public \DateTimeImmutable $endDate = new \DateTimeImmutable(),
        public ?string $hostRoomUrl = null,
        public ?string $viewerRoomUrl = null,
    ) {
    }
}
