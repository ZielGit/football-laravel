<?php

namespace App\Http\Controllers\Api;

use App\Football\Application\Query\GetPlayer\GetPlayerHandler;
use App\Football\Application\Query\GetPlayer\GetPlayerQuery;
use App\Football\Application\Query\ListPlayers\ListPlayersHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListPlayersRequest;
use App\Http\Resources\PlayerResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlayerController extends Controller
{
    /** GET /players — todos los jugadores guardados (paginados). */
    public function index(ListPlayersRequest $request, ListPlayersHandler $handler): AnonymousResourceCollection
    {
        $page = $handler->handle($request->toQuery());

        return PlayerResource::collection($page->items)->additional(['meta' => [
            'page' => $page->page,
            'per_page' => $page->perPage,
            'total' => $page->total,
            'last_page' => $page->lastPage(),
        ]]);
    }

    /** GET /players/{player_id} — extra: detalle de un jugador (404 si no existe). */
    public function show(int $player_id, GetPlayerHandler $handler): PlayerResource
    {
        return new PlayerResource($handler->handle(new GetPlayerQuery($player_id)));
    }
}
