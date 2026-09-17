<?php

namespace Pterodactyl\Models\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AdminServerFilter implements Filter
{
    /**
     * A multi-column filter for the servers table that allows an administrative user to search
     * across UUID, name, owner username, and owner email.
     *
     * @param string $value
     */
    public function __invoke(Builder $query, $value, string $property)
    {
        if ($query->getQuery()->from !== 'servers') {
            throw new \BadMethodCallException('Cannot use the AdminServerFilter against a non-server model.');
        }
        $query
            ->select('servers.*')
            ->leftJoin('users', 'users.id', '=', 'servers.owner_id')
            ->leftJoin('nodes', 'nodes.id', '=', 'servers.node_id')
            ->where(function (Builder $builder) use ($value, $query) {
                $builder->where('servers.uuid', $value)
                    ->orWhere('servers.uuid', 'LIKE', "$value%")
                    ->orWhere('servers.uuidShort', $value)
                    ->orWhere('servers.external_id', $value)
                    ->orWhereRaw('LOWER(users.username) LIKE ?', ["%$value%"])
                    ->orWhereRaw('LOWER(users.email) LIKE ?', ["%$value%"])
                    ->orWhereRaw('LOWER(users.name_first) LIKE ?', ["%$value%"])
                    ->orWhereRaw('LOWER(users.name_last) LIKE ?', ["%$value%"])
                    ->orWhereRaw('LOWER(servers.name) LIKE ?', ["%$value%"])
                    ->orWhereRaw('LOWER(nodes.name) LIKE ?', ["%$value%"]);

                $driver = $query->getConnection()->getDriverName();
                if ($driver === 'sqlite') {
                    $concat = "LOWER(COALESCE(users.name_first, '') || ' ' || COALESCE(users.name_last, '')) LIKE ?";
                    $concatRev = "LOWER(COALESCE(users.name_last, '') || ' ' || COALESCE(users.name_first, '')) LIKE ?";
                } else {
                    $concat = "LOWER(CONCAT(COALESCE(users.name_first, ''), ' ', COALESCE(users.name_last, ''))) LIKE ?";
                    $concatRev = "LOWER(CONCAT(COALESCE(users.name_last, ''), ' ', COALESCE(users.name_first, ''))) LIKE ?";
                }

                $builder->orWhereRaw($concat, ["%$value%"])
                    ->orWhereRaw($concatRev, ["%$value%"]);
            })
            ->groupBy('servers.id');
    }
}
