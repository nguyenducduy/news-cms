<template>
  <div>
    <el-col :span="24">
      <div class="filter-icon"><i class="el-icon-fa-newspaper-o"></i></div>
      <breadcrumb :bc="bc" :totalItems="totalItems" />
      <div class="top-right-toolbar">
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
        <list-view :records="myNewss.records" v-show="loading === false" />
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
import FilterBar from '~/components/admin/news/FilterBar'
import ListView from '~/components/admin/news/ListView'

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
      title: this.$t('pages.admin.newss.title'),
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: this.$t('pages.admin.newss.title')
        }
      ]
    }
  },

  data() {
    return {
      loading: false,
      bc: [
        {
          name: this.$t('pages.admin.newss.title'),
          link: '/admin/news'
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
    myNewss: state => state.newss.data,
    totalItems: state => state.newss.totalItems,
    recordPerPage: state => state.newss.recordPerPage,
    query: state => state.newss.query
  }),

  mounted() {
    this.fetchDataAction()
  },

  methods: {
    async fetchDataAction() {
      this.loading = true
      await this.$store
        .dispatch('newss/get_all', {
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
        .dispatch('newss/get_form_source', {
          authToken: this.$store.state.authToken
        })
        .then(() => {
          this.source = this.$store.state.newss.formSource.records
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
