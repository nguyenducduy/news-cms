<template>
  <el-dialog
    :visible="showEditDialog"
    v-on:open="onOpen"
    v-on:close="onClose"
    :before-close="hideEditForm"
    width="90%"
    top="0"
    :lock-scroll="true"
  >
  <template slot="title">
    <h3>{{ title }}</h3>
    <div class="last_edited" v-if="revision.length > 0">
      <i v-html="`${$t('pages.admin.newss.label.lastEdited')} ${revision[0].humandatecreated} ${$t('pages.admin.newss.label.by')} <strong>${revision[0].uname}</strong>`"></i>
    </div>
  </template>
    <el-row :gutter="30">
      <el-col :span="16">
        <div class="intrinsic-container intrinsic-container-16x9">
          <iframe :src="scope.link" allowfullscreen></iframe>
        </div>
      </el-col>
      <el-col :span="8">
        <el-form autoComplete="on" label-position="left" class="edit-form" :model="form" :rules="rules" ref="form">
          <el-form-item prop="cid" :label="$t('pages.admin.newss.label.category')" size="small">
            <el-select v-model="form.cid" :placeholder="$t('pages.admin.newss.label.selectCategory')" style="width: 100%">
              <el-option v-for="item in source.categoryList" :key="item.value" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item prop="title" :label="$t('pages.admin.newss.label.title')">
            <el-input type="textarea" :autosize="{ minRows: 2, maxRows: 4}" size="small" v-model="form.title"></el-input>
          </el-form-item>
          <el-form-item prop="description" :label="$t('pages.admin.newss.label.description')">
            <el-input type="textarea" :autosize="{ minRows: 6, maxRows: 10}" v-model="form.description" size="small"></el-input>
          </el-form-item>
          <el-form-item prop="keywords" :label="$t('pages.admin.newss.label.keywords')">
            <input-tag :tags.sync="form.keywords" style="width: 93%"></input-tag>
          </el-form-item>
          <el-form-item style="margin-top: 30px">
            <el-button size="small" type="success" v-if="currentUser.sub.groupid === 'administrator'" :loading="loading"  @click.native.prevent="handlePublish">Publish</el-button>
            <el-button size="small" type="primary" :loading="loading" @click.native.prevent="handleSubmit"> {{ $t('default.update') }}
            </el-button>
            <el-button size="small" type="danger" @click="resetField">{{ $t('default.reset') }}</el-button>
            <el-button size="small" @click="hideEditForm">{{ $t('default.cancel') }}</el-button>
          </el-form-item>
        </el-form>

        <!-- REVIEWER -->
        <section class="review_container" v-show="showReview">
          <h5><span>Review</span></h5>
          <el-form :model="reviewForm" ref="reviewForm" :rules="reviewRules">
            <el-form-item prop="status">
              <el-checkbox-group v-model="reviewForm.status">
                <el-col :span="24" v-for="rs in source.reviewStatusList" :key="rs.value">
                  <el-checkbox :label="rs.value" :key="rs.value" name="status">
                    {{ rs.label }}
                  </el-checkbox>
                </el-col>
              </el-checkbox-group>
            </el-form-item>
            <el-form-item prop="comment" :label="$t('pages.admin.newss.label.comment')">
              <el-input type="textarea" :autosize="{ minRows: 3, maxRows: 5}" v-model="reviewForm.comment" size="small"></el-input>
            </el-form-item>
            <el-form-item style="margin-top: 30px">
              <el-button size="small" type="primary" :loading="loading" @click.native.prevent="handleReviewSubmit"> {{ $t('default.create') }}
              </el-button>
            </el-form-item>
          </el-form>
        </section>

        <!-- REVISION -->
        <section class="revision_container" v-if="revision.length > 0">
          <h5><span>{{ $t('pages.admin.newss.label.revision') }}</span></h5>
          <div class="revision_item" v-for="rev in revision">
            <el-row>
              <el-col :span="16">
                <el-popover placement="left" trigger="hover">
                  <div v-html="diffRender(rev.diffcontent)"></div>
                  <el-button type="text" slot="reference" class="btn-rev">
                    <abbr>
                      Rev {{ rev.num }}
                    </abbr>
                  </el-button>
                </el-popover>
                {{ rev.uname }}
              </el-col>
              <el-col :span="8" style="text-align: right;" v-if="rev.num !== currev">
                <el-button size="small" type="text" @click.native.prevent="handleUseRev(rev.id)" v-if="currentUser.sub.groupid === 'administrator'">
                  {{ $t('pages.admin.newss.label.useThisRev') }}
                </el-button>
              </el-col>
              <el-col :span="8" style="text-align: right;" v-else>
                <el-tag size="mini" type="warning">
                  {{ $t('pages.admin.newss.label.currentRev') }}
                </el-tag>
              </el-col>
            </el-row>
          </div>
        </section>

        <!-- HISTORY -->
        <section class="timeline" v-if="history.length > 0">
          <h5><span>{{ $t('pages.admin.newss.label.history') }}</span></h5>
          <ul>
            <li v-for="his in history">
              <div>
                <el-tooltip placement="top-start" :enterable="false" :offset="10">
                    <div slot="content">
                      {{ his.content }}
                    </div>
                    <el-tag size="mini" :type="his.type.style">{{ his.type.label }}</el-tag>
                </el-tooltip>
                {{ $t('pages.admin.newss.label.by') }}
                <strong>{{ his.uname }}</strong>
                {{ $t('pages.admin.newss.label.at') }}
                <i>{{ his.humandatecreated }}</i> &nbsp;

              </div>
            </li>
          </ul>
        </section>
      </el-col>
    </el-row>
  </el-dialog>
