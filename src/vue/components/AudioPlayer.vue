<template>
  <div>
    <el-button type="text" size="mini" @click="togglePlayback" :icon="playing ? 'el-icon-fa-pause' : 'el-icon-fa-play'">
    </el-button>

    <!-- <el-button :icon="playing ? 'el-icon-fa-pause' : 'el-icon-fa-play'" @click="togglePlayback" type="text" class="play-button"></el-button> -->
  </div>
</template>

<script>
import VueHowler from 'vue-howler'

export default {
  mixins: [VueHowler]
}
</script>

<style lang="css">
.audio-player {
  display: inline-block;
  vertical-align: middle;
}
.play-button {
  position: absolute;
  left: 24px;
  top: 24%;
  background: transparent;
}
.el-icon-fa-play::before {
  font-size: 10px;
}
.el-icon-fa-pause {
  margin-left: -1px;
}
.el-icon-fa-pause::before {
  font-size: 10px;
}
</style>
