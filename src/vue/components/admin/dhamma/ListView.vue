<template>
  <transition-group name="el-zoom-in-top">
    <div v-if="!noContent" :key="1">
      <el-table ref="bulkSelected" :data="records" style="width: 100%" @selection-change="handleSelectionChange" row-key="id">
        <el-table-column type="selection"></el-table-column>
        <el-table-column :label="$t('pages.admin.dhammas.label.title')">
          <template slot-scope="scope">
            <text-editable
              :data="scope.row.title"
              :id="scope.row.id"
              store="dhammas"
              field="title">
            </text-editable>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.dhammas.label.author')">
          <template slot-scope="scope">
            <text-editable
              :data="scope.row.author"
              :id="scope.row.id"
              store="dhammas"
              field="author">
            </text-editable>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.dhammas.label.status')" width="160">
          <template slot-scope="scope">
            <select-editable
              v-if="!noFormSource"
              :id="scope.row.id"
              :data="scope.row.status"
              :options="formSource.statusList"
              store="dhammas"
              field="status">
            </select-editable>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.dhammas.label.dateCreated')" width="180">
          <template slot-scope="scope">
            <small>{{ scope.row.humandatecreated }}</small>
          </template>
        </el-table-column>
        <el-table-column class-name="td-operation" width="200">
          <template slot-scope="scope">
              <el-button-group class="operation">
                <audio-player
                  :sources="[scope.row.filepath]"
                  :preload="true"
                  :html5="true"
                  style="padding: 0px 10px;margin-top: -3px;">
                </audio-player>
                <el-button icon="el-icon-edit" size="mini" @click="showEditForm(scope.row)"></el-button>
                <del-button :id="scope.row.id" targetStore="dhammas" />
              </el-button-group>
          </template>
        </el-table-column>
      </el-table>
      <div style="margin-top: 20px">
        <el-select v-model="bulkAction" :placeholder="$t('default.selectAction')" size="small">
          <el-option v-for="item in bulklist" :key="item.value" :label="item.label" :value="item.value" size="small">
          </el-option>
        </el-select>
        <el-button style="margin-left: 10px" type="primary" size="small" @click="bulkSubmitHandle">{{ $t('default.submit') }}</el-button>
      </div>
      <scroll-to-top :duration="1000" :timing="'ease'"></scroll-to-top>
    </div>
    <div v-else class="no-content" :key="2">
      <i class="el-icon-fa-centercode"></i>
      <p>{{ $t('default.noContent') }}</p>
    </div>
    <edit-dialog-form :dialogFormVisible="editDialogFormVisible" :hideEditForm="hideEditForm" :key="3" :id="editId"></edit-dialog-form>
  </transition-group>
</template>

<script>
import { isEmpty, startCase, toLower, find, indexOf } from 'lodash'
import DelButton from '~/components/admin/DelButton'
import TextEditable from '~/components/admin/TextEditable'
import SelectEditable from '~/components/admin/SelectEditable'
import EditDialogForm from '~/components/admin/dhamma/EditDialogForm'
import AudioPlayer from '~/components/AudioPlayer'

export default {
  props: ['records', 'formSource'],

  components: {
    DelButton,
    TextEditable,
    SelectEditable,
    EditDialogForm,
    AudioPlayer
  },

  computed: {
    noContent() {
      return isEmpty(this.$props.records)
    },
    noFormSource() {
      return isEmpty(this.$props.formSource)
    }
  },

  data() {
    return {
      editDialogFormVisible: false,
      editId: null,
      bulklist: [
        {
          value: 'delete',
          label: this.$t('pages.admin.dhammas.label.delete')
        },
        {
          value: 'enable',
          label: this.$t('pages.admin.dhammas.label.enable')
        },
        {
          value: 'disable',
          label: this.$t('pages.admin.dhammas.label.disable')
        }
      ],
      bulkSelected: [],
      bulkAction: ''
    }
  },

  methods: {
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
          .dispatch('dhammas/bulk', {
            authToken: this.$store.state.authToken,
            formData: {
              itemSelected: this.bulkSelected,
              actionSelected: this.bulkAction
            }
          })
          .then(async () => {
            let queryObject = assign({}, this.$route.query)

            await this.$store
              .dispatch('dhammas/get_all', {
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
    },
    showEditForm(row) {
      this.editDialogFormVisible = true
      this.editId = row.id
    },
    hideEditForm() {
      this.editDialogFormVisible = false
    }
  }
}
</script>
