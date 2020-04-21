import fetch from '~/utils/fetch'

export function getAll(jwt, query) {
  return fetch({
    url: '/v1/dhammas',
    method: 'get',
    params: query,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function get(jwt, id) {
  return fetch({
    url: `/v1/dhammas/${id}`,
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function create(jwt, formData, files) {
  let data = new FormData()
  files.map((item, index) => {
    data.append(`files[${index}][value]`, item.raw)
  })
  data.append('title', formData.title)
  data.append('author', formData.author)
  data.append('seokeyword', formData.seokeyword)
  data.append('seodescription', formData.seodescription)
  data.append('status', formData.status)

  return fetch({
    url: `/v1/dhammas`,
    method: 'post',
    data: data,
    headers: {
      Authorization: 'Bearer ' + jwt,
      'Content-Type': 'multipart/form-data'
    }
  })
}

export function update(jwt, id, formData) {
  return fetch({
    url: `/v1/dhammas/${id}`,
    method: 'put',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function del(jwt, id) {
  return fetch({
    url: '/v1/dhammas',
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
    url: '/v1/dhammas/bulk',
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function getFormSource(jwt) {
  return fetch({
    url: '/v1/dhammas/formsource',
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function updateField(jwt, formData) {
  return fetch({
    url: `/v1/dhammas/${formData.id}/field`,
    method: 'put',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}
