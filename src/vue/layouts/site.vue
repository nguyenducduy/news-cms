<template>
  <el-row class="site_main_container">
    <!-- Container -->
    <el-col :span="18" :offset="3" class="container">
      <nuxt :key="$route.fullPath" />
    </el-col>
  </el-row>
</template>
