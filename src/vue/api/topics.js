import fetch from '~/utils/fetch'

export function getAll(jwt, query) {
  return fetch({
    url: '/v1/topics',
    method: 'get',
    params: query,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function get(jwt, id) {
  return fetch({
    url: `/v1/topics/${id}`,
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function create(jwt, formData) {
  return fetch({
    url: '/v1/topics',
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function update(jwt, id, formData) {
  return fetch({
    url: `/v1/topics/${id}`,
    method: 'put',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function updateField(jwt, formData) {
  return fetch({
    url: `/v1/topics/${formData.id}/field`,
    method: 'put',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function del(jwt, id) {
  return fetch({
    url: '/v1/topics',
    method: 'delete',
    data: {
      id: id
    },
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function bulk(jwt, formData) {
  return fetch({
    url: '/v1/topics/bulk',
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function getFormSource(jwt) {
  return fetch({
    url: '/v1/topics/formsource',
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}
