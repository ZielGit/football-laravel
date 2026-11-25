<?php

namespace App\Http\Controllers\Api;

use App\Football\Application\Query\GetTeam\GetTeamHandler;
use App\Football\Application\Query\GetTeam\GetTeamQuery;
use App\Football\Application\Query\ListTeams\ListTeamsHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListTeamsRequest;
use App\Http\Resources\TeamDetailResource;
use App\Http\Resources\TeamResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    /** GET /team — todos los equipos guardados (paginados). */
    public function index(ListTeamsRequest $request, ListTeamsHandler $handler): AnonymousResourceCollection
    {
        $page = $handler->handle($request->toQuery());

        return TeamResource::collection($page->items)->additional(['meta' => [
            'page' => $page->page,
            'per_page' => $page->perPage,
            'total' => $page->total,
            'last_page' => $page->lastPage(),
        ]]);
    }

    /** GET /team/{team_id} — detalle de un equipo (404 si no existe). */
    public function show(int $team_id, GetTeamHandler $handler): TeamDetailResource
    {
        return new TeamDetailResource($handler->handle(new GetTeamQuery($team_id)));
    }
}
