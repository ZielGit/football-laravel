<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\AccountDisabledException;
use App\Exceptions\ApiErrorRenderer;
use App\Exceptions\InvalidCredentialsException;
use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use App\Football\Domain\Exception\ExternalServiceUnavailable;
use App\Football\Domain\Exception\PlayerNotFound;
use App\Football\Domain\Exception\TeamNotFound;
use Illuminate\Auth\AuthenticationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

final class ApiErrorRendererTest extends TestCase
{
    /** @return iterable<string, array{Throwable, int, string}> */
    public static function cases(): iterable
    {
        yield 'equipo inexistente' => [new TeamNotFound(1), 404, 'TEAM_NOT_FOUND'];
        yield 'jugador inexistente' => [new PlayerNotFound(1), 404, 'PLAYER_NOT_FOUND'];
        yield 'sin sesión' => [new AuthenticationException, 401, 'UNAUTHENTICATED'];
        yield 'credenciales' => [new InvalidCredentialsException, 401, 'INVALID_CREDENTIALS'];
        yield 'cuenta desactivada' => [new AccountDisabledException, 403, 'ACCOUNT_DISABLED'];
        yield 'sin permiso' => [new AccessDeniedHttpException, 403, 'FORBIDDEN'];
        yield 'ruta inexistente' => [new NotFoundHttpException, 404, 'NOT_FOUND'];
        yield 'límite propio' => [new TooManyRequestsHttpException(30), 429, 'TOO_MANY_REQUESTS'];
        yield 'límite football-data' => [new ExternalRateLimitExceeded(30), 429, 'EXTERNAL_RATE_LIMIT'];
        yield 'football-data caído' => [new ExternalServiceUnavailable('x'), 503, 'EXTERNAL_SERVICE_UNAVAILABLE'];
    }

    #[DataProvider('cases')]
    public function test_every_error_uses_the_same_json_shape_and_the_right_status(Throwable $e, int $status, string $code): void
    {
        $response = ApiErrorRenderer::render($e);
        $body = json_decode((string) $response->getContent(), true);

        $this->assertSame($status, $response->getStatusCode());
        $this->assertSame($code, $body['error']['code']);
        $this->assertArrayHasKey('message', $body['error']);
    }

    public function test_rate_limit_errors_expose_retry_after_header(): void
    {
        $response = ApiErrorRenderer::render(new ExternalRateLimitExceeded(45));

        $this->assertSame('45', $response->headers->get('Retry-After'));
    }
}
