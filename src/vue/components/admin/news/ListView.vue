<template>
  <transition-group name="el-zoom-in-top">
    <div v-if="!noContent" :key="1">
      <el-table ref="bulkSelected" :data="records" style="width: 100%" @selection-change="handleSelectionChange" row-key="id">
        <el-table-column type="selection" v-if="$store.getters.loggedUser.sub.groupid === 'administrator'"></el-table-column>
        <el-table-column :label="$t('pages.admin.newss.label.category')" width="90" :show-overflow-tooltip="true">
          <template slot-scope="scope">
            <el-tag size="small" type="primary">
              {{ scope.row.category.label }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.newss.label.title')">
          <template slot-scope="scope">
            <el-tooltip placement="top-start" :enterable="false" :visible-arrow="false" :offset="10">
                <div slot="content">{{ scope.row.description }}</div>
                <span>
                  {{ scope.row.title }}
                </span>
            </el-tooltip>
            <br/>
            <div style="margin-top: 4px;">
              <el-tag size="mini" type="success" v-for="(keyword, index) in scope.row.keywords.split(',')" :key="index">{{ keyword }}</el-tag>
            </div>
            <a :href="scope.row.link" target="_blank" class="external-link-btn">
              <i class="el-icon-fa-external-link"></i>
            </a>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.newss.label.source')" width="80" align="center" :show-overflow-tooltip="true">
          <template slot-scope="scope">
            <small><strong>{{ scope.row.source }}</strong></small>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.newss.label.status')" width="120" align="center">
          <template slot-scope="scope">
            <el-tag :type="scope.row.status.style" size="small">{{ scope.row.status.label }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Editor" width="100" align="center">
          <template slot-scope="scope">
            <div v-for="editor in scope.row.editors" v-if="scope.row.editors.length > 0" class="avatar">
              <el-tooltip placement="top">
                <div slot="content">
                  {{ editor.uname }}
                </div>
                <img v-if="editor.uavatar !== ''" :src="editor.uavatar" width="30" height="30">
                <img v-else src="/img/default_avatar.png" width="30" height="30">
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.newss.label.dateCreatedPublished')" width="130" align="center">
          <template slot-scope="scope">
            <small>
              {{ scope.row.humandatecreated }}
              <br/>
              {{ scope.row.humandatepublished }}
            </small>
          </template>
        </el-table-column>
        <el-table-column class-name="td-operation" width="120" align="right">
          <template slot-scope="scope">
              <el-button-group class="operation">
                <el-button icon="el-icon-edit" size="mini" @click="toggleEditForm(scope.row)" v-if="(scope.row.status.value !== '1' && $store.getters.loggedUser.sub.groupid === 'editor') || $store.getters.loggedUser.sub.groupid === 'administrator'"></el-button>
                <del-button :id="scope.row.id" targetStore="newss" v-if="$store.getters.loggedUser.sub.groupid === 'administrator'" />
              </el-button-group>
          </template>
        </el-table-column>
      </el-table>
      <div style="margin-top: 20px">
        <el-select v-model="bulkAction" :placeholder="$t('default.selectAction')" size="small" :disabled="$store.getters.loggedUser.sub.groupid !== 'administrator'">
          <el-option v-for="item in bulklist" :key="item.value" :label="item.label" :value="item.value" size="small">
          </el-option>
        </el-select>
        <el-button style="margin-left: 10px" type="primary" size="small" @click="bulkSubmitHandle" :disabled="$store.getters.loggedUser.sub.groupid !== 'administrator'">{{ $t('default.submit') }}</el-button>
      </div>
      <scroll-to-top :duration="1000" :timing="'ease'"></scroll-to-top>
    </div>
    <div v-else class="no-content" :key="2">
      <i class="el-icon-fa-newspaper"></i>
      <p>{{ $t('default.noContent') }}</p>
    </div>
    <edit-dialog-form :showEditDialog="showEditDialog" :hideEditForm="toggleEditForm" :key="3" :scope="editScope"></edit-dialog-form>
  </transition-group>
</template>

<script>
import { isEmpty, startCase, toLower, find, indexOf } from 'lodash'
import DelButton from '~/components/admin/DelButton'
import EditDialogForm from '~/components/admin/news/EditDialogForm'

export default {
  props: ['records'],

  components: {
    DelButton,
    EditDialogForm
  },

  computed: {
    noContent() {
      return isEmpty(this.$props.records)
    }
  },

  data() {
    return {
      showEditDialog: false,
      editScope: 0,
      bulklist: [
        {
          value: 'delete',
          label: this.$t('pages.admin.users.label.delete')
        },
        {
          value: 'enable',
          label: this.$t('pages.admin.users.label.enable')
        },
        {
          value: 'disable',
          label: this.$t('pages.admin.users.label.disable')
        }
      ],
      bulkSelected: [],
      bulkAction: ''
    }
  },
  methods: {
    toggleEditForm(row) {
      this.showEditDialog = !this.showEditDialog
      this.editScope = row
    },
    handleSelectionChange(val) {
      this.bulkSelected = val
    },
    async bulkSubmitHandle() {
      if (this.bulkSelected.length === 0) {
        this.$message({
          showClose: true,
          message: this.$t('default.msg.noItemSelected'),
          type: 'warning',
          duration: 5 * 1000
        })
      } else if (this.bulkAction === '') {
        this.$message({
          showClose: true,
          message: this.$t('default.msg.noActionChosen'),
          type: 'warning',
          duration: 5 * 1000
        })
      } else {
        await this.$store
          .dispatch('newss/bulk', {
            authToken: this.$store.state.authToken,
            formData: {
              itemSelected: this.bulkSelected,
              actionSelected: this.bulkAction
            }
          })
          .then(async () => {
            let queryObject = assign({}, this.$route.query)

            await this.$store
              .dispatch('newss/get_all', {
                authToken: this.$store.state.authToken,
                query: queryObject
              })
              .then(() => {
                this.$message({
                  showClose: true,
                  message: `${startCase(
                    toLower(this.bulkAction)
                  )} ${this.$t('default.msg.deleteSuccess')}`,
                  type: 'success',
                  duration: 5 * 1000
                })
              })
          })
      }
    }
  }
}
</script>

<style scoped lang="scss">
.external-link-btn {
  float: right;
  position: absolute;
  top: 8px;
  right: 0;
  color: #2d9fbb;
}
.el-tag {
  margin-right: 4px;
}
.avatar {
  margin: 0 auto;
  float: left;
  display: inline-block;
  padding-left: 4px;
  img {
    border-radius: 30px !important;
  }
}
</style>
