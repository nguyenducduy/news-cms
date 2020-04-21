import { lowerCase, findIndex, uniqBy } from 'lodash'
import {
  getAll,
  get,
  update,
  del,
  bulk,
  getFormSource,
  addReview,
  publish,
  useRev
} from '~/api/newss'

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

    // Distinct user to display editor
    state.data.records.map((item) => {
      item.editors = uniqBy(item.revision.data, 'uid')
    })

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

  update({ commit }, { authToken, id, formData }) {
    return update(authToken, id, formData)
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

  update_list({ commit }, { editedItem }) {
    commit('UPDATE_DATA', editedItem)
  },

  add_review({ commit }, { authToken, id, formData }) {
    return addReview(authToken, id, formData)
  },

  publish({ commit }, { authToken, id }) {
    return publish(authToken, id)
  },

  use_rev({ commit }, { authToken, id, formData }) {
    return useRev(authToken, id, formData)
  }
}
