import fetch from '~/utils/fetch'

export function getAll(jwt, query) {
  return fetch({
    url: '/v1/newss?include=revision',
    method: 'get',
    params: query,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function get(jwt, id) {
  return fetch({
    url: `/v1/newss/${id}?include=revision,review,history`,
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function create(jwt, formData) {
  return fetch({
    url: '/v1/newss',
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function update(jwt, id, formData) {
  return fetch({
    url: `/v1/newss/${id}`,
    method: 'put',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function del(jwt, id) {
  return fetch({
    url: '/v1/newss',
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
    url: '/v1/newss/bulk',
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function getFormSource(jwt) {
  return fetch({
    url: '/v1/newss/formsource',
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function updateField(jwt, formData) {
  return fetch({
    url: `/v1/newss/${formData.id}/field`,
    method: 'put',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function addReview(jwt, id, formData) {
  return fetch({
    url: `/v1/newss/${id}/review`,
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function publish(jwt, id) {
  return fetch({
    url: `/v1/newss/${id}/publish`,
    method: 'post',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function useRev(jwt, id, formData) {
  return fetch({
    url: `/v1/newss/${id}/userev`,
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}
