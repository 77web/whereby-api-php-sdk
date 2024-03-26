<?php

declare(strict_types=1);

namespace Nanaweb\WherebyApi\Tests;

use Nanaweb\WherebyApi\Data\MeetingRequest;
use Nanaweb\WherebyApi\Exception\ApiException;
use Nanaweb\WherebyApi\Exception\AuthenticationException;
use Nanaweb\WherebyApi\Exception\YouAreRateLimitedException;
use Nanaweb\WherebyApi\Tool\SerializerFactory;
use Nanaweb\WherebyApi\WherebyApiClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WherebyApiClientTest extends TestCase
{
    private MockObject&HttpClientInterface $httpClientMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
    }

    public function testCreateMeeting(): void
    {
        $request = new MeetingRequest(
            endDate: new \DateTimeImmutable('+1 hour'),
            isLocked: true,
            fields: ['hostRoomUrl'],
        );
        $mockBody = <<<'EOB'
            {
              "meetingId": "1",
              "startDate": "2020-05-12T16:42:49Z",
              "endDate": "2020-05-12T17:42:49Z",
              "roomUrl": "https://subdomain.whereby.com/dda1beca-af37-11eb-ac88-372b6869f077",
              "hostRoomUrl": "https://subdomain.whereby.com/host/dda1beca-af37-11eb-ac88-372b6869f077"
            }
            EOB;
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(201)
        ;
        $responseMock->expects($this->once())
            ->method('getContent')
            ->willReturn($mockBody)
        ;

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with('POST', '/v1/meetings', $this->callback(static fn (array $options): bool => !empty($options['body'])))
            ->willReturn($responseMock)
        ;

        $actual = $this->getSUT()->createMeeting($request);
        $this->assertSame('https://subdomain.whereby.com/dda1beca-af37-11eb-ac88-372b6869f077', $actual->roomUrl);
        $this->assertSame('https://subdomain.whereby.com/host/dda1beca-af37-11eb-ac88-372b6869f077', $actual->hostRoomUrl);
        $this->assertSame('2020-05-12 17:42:49', $actual->endDate->format('Y-m-d H:i:s'));
    }

    public function testGetMeeting(): void
    {
        $mockBody = <<<'EOB'
            {
              "meetingId": "1",
              "startDate": "2020-05-12T16:42:49Z",
              "endDate": "2020-05-12T17:42:49Z",
              "roomUrl": "https://subdomain.whereby.com/dda1beca-af37-11eb-ac88-372b6869f077",
              "hostRoomUrl": "https://subdomain.whereby.com/host/dda1beca-af37-11eb-ac88-372b6869f077"
            }
            EOB;
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200)
        ;
        $responseMock->expects($this->once())
            ->method('getContent')
            ->willReturn($mockBody)
        ;

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', '/v1/meetings/dummy-id')
            ->willReturn($responseMock)
        ;

        $actual = $this->getSUT()->getMeeting('dummy-id');
        $this->assertSame('https://subdomain.whereby.com/dda1beca-af37-11eb-ac88-372b6869f077', $actual->roomUrl);
        $this->assertSame('https://subdomain.whereby.com/host/dda1beca-af37-11eb-ac88-372b6869f077', $actual->hostRoomUrl);
        $this->assertSame('2020-05-12 17:42:49', $actual->endDate->format('Y-m-d H:i:s'));
    }

    public function testDeleteMeeting(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(204)
        ;

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with('DELETE', '/v1/meetings/dummy-id')
            ->willReturn($responseMock)
        ;

        $this->getSUT()->deleteMeeting('dummy-id');
    }

    public function testGetMeetingRateLimited(): void
    {
        $this->expectException(YouAreRateLimitedException::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(429)
        ;

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', '/v1/meetings/dummy-id')
            ->willReturn($responseMock)
        ;

        $this->getSUT()->getMeeting('dummy-id');
    }

    public function testGetMeetingUnauthenticated(): void
    {
        $this->expectException(AuthenticationException::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(401)
        ;

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', '/v1/meetings/dummy-id')
            ->willReturn($responseMock)
        ;

        $this->getSUT()->getMeeting('dummy-id');
    }

    public function testGetMeetingOtherError(): void
    {
        $this->expectException(ApiException::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(404)
        ;
        $responseMock->expects($this->once())
            ->method('getContent')
            ->with(false)
            ->willReturn('not found')
        ;

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with('GET', '/v1/meetings/dummy-id')
            ->willReturn($responseMock)
        ;

        $this->getSUT()->getMeeting('dummy-id');
    }

    private function getSUT(): WherebyApiClient
    {
        $this->httpClientMock->expects($this->once())
            ->method('withOptions')
            ->willReturnSelf()
        ;

        return new WherebyApiClient(
            'dummy-api-key',
            (new SerializerFactory())->create(),
            $this->httpClientMock,
        );
    }
}
