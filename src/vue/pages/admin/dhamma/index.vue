<template>
  <div>
    <el-col :span="24">
      <div class="filter-icon"><i class="el-icon-fa-first-order"></i></div>
      <breadcrumb :bc="bc" :totalItems="totalItems" />
      <div class="top-right-toolbar">
        <nuxt-link to="/admin/dhamma/create">
          <el-button type="text" icon="el-icon-plus">{{ $t('default.create') }}</el-button>
        </nuxt-link>
        <pagination :totalItems="totalItems" :currentPage="query.page" :recordPerPage="recordPerPage" />
      </div>
    </el-col>
    <el-col :span="24">
      <div class="filter-container">
        <filter-bar :data="source" />
      </div>
      <div class="panel-body">
        <el-row>
          <el-col :span="24" style="text-align: right;">
            <el-button size="mini" icon="el-icon-fa-refresh" @click="handleRefresh()">
              {{ $t('default.refresh') }}
            </el-button>
          </el-col>
        </el-row>
        <list-view :records="myDhammas.records" :formSource="formSource" v-show="loading === false" />
        <pagination :totalItems="totalItems" :currentPage="query.page" :recordPerPage="recordPerPage" />
        <span v-show="loading === true" class="loading">
          <i class="el-icon-fa-spinner el-icon-fa-spin"></i>
        </span>
      </div>
    </el-col>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import Breadcrumb from '~/components/admin/Breadcrumb'
import Pagination from '~/components/admin/Pagination'
import FilterBar from '~/components/admin/dhamma/FilterBar'
import ListView from '~/components/admin/dhamma/ListView'

export default {
  layout: 'admin',
  middleware: 'authenticated',

  components: {
    Breadcrumb,
    Pagination,
    FilterBar,
    ListView
  },

  head() {
    return {
      title: this.$t('pages.admin.dhammas.title'),
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: this.$t('pages.admin.dhammas.title')
        }
      ]
    }
  },

  data() {
    return {
      loading: false,
      bc: [
        {
          name: this.$t('pages.admin.dhammas.title'),
          link: '/admin/dhamma'
        },
        {
          name: this.$t('default.list'),
          link: ''
        }
      ],
      source: {}
    }
  },

  computed: mapState({
    formSource: state => state.dhammas.formSource.records,
    myDhammas: state => state.dhammas.data,
    totalItems: state => state.dhammas.totalItems,
    recordPerPage: state => state.dhammas.recordPerPage,
    query: state => state.dhammas.query
  }),

  mounted() {
    this.fetchDataAction()
  },

  methods: {
    async fetchDataAction() {
      this.loading = true

      await this.$store
        .dispatch('dhammas/get_all', {
          authToken: this.$store.state.authToken,
          query: this.$route.query
        })
        .then(() => {
          this.loading = false
        })
        .catch(e => {
          this.loading = false
        })

      await this.$store
        .dispatch('dhammas/get_form_source', {
          authToken: this.$store.state.authToken
        })
        .then(() => {
          this.source = this.$store.state.dhammas.formSource.records
        })
        .catch(e => {
          this.loading = false
        })
    },
    handleRefresh() {
      return this.fetchDataAction()
    }
  }
}
</script>