</template>

<script>
import InputTag from 'vue-input-tag'
import { Diff2Html } from 'diff2html'
import { find } from 'lodash'

export default {
  components: {
    InputTag
  },

  props: ['scope', 'showEditDialog', 'hideEditForm'],

  data() {
    return {
      showReview: true,
      loading: false,
      reviewForm: {
        status: [],
        comment: null
      },
      reviewRules: {
        status: [
          {
            type: 'array',
            required: true,
            message: this.$t('pages.admin.newss.msg.statusIsRequired'),
            trigger: 'change'
          }
        ]
      },
      form: {
        cid: '',
        title: '',
        description: '',
        keywords: ''
      },
      rules: {
        title: [
          {
            required: true,
            message: this.$t('pages.admin.newss.msg.titleIsRequired'),
            trigger: 'blur'
          }
        ],
        description: [
          {
            required: true,
            message: this.$t('pages.admin.newss.msg.descriptionIsRequired'),
            trigger: 'blur'
          }
        ],
        keywords: [
          {
            required: true,
            message: this.$t('pages.admin.newss.msg.keywordsIsRequired'),
            trigger: 'blur'
          }
        ]
      },
      source: {},
      revision: [],
      review: [],
      history: [],
      currev: null,
      userEditors: [],
      userReviews: []
    }
  },

  computed: {
    title() {
      return `${this.$t('pages.admin.newss.label.editNewsNum')} #${this.$props.scope.id}`
    },
    currentUser() {
      return this.$store.getters.loggedUser
    }
  },

  methods: {
    diffRender(diffcontent) {
      return `
        ${Diff2Html.getPrettyHtml(diffcontent.category, {inputFormat: "diff", matching: "words"})}
        ${Diff2Html.getPrettyHtml(diffcontent.title, {inputFormat: "diff", matching: "words"})}
        ${Diff2Html.getPrettyHtml(diffcontent.description, {inputFormat: "diff", matching: "words"})}
        ${Diff2Html.getPrettyHtml(diffcontent.keywords, {inputFormat: "diff", matching: "words"})}
      `
    },
    onOpen() {
      this.fetchSourceAction(),
      this.fetchNewsInfo()
    },
    onClose() {
      this.userEditors = []
      this.userReviews = []
      this.revision = []
      this.review = []
      this.history = []
      this.showReview = true
    },
    async handleUseRev(revisionId) {
      this.loading = true
      await this.$store
        .dispatch('newss/use_rev', {
          authToken: this.$store.state.authToken,
          id: this.$props.scope.id,
          formData: {
            id: revisionId
          }
        })
        .then(res => {
          this.loading = false

          this.$message({
            showClose: true,
            message: this.$t('pages.admin.newss.msg.useRevSuccess'),
            type: 'success',
            duration: 5 * 1000
          })

          // Loading new state
          this.fetchNewsInfo()
        })
        .catch(e => {
          this.loading = false
        })
    },
    async handlePublish() {
      this.loading = true
      await this.$store
        .dispatch('newss/publish', {
          authToken: this.$store.state.authToken,
          id: this.$props.scope.id
        })
        .then(res => {
          this.loading = false

          this.$message({
            showClose: true,
            message: this.$t('pages.admin.newss.msg.publishSuccess'),
            type: 'success',
            duration: 5 * 1000
          })
        })
        .catch(e => {
          this.loading = false
        })
    },
    handleReviewSubmit() {
      this.$refs.reviewForm.validate(async valid => {
        if (valid) {
          this.loading = true
          await this.$store
            .dispatch('newss/add_review', {
              authToken: this.$store.state.authToken,
              id: this.$props.scope.id,
              formData: this.reviewForm
            })
            .then(res => {
              this.loading = false

              this.$message({
                showClose: true,
                message: this.$t('pages.admin.newss.msg.addReviewSuccess'),
                type: 'success',
                duration: 5 * 1000
              })

              this.$refs.reviewForm.resetFields()

              // Loading new state
              this.fetchNewsInfo()
            })
            .catch(e => {
              this.loading = false
            })
        } else {
          return false
        }
      })
    },
    handleSubmit() {
      this.$refs.form.validate(async valid => {
        if (valid) {
          this.loading = true
          await this.$store
            .dispatch('newss/update', {
              authToken: this.$store.state.authToken,
              id: this.$props.scope.id,
              formData: this.form
            })
            .then(res => {
              this.loading = false

              this.$message({
                showClose: true,
                message: this.$t('pages.admin.newss.msg.updateSuccess'),
                type: 'success',
                duration: 5 * 1000
              })

              // Loading new state
              this.fetchNewsInfo()
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
        .dispatch('newss/get', {
          authToken: this.$store.state.authToken,
          id: this.$props.scope.id
        })
        .then(res => {
          return this.mapProperty(res)
        })
    },
    async fetchSourceAction() {
      await this.$store
        .dispatch('newss/get_form_source', {
          authToken: this.$store.state.authToken
        })
        .then(() => {
          this.source = this.$store.state.newss.formSource.records
        })
        .catch(e => {
          this.error = true
        })
    },
    async fetchNewsInfo() {
      await this.$store
        .dispatch('newss/get', {
          authToken: this.$store.state.authToken,
          id: this.$props.scope.id
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
      this.form.description = res.data.response.description
      this.form.keywords = res.data.response.keywords.split(',')
      this.form.cid = res.data.response.category.value
      this.currev = res.data.response.currev
      this.revision = res.data.response.revision.data
      this.review = res.data.response.review.data
      this.history = res.data.response.history.data

      if (this.revision.length > 0) {
        this.revision.map(rev => {
          this.userEditors.push({
            uid: rev.uid,
            num: rev.num
          })
        })
      }

      if (this.review.length > 0) {
        this.review.map(revi => {
          this.userReviews.push({
            uid: revi.uid,
            num: revi.num
          })
        })
      }

      const user = this.$store.getters.loggedUser

      // Check if user is edited this revision, dont't show review block
      const isEditor = find(this.userEditors, function(obj) {
        return user.sub.id === obj.uid && res.data.response.currev === obj.num
      })

      // Check if user is reviewed this revision, dont't show review block
      const isReviewer = find(this.userReviews, function(obj) {
        return user.sub.id === obj.uid && res.data.response.currev === obj.num
      })

      if (typeof isEditor === 'object') {
        this.showReview = false
      } else if (typeof isReviewer === 'object' && typeof isEditor === 'undefined') {
        this.showReview = false
      } else if (typeof isReviewer === 'undefined' && typeof isEditor === 'object') {
        this.showReview = false
      } else if (user.sub.groupid === 'administrator') {
        this.showReview = true
      }
    }
  }
}
</script>
<style scoped>
iframe {
  border: none;
}
.intrinsic-container {
  position: relative;
  height: 0;
  overflow: hidden;
  width: 100%;
  padding: 8px
}

/* 16x9 Aspect Ratio */
.intrinsic-container-16x9 {
  padding-bottom: 100%;
}

/* 4x3 Aspect Ratio */
.intrinsic-container-4x3 {
  padding-bottom: 100%;
}

.intrinsic-container iframe {
  position: absolute;
  top:0;
  left: 0;
  width: 100%;
  height: 100%;
}
.last_edited {
  float: right;
  position: absolute;
  right: 0;
  top: 30px;
  margin-right: 35px;
}
.btn-rev {
  padding: 8px;
}
.review_container, .revision_container {
  margin-top: 17px;
  padding-top: 10px;
}

.timeline {
  background-color: #fff;
  margin-top: 17px;
}
.timeline ul {
  -webkit-padding-start: 0
}
.timeline ul li {
  list-style-type: none;
  position: relative;
  width: 1px;
  margin: 0 10px;
  padding-top: 0px;
  background: #F56C6C;
}

.timeline ul li::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: 0;
  transform: translateX(-50%);
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: inherit;
}

.timeline ul li div {
  position: relative;
  bottom: -10px;
  width: 370px;
  padding: 5px;
  background: #fff;
  /* border: 1px solid #ddd; */
}

.timeline ul li div::before {
  content: '';
  position: absolute;
  bottom: 12px;
  width: 0;
  height: 0;
  border-style: solid;
}

.timeline ul li div {
  left: 25px;
}

.timeline ul li div::before {
  left: -10px;
  border-width: 2px 7px 3px 0;
  border-color: transparent #f56c6c4d transparent transparent;
}

h5 {
 width: 100%;
 text-align: center;
 border-bottom: 1px solid #ededed;
 line-height: 0.1em;
 margin: 10px 0 20px;
}

h5 span {
  background: #fff;
  padding:0 10px;
}
</style>
