import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

const _14014f1b = () => import('../pages/403.vue' /* webpackChunkName: "pages/403" */).then(m => m.default || m)
const _140f669c = () => import('../pages/404.vue' /* webpackChunkName: "pages/404" */).then(m => m.default || m)
const _8322148a = () => import('../pages/admin/dashboard/index.vue' /* webpackChunkName: "pages/admin/dashboard/index" */).then(m => m.default || m)
const _6973cb60 = () => import('../pages/admin/login/index.vue' /* webpackChunkName: "pages/admin/login/index" */).then(m => m.default || m)
const _b9f3fcbc = () => import('../pages/admin/news/index.vue' /* webpackChunkName: "pages/admin/news/index" */).then(m => m.default || m)
const _1d820164 = () => import('../pages/admin/song/index.vue' /* webpackChunkName: "pages/admin/song/index" */).then(m => m.default || m)
const _8842e6cc = () => import('../pages/admin/user/index.vue' /* webpackChunkName: "pages/admin/user/index" */).then(m => m.default || m)
const _474dbf33 = () => import('../pages/admin/user/changepassword.vue' /* webpackChunkName: "pages/admin/user/changepassword" */).then(m => m.default || m)
const _30b15d38 = () => import('../pages/admin/user/create.vue' /* webpackChunkName: "pages/admin/user/create" */).then(m => m.default || m)
const _7850cef2 = () => import('../pages/admin/user/logout.vue' /* webpackChunkName: "pages/admin/user/logout" */).then(m => m.default || m)
const _7eade01d = () => import('../pages/admin/user/edit/_id.vue' /* webpackChunkName: "pages/admin/user/edit/_id" */).then(m => m.default || m)
const _7185e636 = () => import('../pages/index.vue' /* webpackChunkName: "pages/index" */).then(m => m.default || m)



if (process.client) {
  window.history.scrollRestoration = 'manual'
}
const scrollBehavior = function (to, from, savedPosition) {
  // if the returned position is falsy or an empty object,
  // will retain current scroll position.
  let position = false

  // if no children detected
  if (to.matched.length < 2) {
    // scroll to the top of the page
    position = { x: 0, y: 0 }
  } else if (to.matched.some((r) => r.components.default.options.scrollToTop)) {
    // if one of the children has scrollToTop option set to true
    position = { x: 0, y: 0 }
  }

  // savedPosition is only available for popstate navigations (back button)
  if (savedPosition) {
    position = savedPosition
  }

  return new Promise(resolve => {
    // wait for the out transition to complete (if necessary)
    window.$nuxt.$once('triggerScroll', () => {
      // coords will be used if no selector is provided,
      // or if the selector didn't match any element.
      if (to.hash) {
        let hash = to.hash
        // CSS.escape() is not supported with IE and Edge.
        if (typeof window.CSS !== 'undefined' && typeof window.CSS.escape !== 'undefined') {
          hash = '#' + window.CSS.escape(hash.substr(1))
        }
        try {
          if (document.querySelector(hash)) {
            // scroll to anchor by returning the selector
            position = { selector: hash }
          }
        } catch (e) {
          console.warn('Failed to save scroll position. Please add CSS.escape() polyfill (https://github.com/mathiasbynens/CSS.escape).')
        }
      }
      resolve(position)
    })
  })
}


export function createRouter () {
  return new Router({
    mode: 'history',
    base: '/',
    linkActiveClass: 'nuxt-link-active',
    linkExactActiveClass: 'nuxt-link-exact-active',
    scrollBehavior,
    routes: [
		{
			path: "/403",
			component: _14014f1b,
			name: "403"
		},
		{
			path: "/404",
			component: _140f669c,
			name: "404"
		},
		{
			path: "/admin/dashboard",
			component: _8322148a,
			name: "admin-dashboard"
		},
		{
			path: "/admin/login",
			component: _6973cb60,
			name: "admin-login"
		},
		{
			path: "/admin/news",
			component: _b9f3fcbc,
			name: "admin-news"
		},
		{
			path: "/admin/song",
			component: _1d820164,
			name: "admin-song"
		},
		{
			path: "/admin/user",
			component: _8842e6cc,
			name: "admin-user"
		},
		{
			path: "/admin/user/changepassword",
			component: _474dbf33,
			name: "admin-user-changepassword"
		},
		{
			path: "/admin/user/create",
			component: _30b15d38,
			name: "admin-user-create"
		},
		{
			path: "/admin/user/logout",
			component: _7850cef2,
			name: "admin-user-logout"
		},
		{
			path: "/admin/user/edit/:id?",
			component: _7eade01d,
			name: "admin-user-edit-id"
		},
		{
			path: "/",
			component: _7185e636,
			name: "index"
		}
    ],
    
    
    fallback: false
  })
}
