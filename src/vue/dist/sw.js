importScripts('/assets/workbox.476439e0.js')

const workboxSW = new self.WorkboxSW({
  "cacheId": "oll-cms",
  "clientsClaim": true,
  "directoryIndex": "/"
})

workboxSW.precache([
  {
    "url": "/assets/layouts/admin.4d84249143f669ede66b.js",
    "revision": "83c9d8f1fe7d57590e159c658df3c8c3"
  },
  {
    "url": "/assets/layouts/default.12031b97512b331c7c19.js",
    "revision": "30180377e872e16fb201e472e111e5eb"
  },
  {
    "url": "/assets/layouts/site.fc4da4b132c646bed348.js",
    "revision": "1e38268dad3cc2cf2c4b82a3cc492d65"
  },
  {
    "url": "/assets/manifest.f21140bc2c6877dd810d.js",
    "revision": "d595614a93c439cc900df7a15f058757"
  },
  {
    "url": "/assets/pages/403.e36e2e3f2221cdd4ada4.js",
    "revision": "057656066a4f4dbfd6f7f092821ce3bb"
  },
  {
    "url": "/assets/pages/404.1e54afb59331cc11c14d.js",
    "revision": "4a369cc0ebc14803116206ab1c78a192"
  },
  {
    "url": "/assets/pages/admin/dashboard/index.e53f9f8ed636731bd303.js",
    "revision": "6b392b7aa4ad1edbaee02ee458ae9ea0"
  },
  {
    "url": "/assets/pages/admin/dhamma/create.0a23072efd6334b3f934.js",
    "revision": "1d44abc46fba0ce5f94657fe2272ba4e"
  },
  {
    "url": "/assets/pages/admin/dhamma/index.9db2a280ad5d5439c5c2.js",
    "revision": "5593237e77dda21f177d6f7269d5e0b0"
  },
  {
    "url": "/assets/pages/admin/login/index.6d9c55f8adc72c1e19ab.js",
    "revision": "f9397e65f46f01859235b8031a1b4ab2"
  },
  {
    "url": "/assets/pages/admin/song/index.f723104da8895d5680f0.js",
    "revision": "9be51af110c92b37857765d994fa4ea2"
  },
  {
    "url": "/assets/pages/admin/user/changepassword.1b32688c60f61f782969.js",
    "revision": "38ad26ade54439cbc9841bf0744ec36e"
  },
  {
    "url": "/assets/pages/admin/user/create.27f1160682033b1635d9.js",
    "revision": "671c501b771a4653b042f0491d477d36"
  },
  {
    "url": "/assets/pages/admin/user/edit/_id.3df7575c98d23ee1b962.js",
    "revision": "9880ba2f86d194bd4bd4504ad2df30f9"
  },
  {
    "url": "/assets/pages/admin/user/index.8359c9520a8772d92f66.js",
    "revision": "d3026ff15bb71e184ba2166909833d9a"
  },
  {
    "url": "/assets/pages/admin/user/logout.db0269462c648c83f26d.js",
    "revision": "b2fa53b9123f009696aa0006bad5f89b"
  },
  {
    "url": "/assets/pages/index.0ea80d75d593c7e274f0.js",
    "revision": "4e25a4a1d433926a68bee6069ba403b7"
  }
])


workboxSW.router.registerRoute(new RegExp('/assets/.*'), workboxSW.strategies.cacheFirst({}), 'GET')

workboxSW.router.registerRoute(new RegExp('/.*'), workboxSW.strategies.networkFirst({}), 'GET')

