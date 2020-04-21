<template>
  <div>
    <el-tooltip class="item" effect="dark" :content="myIcon.label" placement="top" :enterable="false" v-show="!loading && downloadStatus !== '5'">
      <el-button :icon="myIcon.type" size="mini" class="download_btn" @click="handleDownload"></el-button>
    </el-tooltip>
    <el-tooltip class="item" effect="dark" :content="loadingText" placement="top" :enterable="false" v-show="loading">
      <el-button :icon="loadingIcon" size="mini" class="download_btn"></el-button>
    </el-tooltip>
  </div>
</template>

<script>
export default {
  props: ['id', 'downloadStatus'],

  data() {
    return {
      loading: false,
      loadingIcon: 'el-icon-fa-cog el-icon-fa-spin',
      loadingText: this.$t('default.downloading')
    }
  },

  computed: {
    myIcon() {
      let iconString,
        labelString = ''

      switch (this.downloadStatus) {
        case '1':
        case '7':
        case '9':
          iconString = 'el-icon-fa-cloud-download'
          labelString = this.$t('pages.admin.songs.label.download')
          break
        case '3':
          iconString = 'el-icon-fa-cog el-icon-fa-spin'
          labelString = this.$t('default.downloading')
          break
      }

      return {
        type: iconString,
        label: labelString
      }
    }
  },

  methods: {
    async handleDownload() {
      // Only handle with status not equal Downloading
      if (this.downloadStatus != '3') {
        this.loading = true

        await this.$store
          .dispatch('songs/download_to_server', {
            authToken: this.$store.state.authToken,
            formData: {
              id: this.id
            }
          })
          .then(res => {
            this.$notify.success({
              iconClass: 'el-icon-fa-cloud-download',
              position: 'bottom-right',
              dangerouslyUseHTMLString: true,
              title: this.$t('default.notify'),
              message: this.$t(
                'pages.admin.songs.msg.putToQueueDowloadSuccess'
              ).replace('###name###', res.data.response.name)
            })
          })
      }
    }
  }
}
</script>
<style scoped lang="scss">
  .download_btn {
    padding: 6px 15px;
  }
  .el-icon-fa-cog {
    color: red;
  }
</style>
