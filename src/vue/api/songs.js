import fetch from '~/utils/fetch'

export function getAll(jwt, query) {
  return fetch({
    url: '/v1/songs',
    method: 'get',
    params: query,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function get(jwt, id) {
  return fetch({
    url: `/v1/songs/${id}`,
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function create(jwt, formData) {
  return fetch({
    url: '/v1/songs',
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function update(jwt, id, formData) {
  return fetch({
    url: `/v1/songs/${id}`,
    method: 'put',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function del(jwt, id) {
  return fetch({
    url: '/v1/songs',
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
    url: '/v1/songs/bulk',
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function getFormSource(jwt) {
  return fetch({
    url: '/v1/songs/formsource',
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function updateField(jwt, formData) {
  return fetch({
    url: `/v1/songs/${formData.id}/field`,
    method: 'put',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function upload(jwt, formData, type) {
  let data = new FormData()
  formData.map((item, index) => {
    data.append(`files[${index}][value]`, item.raw)
  })

  return fetch({
    url: `/v1/songs/import/${type}`,
    method: 'post',
    data: data,
    headers: {
      Authorization: 'Bearer ' + jwt,
      'Content-Type': 'multipart/form-data'
    }
  })
}

export function downloadToServer(jwt, formData) {
  return fetch({
    url: '/v1/songs/downloadtoserver',
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function reIndex(jwt) {
  return fetch({
    url: '/v1/songs/reindex',
    method: 'post',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function uploadFile(jwt, formData) {
  let data = new FormData()
  formData.map((item, index) => {
    data.append(`files[${index}][value]`, item.raw)
  })

  return fetch({
    url: `/v1/songs/upload`,
    method: 'post',
    data: data,
    headers: {
      Authorization: 'Bearer ' + jwt,
      'Content-Type': 'multipart/form-data'
    }
  })
}
