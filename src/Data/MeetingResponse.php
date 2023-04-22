<?php

declare(strict_types=1);

namespace Nanaweb\WherebyApi\Data;

readonly class MeetingResponse
{
    public function __construct(
        public string $meetingId,
        public string $roomName,
        public string $roomUrl,
        public \DateTimeImmutable $startDate,
        public \DateTimeImmutable $endDate,
        public ?string $hostRoomUrl = null,
        public ?string $viewerRoomUrl = null
    ) {
    }
}