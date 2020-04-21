<template>
  <el-upload
    ref="upload"
    action=""
    accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
    :multiple="true"
    :auto-upload="false"
    :with-credentials="true"
    :file-list="myFiles"
    :on-change="handleChange"
    :on-remove="handleRemove"
    style="float: left; display: block">
    <el-button slot="trigger" size="mini" type="primary">
      {{ $t('pages.admin.songs.label.selectFiles') }}
    </el-button>
    <el-button v-show="myFiles.length > 0" :loading="loading" style="margin-left: 10px;" size="mini" icon="el-icon-fa-upload" type="success" @click="submitUpload">
      {{ $t('pages.admin.songs.label.import') }}
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
        .dispatch('songs/upload', {
          authToken: this.$store.state.authToken,
          formData: this.myFiles,
          type: 'excel'
        })
        .then(async res => {
          this.loading = false
          this.myFiles = []

          if (res.data.records.recordsImported === 0) {
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
                    `<strong>${res.data.records.recordsImported}</strong>` +
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
