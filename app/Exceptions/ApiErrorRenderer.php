<?php

namespace App\Exceptions;

use App\Football\Domain\Exception\EntityNotFound;
use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use App\Football\Domain\Exception\ExternalResourceNotFound;
use App\Football\Domain\Exception\ExternalServiceException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Traduce cualquier excepción al formato único de error de la API:
 * { "error": { "code": "...", "message": "...", "details": {...} } }
 */
final class ApiErrorRenderer
{
    public static function render(Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException => self::json(422, 'VALIDATION_ERROR', 'Los datos enviados no son válidos.', ['fields' => $e->errors()]),
            $e instanceof AuthenticationException => self::json(401, 'UNAUTHENTICATED', 'Sesión no iniciada o vencida. Inicia sesión nuevamente.'),
            $e instanceof InvalidCredentialsException => self::json(401, 'INVALID_CREDENTIALS', $e->getMessage()),
            $e instanceof AccountDisabledException => self::json(403, 'ACCOUNT_DISABLED', $e->getMessage()),
            $e instanceof EntityNotFound => self::json(404, $e->errorCode(), $e->getMessage()),
            $e instanceof ExternalRateLimitExceeded => self::json(429, 'EXTERNAL_RATE_LIMIT', $e->getMessage(), ['retry_after' => $e->retryAfterSeconds], ['Retry-After' => (string) $e->retryAfterSeconds]),
            $e instanceof ExternalResourceNotFound => self::json(404, 'EXTERNAL_RESOURCE_NOT_FOUND', $e->getMessage()),
            $e instanceof ExternalServiceException => self::json(503, 'EXTERNAL_SERVICE_UNAVAILABLE', $e->getMessage()),
            $e instanceof HttpExceptionInterface => self::fromHttp($e),
            default => self::json(500, 'SERVER_ERROR', config('app.debug') ? $e->getMessage() : 'Error interno del servidor.'),
        };
    }

    private static function fromHttp(HttpExceptionInterface $e): JsonResponse
    {
        $status = $e->getStatusCode();
        $headers = $e->getHeaders();

        [$code, $message] = match ($status) {
            403 => ['FORBIDDEN', 'No tienes permiso para realizar esta acción.'],
            404 => ['NOT_FOUND', 'El recurso solicitado no existe.'],
            405 => ['METHOD_NOT_ALLOWED', 'Método no permitido.'],
            429 => ['TOO_MANY_REQUESTS', 'Demasiadas peticiones. Espera un momento e inténtalo de nuevo.'],
            default => ['HTTP_ERROR', $e->getMessage() !== '' ? $e->getMessage() : 'Error en la petición.'],
        };

        $details = isset($headers['Retry-After']) ? ['retry_after' => (int) $headers['Retry-After']] : null;

        return self::json($status, $code, $message, $details, $headers);
    }

    /**
     * @param  array<string, mixed>|null  $details
     * @param  array<string, string>  $headers
     */
    private static function json(int $status, string $code, string $message, ?array $details = null, array $headers = []): JsonResponse
    {
        $error = ['code' => $code, 'message' => $message];

        if ($details !== null) {
            $error['details'] = $details;
        }

        return new JsonResponse(['error' => $error], $status, $headers);
    }
}
