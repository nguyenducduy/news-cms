<template>
  <p>Logging off...</p>
</template>

<script>
import { unsetToken } from '~/utils/auth'

export default {
  mounted() {
    unsetToken()

    setInterval(function() {
      window.location = '/admin/login'
    }, 2000)
  }
}
</script>
