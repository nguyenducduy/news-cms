import { indexOf } from 'lodash'
import { checkChangePassword } from '~/api/users'

const secureGroup = ['administrator', 'moderator', 'member', 'editor']

export default function({ store, route, redirect, next }) {
  const routePath = route.path
  const loggedUser = store.getters.loggedUser
  const loggedToken = store.getters.loggedToken

  let redirectUrl = '/'
  if (routePath === '/admin') {
    redirectUrl = '/admin/login'
  }

  if (!store.getters.isAuthenticated) {
    const redirectEncodeUrl = new Buffer(routePath).toString('base64')
    return redirect(`${redirectUrl}?redirect=${redirectEncodeUrl}`)
  } else {
    if (
      routePath.indexOf('admin') &&
      indexOf(secureGroup, loggedUser.sub.groupid) === -1
    ) {
      return redirect('/403')
    }

    // else {
    //   if (typeof loggedToken !== 'undefined') {
    //     return new Promise((resolve, reject) => {
    //       checkChangePassword(loggedToken)
    //         .then(() => {
    //           return
    //         })
    //         .catch(e => {
    //           reject(redirect('/admin/user/logout'))
    //         })
    //     })
    //   }
    // }
  }
}
