<?php

namespace App\Http\Controllers\Api;

use App\Football\Application\Command\RequestCompetitionImport\RequestCompetitionImportCommand;
use App\Football\Application\Command\RequestCompetitionImport\RequestCompetitionImportHandler;
use App\Football\Application\Command\RequestCompetitionsRefresh\RequestCompetitionsRefreshHandler;
use App\Football\Application\Query\GetCompetition\GetCompetitionHandler;
use App\Football\Application\Query\GetCompetition\GetCompetitionQuery;
use App\Football\Application\Query\GetImportStatus\GetImportStatusHandler;
use App\Football\Application\Query\GetImportStatus\GetImportStatusQuery;
use App\Football\Application\Query\ListCompetitions\ListCompetitionsHandler;
use App\Football\Domain\Exception\CompetitionNotFound;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompetitionDetailResource;
use App\Http\Resources\CompetitionResource;
use App\Http\Resources\LeagueImportResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompetitionController extends Controller
{
    /** GET /competitions — responde con lo guardado; si está viejo, encola una actualización. */
    public function index(RequestCompetitionsRefreshHandler $refresh, ListCompetitionsHandler $list): AnonymousResourceCollection
    {
        $refresh->handle();
        $result = $list->handle();

        return CompetitionResource::collection($result->competitions)->additional(['meta' => [
            'last_synced_at' => $result->lastSyncedAt?->format(DATE_ATOM),
            'refreshing' => $result->refreshing,
        ]]);
    }

    /**
     * GET /competitions/{competition_id} — devuelve la liga guardada y, si el usuario puede importar,
     * encola la importación de sus equipos y jugadores (sin llamar a football-data en esta petición).
     */
    public function show(
        Request $request,
        int $competition_id,
        RequestCompetitionImportHandler $requestImport,
        GetCompetitionHandler $getCompetition,
    ): JsonResponse|CompetitionDetailResource {
        $user = $request->user();

        $import = $user->can('competitions.import')
            ? $requestImport->handle(new RequestCompetitionImportCommand($competition_id, $user->getAuthIdentifier()))
            : null;

        $detail = $getCompetition->handle(new GetCompetitionQuery($competition_id));

        if ($detail !== null) {
            return new CompetitionDetailResource($detail);
        }

        if ($import !== null && $import->isActive()) {
            // Liga todavía no guardada: la importación está en marcha.
            return response()->json([
                'data' => null,
                'meta' => ['import' => (new LeagueImportResource($import))->resolve($request)],
            ], 202);
        }

        throw new CompetitionNotFound($competition_id);
    }

    /** GET /competitions/{competition_id}/import-status — para consultar el avance sin disparar nada. */
    public function importStatus(Request $request, int $competition_id, GetImportStatusHandler $status): JsonResponse
    {
        $import = $status->handle(new GetImportStatusQuery($competition_id));

        return response()->json([
            'data' => $import === null ? null : (new LeagueImportResource($import))->resolve($request),
        ]);
    }
}
