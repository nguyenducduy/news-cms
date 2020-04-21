import { getLocaleFromCookie, getLocaleFromLocalStorage } from '~/utils/locale'

export default function({ app, store, req }) {
  // If nuxt generate, pass this middleware
  if (process.server && !req) return

  // Get locale from cookie
  let locale = process.server ? getLocaleFromCookie(req) : getLocaleFromLocalStorage()

  if (typeof locale === 'undefined') {
    locale = 'en'
  }

  // Set locale
  store.commit('SET_LANG', locale)
  app.i18n.locale = store.state.locale
}
