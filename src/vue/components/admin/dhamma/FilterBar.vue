<template>
  <el-form ref="form" :model="form" label-position="top">
    <el-form-item prop="keyword">
      <el-input size="small" :placeholder="$t('pages.admin.dhammas.label.search')"
        v-model="form.keyword"
        @keyup.enter.native="handleFilter"
        @click.native="setSearchWidth('focus')"
        @blur="setSearchWidth('blur')"
        :style="{ width: searchInputWidth + '%' }"
        clearable>
        <el-button slot="append" @click="handleFilter"><i class="el-icon-fa-search"></i></el-button>
      </el-input>
    </el-form-item>
    <el-form-item prop="status" :label="$t('pages.admin.dhammas.label.status')">
      <el-select clearable size="small" v-model="form.status" :placeholder="$t('default.all')">
        <el-option v-for="item in data.statusList" :key="item.value" :label="item.label" :value="item.value">
        </el-option>
      </el-select>
    </el-form-item>
    <el-form-item>
      <el-button type="primary" size="small" @click="handleFilter">{{ $t('default.filter') }}</el-button>
      <el-button size="small" @click="resetFilter">{{ $t('default.reset') }}</el-button>
    </el-form-item>
  </el-form>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: ['data'],

  data() {
    return {
      searchInputWidth: 100,
      form: {
        keyword: this.$route.query.keyword || '',
        status: this.$route.query.status || ''
      }
    }
  },

  computed: mapState({
    query: state => state.songs.query
  }),

  methods: {
    handleFilter() {
      const querystring = require('querystring')
      this.query.page = 1
      const pageUrl = `?${querystring.stringify(
        this.form
      )}&${querystring.stringify(this.query)}`

      return this.$router.push(pageUrl)
    },
    resetFilter() {
      return this.$router.push('/admin/dhamma')
    },
    setSearchWidth(type) {
      switch (type) {
        case 'focus':
          this.searchInputWidth = 250
          break
        case 'blur':
          this.searchInputWidth = 100
          break
      }
    }
  }
}
</script>
