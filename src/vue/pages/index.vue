<template>
</template>

<script>
export default {
  layout: 'site',

  head () {
    return {
      title: this.$t('pages.site.home.title'),
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: this.$t('pages.site.home.title')
        }
      ]
    }
  },

  mounted () {
    return this.$router.push('/admin/login')
  }
}
</script>
