<?php

declare(strict_types=1);

namespace Nanaweb\WherebyApi;

use Nanaweb\WherebyApi\Data\MeetingRequest;
use Nanaweb\WherebyApi\Data\MeetingResponse;
use Nanaweb\WherebyApi\Exception\ApiException;
use Nanaweb\WherebyApi\Exception\AuthenticationException;
use Nanaweb\WherebyApi\Exception\YouAreRateLimitedException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://whereby.dev/http-api/
 */
class WherebyApiClient implements WherebyApiClientInterface
{
    private const string BASE_URI = 'https://api.whereby.dev';

    private readonly HttpClientInterface $httpClient;

    public function __construct(
        string $apiKey,
        private readonly SerializerInterface $serializer,
        HttpClientInterface $httpClient,
    ) {
        $this->httpClient = $httpClient->withOptions([
            'auth_bearer' => $apiKey,
            'base_uri' => self::BASE_URI,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * @throws ApiException
     */
    #[\Override]
    public function createMeeting(MeetingRequest $request): MeetingResponse
    {
        $response = $this->makeRequest('POST', '/v1/meetings', [
            'body' => $this->serializer->serialize($request, 'json'),
        ], 201);

        return $this->serializer->deserialize($response->getContent(), MeetingResponse::class, 'json');
    }

    /**
     * @throws ApiException
     */
    #[\Override]
    public function deleteMeeting(string $meetingId): void
    {
        $this->makeRequest('DELETE', '/v1/meetings/' . $meetingId, [], 204);
    }

    /**
     * @throws ApiException
     */
    #[\Override]
    public function getMeeting(string $meetingId): MeetingResponse
    {
        $response = $this->makeRequest('GET', '/v1/meetings/' . $meetingId, [], 200);

        return $this->serializer->deserialize($response->getContent(), MeetingResponse::class, 'json');
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws ApiException
     */
    private function makeRequest(string $method, string $path, array $options, int $expectedStatusCode): ResponseInterface
    {
        try {
            $response = $this->httpClient->request($method, $path, $options);

            if ($response->getStatusCode() !== $expectedStatusCode) {
                throw $this->handleErrorResponse($response);
            }
        } catch (TransportExceptionInterface $e) {
            throw new ApiException($e->getMessage());
        }

        return $response;
    }

    /**
     * @throws AuthenticationException
     * @throws YouAreRateLimitedException
     */
    private function handleErrorResponse(ResponseInterface $response): ApiException
    {
        if ($response->getStatusCode() === 429) {
            // TODO retrieve time and contain them into Exception
            throw new YouAreRateLimitedException();
        } elseif ($response->getStatusCode() === 401) {
            throw new AuthenticationException();
        }

        return new ApiException($response->getContent(false), $response->getStatusCode());
    }
}
