<template>
  <div>
    <el-row class="header">
      <el-col :span="24">
        <navigation />
      </el-col>
    </el-row>
    <el-row class="container">
      <el-col :span="24">
        <nuxt :key="$route.fullPath" />
      </el-col>
      <el-col :span="24" class="footer">MK Software &#169; 2017</el-col>
    </el-row>
  </div>
</template>

<script>
import Navigation from "~/components/admin/Navigation";

export default {
  components: {
    Navigation
  }
};
</script>

<style>
.footer {
  padding: 12px 30px 9px 30px;
  text-align: right;
  background-color: #ecf0f1;
  margin-top: 16px;
}
</style>
