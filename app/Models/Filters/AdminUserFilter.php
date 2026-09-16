<?php

namespace Pterodactyl\Models\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AdminUserFilter implements Filter
{
    /**
     * A multi-column filter for the users table that allows an administrative user to search
     * across UUID, username, email, name_first, and name_last.
     *
     * @param string $value
     */
    public function __invoke(Builder $query, $value, string $property)
    {
        $query->where(function (Builder $builder) use ($value, $query) {
            $builder->where('users.uuid', $value)
                ->orWhere('users.uuid', 'LIKE', "$value%")
                ->orWhere('users.external_id', $value)
                ->orWhereRaw('LOWER(users.username) LIKE ?', ["%$value%"])
                ->orWhereRaw('LOWER(users.email) LIKE ?', ["%$value%"])
                ->orWhereRaw('LOWER(users.name_first) LIKE ?', ["%$value%"])
                ->orWhereRaw('LOWER(users.name_last) LIKE ?', ["%$value%"]);

            $driver = $query->getConnection()->getDriverName();
            if ($driver === 'sqlite') {
                $concat = 'LOWER(COALESCE(users.name_first, "") || " " || COALESCE(users.name_last, "")) LIKE ?';
                $concatRev = 'LOWER(COALESCE(users.name_last, "") || " " || COALESCE(users.name_first, "")) LIKE ?';
            } else {
                $concat = 'LOWER(CONCAT(COALESCE(users.name_first, ""), " ", COALESCE(users.name_last, ""))) LIKE ?';
                $concatRev = 'LOWER(CONCAT(COALESCE(users.name_last, ""), " ", COALESCE(users.name_first, ""))) LIKE ?';
            }

            $builder->orWhereRaw($concat, ["%$value%"])
                ->orWhereRaw($concatRev, ["%$value%"]);
        });
    }
}
