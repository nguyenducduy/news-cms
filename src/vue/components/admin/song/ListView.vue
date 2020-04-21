<template>
  <transition-group name="el-zoom-in-top">
    <div v-if="!noContent" :key="1">
      <el-table ref="bulkSelected" :data="records" style="width: 100%" @selection-change="handleSelectionChange" :default-expand-all="true" row-key="id" class="music-table">
        <el-table-column type="selection"></el-table-column>
        <el-table-column type="expand">
          <template slot-scope="scope">
            <small>
              <strong>{{ scope.row.id }}</strong>
              &nbsp;|&nbsp;
              <strong>{{ scope.row.myid }}</strong>
              <span v-if="scope.row.nctkey.length > 0">
                &nbsp;|&nbsp;
                <strong>{{ scope.row.nctkey }}</strong>
              </span>
              <br/>
            </small>
            <small v-if="scope.row.downloadlink.length > 0">
              {{ $t('pages.admin.songs.label.downloadLink') }}:
              <a :href="scope.row.downloadlink" target="_blank">{{ scope.row.downloadlink }}</a>
                <br/>
            </small>
            <small>
              {{ $t('pages.admin.songs.label.listenLink') }}:
              <a :href="scope.row.listenlink" target="_blank">{{ scope.row.listenlink }}</a>
                <br/>
            </small>
            <small>
              {{ $t('pages.admin.songs.label.title') }}: <strong>{{ scope.row.title }}</strong>
              &nbsp;|&nbsp;
              {{ $t('pages.admin.songs.label.size') }}: <strong>{{ scope.row.size | formatSize }}</strong>
              &nbsp;|&nbsp;
              Bitrate: <strong>{{ scope.row.cbr | formatBitrate }}</strong>
              &nbsp;|&nbsp;
              {{ $t('pages.admin.songs.label.channel') }}: <strong>{{ scope.row.channel }}</strong>
            </small>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.songs.label.name')" width="250">
          <template slot-scope="scope">
            <text-editable
              :data="scope.row.name"
              :id="scope.row.id"
              store="songs"
              field="name">
            </text-editable>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.songs.label.artist')" width="220">
          <template slot-scope="scope">
            <text-editable
              :data="scope.row.artist"
              :id="scope.row.id"
              store="songs"
              field="artist">
            </text-editable>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.songs.label.genre')">
          <template slot-scope="scope">
            <text-editable
              :id="scope.row.id"
              :data="scope.row.genre"
              store="songs"
              field="genre">
            </text-editable>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.songs.label.status')">
          <template slot-scope="scope">
            <select-editable
              v-if="!noFormSource"
              :id="scope.row.id"
              :data="scope.row.status"
              :options="formSource.statusList"
              store="songs"
              field="status">
            </select-editable>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.songs.label.downloadstatus')">
          <template slot-scope="scope">
            <select-editable
              v-if="!noFormSource"
              :id="scope.row.id"
              :data="scope.row.downloadstatus"
              :options="formSource.downloadStatusList"
              store="songs"
              field="downloadstatus">
            </select-editable>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.songs.label.dateCreated')" width="180">
          <template slot-scope="scope">
            <small>{{ scope.row.humandatecreated }}</small>
          </template>
        </el-table-column>
        <el-table-column class-name="td-operation" width="200">
          <template slot-scope="scope">
              <el-button-group class="operation">
                <download-button
                  :downloadStatus="scope.row.downloadstatus.value"
                  :id="scope.row.id">
                </download-button>
                <audio-player
                  :sources="[scope.row.listenlink]"
                  :preload="false"
                  :html5="true"
                  style="padding: 0px 10px;margin-top: -3px;">
                </audio-player>
                <el-button icon="el-icon-edit" size="mini" @click="showEditForm(scope.row)"></el-button>
                <del-button :id="scope.row.id" targetStore="songs" />
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
      <i class="el-icon-fa-music"></i>
      <p>{{ $t('default.noContent') }}</p>
    </div>
    <edit-dialog-form :dialogFormVisible="dialogFormVisible" :hideEditForm="hideEditForm" :key="3" :id="editId"></edit-dialog-form>
  </transition-group>
</template>

<script>
import { isEmpty, startCase, toLower, find, indexOf } from 'lodash'
import DelButton from '~/components/admin/DelButton'
import DownloadButton from '~/components/admin/song/DownloadButton'
import TextEditable from '~/components/admin/TextEditable'
import SelectEditable from '~/components/admin/SelectEditable'
import EditDialogForm from '~/components/admin/song/EditDialogForm'
import AudioPlayer from '~/components/AudioPlayer'
const prettysize = require('prettysize')

export default {
  props: ['records', 'formSource'],

  components: {
    DelButton,
    DownloadButton,
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
      dialogFormVisible: false,
      editId: null,
      bulklist: [
        {
          value: 'delete',
          label: this.$t('pages.admin.songs.label.delete')
        },
        {
          value: 'enable',
          label: this.$t('pages.admin.songs.label.enable')
        },
        {
          value: 'disable',
          label: this.$t('pages.admin.songs.label.disable')
        },
        {
          value: 'download',
          label: this.$t('pages.admin.songs.label.download')
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
          .dispatch('songs/bulk', {
            authToken: this.$store.state.authToken,
            formData: {
              itemSelected: this.bulkSelected,
              actionSelected: this.bulkAction
            }
          })
          .then(async () => {
            let queryObject = assign({}, this.$route.query)

            await this.$store
              .dispatch('songs/get_all', {
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
      this.dialogFormVisible = true
      this.editId = row.id
    },
    hideEditForm() {
      this.dialogFormVisible = false
    }
  },

  filters: {
    formatSize(size) {
      return prettysize(size)
    },
    formatBitrate(bitrate) {
      return bitrate.toString().slice(0, -3) + ' Kb'
    }
  }
}
</script>

<style scoped>

</style>
