<template>
  <div class="panel-body">
    <el-col :span="4">
      <h2>{{ $t('pages.admin.dhammas.create.description') }}</h2>
      <p>{{ $t('pages.admin.dhammas.create.extraDescription') }}</p>
    </el-col>
    <el-col :span="20">
      <el-col :span="8">
        <el-form autoComplete="on" label-position="left" class="create-form" :model="form" :rules="rules" ref="form">
          <el-form-item prop="topic">
            <label>
              {{ $t('pages.admin.dhammas.label.topicname') }}
              <el-button type="text" icon="el-icon-plus" @click.prevent="showCreateTopicForm()" style="float: right">{{ $t('default.create') }}</el-button>
            </label>
            <el-input type="text" size="small" v-model="form.title"></el-input>
          </el-form-item>
          <el-form-item prop="title" :label="$t('pages.admin.dhammas.label.title')">
            <el-input type="text" size="small" v-model="form.title"></el-input>
          </el-form-item>
          <el-form-item prop="author" :label="$t('pages.admin.dhammas.label.author')">
            <el-input type="text" size="small" v-model="form.author"></el-input>
          </el-form-item>
          <el-form-item prop="seokeyword" :label="$t('pages.admin.dhammas.label.seokeyword')">
            <input-tag :tags.sync="form.seokeyword" style="width: 93%"></input-tag>
          </el-form-item>
          <el-form-item prop="seodescription" :label="$t('pages.admin.dhammas.label.seodescription')">
            <el-input size="small" type="textarea" :autosize="{ minRows: 3, maxRows: 6}" v-model="form.seodescription"></el-input>
          </el-form-item>

          <el-form-item prop="status" :label="$t('pages.admin.dhammas.label.status')">
            <el-select size="small" v-model="form.status" :placeholder="$t('pages.admin.dhammas.label.selectStatus')" style="width: 100%" :loading="loading">
              <el-option v-for="item in source.statusList" :key="item.label" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-upload
              ref="upload"
              action=""
              accept="audio/mp3"
              :multiple="false"
              :auto-upload="false"
              :with-credentials="true"
              :file-list="myFiles"
              :on-change="handleChange"
              :on-remove="handleRemove">
              <el-button slot="trigger" size="small" type="warning">
                {{ $t('pages.admin.dhammas.label.selectFiles') }}
              </el-button>
            </el-upload>
          </el-form-item>

          <el-form-item style="margin-top: 30px">
            <el-button type="primary" :loading="loading" @click.native.prevent="handleSubmit"> {{ $t('default.create') }}
            </el-button>
            <el-button @click="resetField">{{ $t('default.reset') }}</el-button>
          </el-form-item>
        </el-form>
      </el-col>
    </el-col>
    <create-topic-dialog-form
      :dialogFormVisible="createTopicDialogFormVisible"
      :hideCreateForm="hideCreateTopicForm"></create-topic-dialog-form>
  </div>
</template>

<script>
import InputTag from 'vue-input-tag'
import CreateTopicDialogForm from '~/components/admin/dhamma/CreateTopicDialogForm'

export default {
  components: {
    InputTag,
    CreateTopicDialogForm
  },

  data() {
    return {
      createTopicDialogFormVisible: false,
      loading: false,
      myFiles: [],
      form: {
        title: '',
        author: '',
        seokeyword: [],
        seodescription: '',
        status: '',
        topic: ''
      },
      rules: {
        title: [
          {
            required: true,
            message: this.$t('pages.admin.dhammas.msg.titleIsRequired'),
            trigger: 'blur'
          }
        ],
        author: [
          {
            required: true,
            message: this.$t('pages.admin.dhammas.msg.authorIsRequired'),
            trigger: 'blur'
          }
        ],
        seokeyword: [
          {
            required: true,
            message: this.$t('pages.admin.dhammas.msg.seokeywordIsRequired'),
            trigger: 'blur'
          }
        ],
        seodescription: [
          {
            required: true,
            message: this.$t('pages.admin.dhammas.msg.seodescriptionIsRequired'),
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
    handleChange(file, fileList) {
      this.myFiles = fileList
      if (fileList.length > 1) {
        this.myFileList = fileList.shift()
      }

      this.form.myFileList = fileList
    },
    handleRemove(file, fileList) {
      this.myFiles = fileList
      this.myFileList = fileList.pop()
    },
    handleSubmit() {
      this.$refs.form.validate(async valid => {
        if (valid) {
          this.loading = true

          await this.$store
            .dispatch('dhammas/create', {
              authToken: this.$store.state.authToken,
              formData: this.form,
              files: this.myFiles
            })
            .then(res => {
              this.loading = false

              const msg = this.$message({
                showClose: true,
                message: this.$t('pages.admin.dhammas.msg.createSuccess'),
                type: 'success',
                duration: 5 * 1000
              })

              // reset form
              this.$refs.upload.clearFiles()
              this.$refs.form.resetFields()
            })
            .catch(e => {
              this.loading = false
            })
        } else {
          return false
        }
      })
    },
    resetField() {
      this.$refs.form.resetFields()
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
    showCreateTopicForm(row) {
      this.createTopicDialogFormVisible = true
    },
    hideCreateTopicForm() {
      this.createTopicDialogFormVisible = false
    }
  },

  mounted() {
    this.fetchSourceAction()
  }
}
</script>
