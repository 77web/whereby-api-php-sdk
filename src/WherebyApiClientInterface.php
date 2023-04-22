<?php

declare(strict_types=1);

namespace Nanaweb\WherebyApi;

use Nanaweb\WherebyApi\Data\MeetingRequest;
use Nanaweb\WherebyApi\Data\MeetingResponse;
use Nanaweb\WherebyApi\Exception\ApiException;

interface WherebyApiClientInterface
{
    /**
     * @throws ApiException
     */
    public function createMeeting(MeetingRequest $request): MeetingResponse;

    /**
     * @throws ApiException
     */
    public function deleteMeeting(string $meetingId): void;

    /**
     * @throws ApiException
     */
    public function getMeeting(string $meetingId): MeetingResponse;
}
