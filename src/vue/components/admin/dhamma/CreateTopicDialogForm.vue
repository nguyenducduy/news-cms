<template>
  <el-dialog
    :title="title"
    :visible="dialogFormVisible"
    width="30%"
    v-on:open="handleOpen"
    :before-close="hideCreateForm"
    >
    <el-form autoComplete="on" label-position="left" class="edit-form" :model="form" :rules="rules" ref="form">
      <el-form-item prop="name" :label="$t('pages.admin.dhammas.label.topic.name')">
        <el-input type="text" v-model="form.name" size="small"></el-input>
      </el-form-item>
      <el-form-item prop="description" :label="$t('pages.admin.dhammas.label.topic.description')">
        <el-input type="textarea" :autosize="{ minRows: 3, maxRows: 6}" v-model="form.description" size="small"></el-input>
      </el-form-item>
      <el-form-item prop="status" :label="$t('pages.admin.dhammas.label.status')">
        <el-select size="small" v-model="form.status" :placeholder="$t('pages.admin.dhammas.label.selectStatus')" style="width: 100%" :loading="loading">
          <el-option v-for="item in source.statusList" :key="item.label" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
      </el-form-item>
      <el-form-item prop="displayorder" :label="$t('pages.admin.dhammas.label.topic.displayorder')">
        <el-input type="text" v-model="form.displayorder" size="small"></el-input>
      </el-form-item>
    </el-form>
    <span slot="footer" class="dialog-footer">
      <el-button size="small" type="primary" :loading="loading" @click.native.prevent="handleSubmit"> {{ $t('default.create') }}
      </el-button>
      <el-button size="small" @click="hideCreateForm">{{ $t('default.cancel') }}</el-button>
    </span>
  </el-dialog>
</template>

<script>
export default {
  props: ['dialogFormVisible', 'hideCreateForm'],

  data() {
    return {
      title: '',
      loading: false,
      form: {
        name: '',
        description: '',
        status: '',
        displayorder: ''
      },
      rules: {
        name: [
          {
            required: true,
            message: this.$t('pages.admin.dhammas.msg.topicNameIsRequired'),
            trigger: 'blur'
          }
        ],
        status: [
          {
            required: true,
            message: this.$t('pages.admin.dhammas.msg.statusIsRequired'),
            trigger: 'change'
          }
        ]
      },
      source: {}
    }
  },
  methods: {
    handleOpen() {
      this.title = this.$t('default.create')
      this.fetchSourceAction()
    },
    handleSubmit() {
      this.$refs.form.validate(async valid => {
        if (valid) {
          this.loading = true

          await this.$store
            .dispatch('topics/create', {
              authToken: this.$store.state.authToken,
              id: this.$props.id,
              formData: this.form
            })
            .then(res => {
              this.loading = false

              this.$message({
                showClose: true,
                message: this.$t('pages.admin.dhammas.msg.topicCreateSuccess'),
                type: 'success',
                duration: 5 * 1000
              })
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

    },
    async fetchSourceAction() {
      await this.$store
        .dispatch('topics/get_form_source', {
          authToken: this.$store.state.authToken
        })
        .then(() => {
          this.source = this.$store.state.topics.formSource.records
        })
        .catch(e => {
          this.error = true
        })
    }
  }
}
</script>
