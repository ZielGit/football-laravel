<?php

namespace App\Http\Requests;

use App\Football\Application\Query\ListTeams\ListTeamsQuery;
use Illuminate\Foundation\Http\FormRequest;

class ListTeamsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'competition_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function toQuery(): ListTeamsQuery
    {
        return new ListTeamsQuery(
            (int) $this->integer('page', 1),
            (int) $this->integer('per_page', 20),
            $this->filled('search') ? (string) $this->input('search') : null,
            $this->filled('competition_id') ? (int) $this->input('competition_id') : null,
        );
    }
}
