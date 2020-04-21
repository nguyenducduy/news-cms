import fetch from '~/utils/fetch'

export function getAll(jwt, query) {
  return fetch({
    url: '/v1/dashboards',
    method: 'get',
    params: query,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function getDbstatus(jwt, query) {
  return fetch({
    url: '/v1/dashboards/dbstatus',
    method: 'get',
    params: query,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function getSearchstatus(jwt, query) {
  return fetch({
    url: '/v1/dashboards/searchstatus',
    method: 'get',
    params: query,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function getQueuestatus(jwt, query) {
  return fetch({
    url: '/v1/dashboards/queuestatus',
    method: 'get',
    params: query,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}


