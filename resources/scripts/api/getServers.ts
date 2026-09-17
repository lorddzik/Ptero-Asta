import { rawDataToServerObject, Server } from '@/api/server/getServer';
import http, { getPaginationSet, PaginatedResult } from '@/api/http';

interface QueryParams {
    query?: string;
    page?: number;
    type?: string;
    node?: string;
}

export interface ServersResponse extends PaginatedResult<Server> {
    nodes?: string[];
}

export default ({ query, node, ...params }: QueryParams): Promise<ServersResponse> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client', {
            params: {
                'filter[*]': query,
                'filter[node]': node,
                ...params,
            },
        })
            .then(({ data }) =>
                resolve({
                    items: (data.data || []).map((datum: any) => rawDataToServerObject(datum)),
                    pagination: getPaginationSet(data.meta.pagination),
                    nodes: data.meta?.nodes || [],
                })
            )
            .catch(reject);
    });
};
