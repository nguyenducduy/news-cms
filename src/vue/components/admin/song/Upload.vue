<template>
  <el-upload
    ref="upload"
    action=""
    accept="audio/mp3"
    :multiple="true"
    :auto-upload="false"
    :with-credentials="true"
    :file-list="myFiles"
    :on-change="handleChange"
    :on-remove="handleRemove"
    style="float: left; display: block; margin-right: 5px;">
    <el-button slot="trigger" size="mini" type="info">
      {{ $t('pages.admin.songs.label.selectUploadFiles') }}
    </el-button>
    <el-button v-show="myFiles.length > 0"
      :loading="loading"
      size="mini"
      icon="el-icon-fa-upload"
      type="success"
      @click="submitUpload"
      style="margin-left: 10px;">
      {{ $t('pages.admin.songs.label.upload') }}
    </el-button>
  </el-upload>
</template>

<script>
import { assign } from 'lodash'

export default {
  data() {
    return {
      loading: false,
      myFiles: []
    }
  },

  methods: {
    handleChange(file, fileList) {
      this.myFiles = fileList
    },
    handleRemove(file, fileList) {
      this.myFiles = fileList
    },
    async submitUpload() {
      this.loading = true

      // upload to server
      await this.$store
        .dispatch('songs/upload_file', {
          authToken: this.$store.state.authToken,
          formData: this.myFiles
        })
        .then(async res => {
          this.loading = false
          this.myFiles = []

          if (res.data.records.recordsUploaded === 0) {
            this.$notify({
              title: this.$t('pages.admin.songs.label.titleUploadNotification'),
              message: this.$t('pages.admin.songs.msg.uploadZero'),
              type: 'warning'
            })
          } else {
            // reload page
            let queryObject = assign({}, this.$route.query)

            await this.$store
              .dispatch('songs/get_all', {
                authToken: this.$store.state.authToken,
                query: queryObject
              })
              .then(() => {
                this.$notify({
                  dangerouslyUseHTMLString: true,
                  title: this.$t(
                    'pages.admin.songs.label.titleUploadNotification'
                  ),
                  message:
                    `<strong>${res.data.records.recordsUploaded}</strong>` +
                    this.$t('pages.admin.songs.msg.uploadSuccess'),
                  type: 'success'
                })
              })
          }
        })
        .catch(() => {
          this.loading = false
        })
    }
  }
}
</script>
