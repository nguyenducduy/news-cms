import {
  getUserFromCookie,
  getTokenFromCookie,
  getUserFromLocalStorage,
  getTokenFromLocalStorage
} from '~/utils/auth'

export default function({ store, req }) {
  // If nuxt generate, pass this middleware
  if (process.server && !req) return

  const loggedUser = process.server
    ? getUserFromCookie(req)
    : getUserFromLocalStorage()
  const loggedToken = process.server
    ? getTokenFromCookie(req)
    : getTokenFromLocalStorage()

  store.commit('SET_USER', {
    user: loggedUser,
    token: loggedToken
  })
}
