<?php

declare(strict_types=1);

namespace Nanaweb\WherebyApi;

use Nanaweb\WherebyApi\Exception\ApiException;

interface WherebyApiClientInterface
{
    /**
     * @throws ApiException
     */
    public function createMeeting(Data\MeetingRequest $request): Data\MeetingResponse;

    /**
     * @throws ApiException
     */
    public function deleteMeeting(string $meetingId): void;

    /**
     * @throws ApiException
     */
    public function getMeeting(string $meetingId): Data\MeetingResponse;
}
