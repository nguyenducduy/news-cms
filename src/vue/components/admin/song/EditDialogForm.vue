<template>
  <el-dialog
    :title="title"
    :visible="dialogFormVisible"
    width="30%"
    v-on:open="handleOpen"
    :before-close="handleClose"
    >
    <el-form autoComplete="on" label-position="left" class="edit-form" :model="form" ref="form">
      <el-form-item prop="name" :label="$t('pages.admin.songs.label.name')">
        <el-input type="text" v-model="form.name" size="small"></el-input>
      </el-form-item>
      <el-form-item prop="title" :label="$t('pages.admin.songs.label.title')">
        <el-input type="text" v-model="form.title" size="small"></el-input>
      </el-form-item>
      <el-form-item prop="artist" :label="$t('pages.admin.songs.label.artist')">
        <el-input type="text" v-model="form.artist" size="small"></el-input>
      </el-form-item>
      <el-form-item prop="genre" :label="$t('pages.admin.songs.label.genre')">
        <el-input type="text" v-model="form.genre" size="small"></el-input>
      </el-form-item>
      <el-form-item prop="status" :label="$t('pages.admin.songs.label.status')">
        <el-select size="small" v-model="form.status" :placeholder="$t('pages.admin.songs.label.selectStatus')" style="width: 100%" :loading="loading">
          <el-option v-for="item in source.statusList" :key="item.label" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
      </el-form-item>
      <el-form-item prop="downloadstatus" :label="$t('pages.admin.songs.label.downloadstatus')">
        <el-select v-model="form.downloadstatus" :placeholder="$t('pages.admin.songs.label.selectDownloadStatus')" style="width: 100%" :loading="loading" size="small">
          <el-option v-for="item in source.downloadStatusList
" :key="item.label" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
      </el-form-item>
      <el-form-item prop="downloadlink" :label="$t('pages.admin.songs.label.downloadLink')">
        <el-input type="textarea" v-model="form.downloadlink" size="small" :rows="4"></el-input>
      </el-form-item>
    </el-form>

    <span slot="footer" class="dialog-footer">
      <el-button size="small" type="primary" :loading="loading" @click.native.prevent="handleSubmit"> {{ $t('default.update') }}
      </el-button>
      <el-button size="small" @click="handleClose">{{ $t('default.cancel') }}</el-button>
    </span>
  </el-dialog>
</template>

<script>
export default {
  props: ['dialogFormVisible', 'id', 'hideEditForm'],

  data() {
    return {
      title: '',
      loading: false,
      form: {
        name: '',
        title: '',
        artist: '',
        composer: '',
        genre: '',
        status: '',
        downloadstatus: '',
        downloadlink: ''
      },
      source: {}
    }
  },
  methods: {
    handleClose() {
      this.hideEditForm()
    },
    handleOpen() {
      this.title = "Edit song #" + this.id
      this.fetchSourceAction(),
      this.fetchSongInfo()
    },
    handleSubmit() {
      this.$refs.form.validate(async valid => {
        if (valid) {
          this.loading = true
          await this.$store
            .dispatch('songs/update', {
              authToken: this.$store.state.authToken,
              id: this.$props.id,
              formData: this.form
            })
            .then(res => {
              this.loading = false

              this.$message({
                showClose: true,
                message: this.$t('pages.admin.songs.msg.updateSuccess'),
                type: 'success',
                duration: 5 * 1000
              })

              this.hideEditForm()
            })
            .catch(e => {
              this.loading = false
            })
        } else {
          return false
        }
      })
    },
    async resetField() {
      await this.$store
        .dispatch('songs/get', {
          authToken: this.$store.state.authToken,
          id: this.$props.id
        })
        .then(res => {
          return this.mapProperty(res)
        })
    },
    async fetchSourceAction() {
      await this.$store
        .dispatch('songs/get_form_source', {
          authToken: this.$store.state.authToken
        })
        .then(() => {
          this.source = this.$store.state.songs.formSource.records
        })
        .catch(e => {
          this.error = true
        })
    },
    async fetchSongInfo() {
      await this.$store
        .dispatch('songs/get', {
          authToken: this.$store.state.authToken,
          id: this.$props.id
        })
        .then(res => {
          return this.mapProperty(res)
        })
        .catch(e => {
          this.error = true
        })
    },
    mapProperty(res) {
      this.form.name = res.data.response.name
      this.form.title = res.data.response.title
      this.form.artist = res.data.response.artist
      this.form.genre = res.data.response.genre
      this.form.status = res.data.response.status.value
      this.form.downloadstatus = res.data.response.downloadstatus.value
      this.form.downloadlink = res.data.response.downloadlink
    }
  }
}
</script>
