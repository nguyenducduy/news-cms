import { lowerCase, findIndex } from 'lodash'
import {
  getAll,
  get,
  create,
  update,
  del,
  bulk,
  getFormSource,
  updateField,
  upload,
  downloadToServer,
  reIndex,
  uploadFile
} from '~/api/songs'

export const state = () => ({
  data: {},
  query: {},
  formSource: {},
  totalItems: 0,
  recordPerPage: 0
})

export const mutations = {
  SET_QUERY(state) {
    state.query.orderby =
      typeof state.data.meta !== 'undefined'
        ? lowerCase(state.data.meta.orderBy)
        : 'id'
    state.query.ordertype =
      typeof state.data.meta !== 'undefined'
        ? lowerCase(state.data.meta.orderType)
        : 'desc'
    state.query.page =
      typeof state.data.meta !== 'undefined' ? state.data.meta.page : 1
  },

  SET_DATA(state, responseData) {
    state.data = responseData || null
    state.totalItems =
      typeof responseData.meta !== 'undefined'
        ? responseData.meta.totalItems
        : 0
    state.recordPerPage =
      typeof responseData.meta !== 'undefined'
        ? responseData.meta.recordPerPage
        : 0
  },

  SET_FORM_SOURCE(state, responseData) {
    state.formSource = responseData || null
  },

  UPDATE_DATA(state, editedItem) {
    const index = findIndex(state.data.records, { id: editedItem.id })
    state.data.records.splice(index, 1, editedItem)
  },

  DELETE_DATA(state, removedItem) {
    const index = findIndex(state.data.records, { id: removedItem.id })
    state.data.records.splice(index, 1)
    state.totalItems = state.totalItems - 1
  },

  CHANGE_DOWNLOAD_STATUS(state, selectedItem) {
    const index = findIndex(state.data.records, { id: selectedItem.id })
    state.data.records.splice(index, 1, selectedItem)
  }
}

export const actions = {
  get_all({ commit }, { authToken, query }) {
    return getAll(authToken, query).then(res => {
      commit('SET_DATA', res.data)
      commit('SET_QUERY')
    })
  },

  get({ commit }, { authToken, id }) {
    return get(authToken, id)
  },

  create({ commit }, { authToken, formData }) {
    return create(authToken, formData)
  },

  update({ commit }, { authToken, id, formData }) {
    return update(authToken, id, formData).then(res => {
      commit('UPDATE_DATA', res.data.response)
      return res
    })
  },

  delete({ commit }, { authToken, id }) {
    return del(authToken, id).then(res => {
      commit('DELETE_DATA', res.data.response)
    })
  },

  bulk({ commit }, { authToken, formData }) {
    return bulk(authToken, formData)
  },

  get_form_source({ commit }, { authToken }) {
    return getFormSource(authToken).then(res => {
      commit('SET_FORM_SOURCE', res.data)
    })
  },

  update_field({ commit }, { authToken, formData }) {
    return updateField(authToken, formData).then(res => {
      commit('UPDATE_DATA', res.data.response)
      return res
    })
  },

  upload({ commit }, { authToken, formData, type }) {
    return upload(authToken, formData, type)
  },

  download_to_server({ commit }, { authToken, formData }) {
    return downloadToServer(authToken, formData).then(res => {
      commit('CHANGE_DOWNLOAD_STATUS', res.data.response)
      return res
    })
  },

  re_index({ commit }, { authToken }) {
    return reIndex(authToken).then(res => {
      return res
    })
  },

  upload_file({ commit }, { authToken, formData }) {
    return uploadFile(authToken, formData)
  }
}
