<template>
  <el-dialog
    :title="title"
    :visible="dialogFormVisible"
    width="30%"
    v-on:open="handleOpen"
    :before-close="handleClose"
    >
    <el-form autoComplete="on" label-position="left" class="edit-form" :model="form" ref="form">
      <el-form-item prop="title" :label="$t('pages.admin.dhammas.label.title')">
        <el-input type="text" v-model="form.title" size="small"></el-input>
      </el-form-item>
      <el-form-item prop="author" :label="$t('pages.admin.dhammas.label.author')">
        <el-input type="text" v-model="form.author" size="small"></el-input>
      </el-form-item>
      <el-form-item prop="seokeyword" :label="$t('pages.admin.dhammas.label.seokeyword')">
        <input-tag :tags.sync="form.seokeyword" style="width: 93%"></input-tag>
      </el-form-item>
      <el-form-item prop="seodescription" :label="$t('pages.admin.dhammas.label.seodescription')">
        <el-input type="text" v-model="form.seodescription" size="small"></el-input>
      </el-form-item>
      <el-form-item prop="status" :label="$t('pages.admin.dhammas.label.status')">
        <el-select size="small" v-model="form.status" :placeholder="$t('pages.admin.dhammas.label.selectStatus')" style="width: 100%" :loading="loading">
          <el-option v-for="item in source.statusList" :key="item.label" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
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
import InputTag from 'vue-input-tag'

export default {
  components: {
    InputTag
  },

  props: ['dialogFormVisible', 'id', 'hideEditForm'],

  data() {
    return {
      title: '',
      loading: false,
      form: {
        title: '',
        author: '',
        seokeyword: '',
        seodescription: '',
        status: ''
      },
      source: {}
    }
  },
  methods: {
    handleClose() {
      this.hideEditForm()
    },
    handleOpen() {
      this.title = "Edit dhamma #" + this.id
      this.fetchSourceAction(), this.fetchDhammaInfo()
    },
    handleSubmit() {
      this.$refs.form.validate(async valid => {
        if (valid) {
          this.loading = true

          this.form.seokeyword = this.form.seokeyword.join(',')
          await this.$store
            .dispatch('dhammas/update', {
              authToken: this.$store.state.authToken,
              id: this.$props.id,
              formData: this.form
            })
            .then(res => {
              this.loading = false

              this.$message({
                showClose: true,
                message: this.$t('pages.admin.dhammas.msg.updateSuccess'),
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
        .dispatch('dhammas/get', {
          authToken: this.$store.state.authToken,
          id: this.$props.id
        })
        .then(res => {
          return this.mapProperty(res)
        })
    },
    async fetchSourceAction() {
      await this.$store
        .dispatch('dhammas/get_form_source', {
          authToken: this.$store.state.authToken
        })
        .then(() => {
          this.source = this.$store.state.dhammas.formSource.records
        })
        .catch(e => {
          this.error = true
        })
    },
    async fetchDhammaInfo() {
      await this.$store
        .dispatch('dhammas/get', {
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
      this.form.title = res.data.response.title
      this.form.author = res.data.response.author
      this.form.seokeyword = res.data.response.seokeyword.split(',')
      this.form.seodescription = res.data.response.seodescription
      this.form.status = res.data.response.status.value
    }
  }
}
</script>
