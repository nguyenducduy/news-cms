<template>
  <div>
    <p>
      <el-tag type="primary" size="mini">{{ label }}</el-tag> &nbsp;
      <code>{{ host }}</code>
      &nbsp;|&nbsp;
      <i class="el-icon-fa-circle" :class="running ? `online` : `offline`"></i> {{ uptime }}
    </p>
    <figure>
      <chart
        :options="pie"
        :init-options="initOptions"
        ref="pie"
        auto-resize
      />
    </figure>
  </div>
</template>

<script>
import { isEmpty } from 'lodash'
import { getQueuestatus } from '~/api/dashboards'

export default {
  props: ['host', 'label'],

  data() {
    return {
      running: false,
      uptime: 0,
      initOptions: {
        renderer: 'canvas'
      },
      pie: {
        tooltip: {
          trigger: 'item',
          formatter: '{b} : {c} ({d}%)'
        },
        legend: {
          orient: 'vertical',
          left: 'left',
          data: ['total_jobs', 'current_tubes', 'current_producers', 'current_workers', 'current_waiting']
        },
        series: []
      }
    }
  },

  mounted() {
    let pie = this.$refs.pie

    getQueuestatus(this.$store.getters.loggedToken, { host: this.$props.host }).then(response => {
      const dbstat = response.data.beanstalk

      this.running = true
      this.uptime = dbstat.uptime

      pie.options.series.push({
        name: dbstat.host,
        type: 'pie',
        radius: '50%',
        center: ['50%', '50%'],
        data: [
          {value: dbstat.total_jobs, name: 'total_jobs'},
          {value: dbstat.current_tubes, name: 'current_tubes'},
          {value: dbstat.current_producers, name: 'current_producers'},
          {value: dbstat.current_workers, name: 'current_workers'},
          {value: dbstat.current_waiting, name: 'current_waiting'},
        ],
        itemStyle: {
          emphasis: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: 'rgba(0, 0, 0, 0.5)'
          }
        }
      })
    })
  }
}

</script>

<style scoped>
.echarts {
  width: 435px;
  height: 235px;
}
</style>
