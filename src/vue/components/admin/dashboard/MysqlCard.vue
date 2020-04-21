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
        theme="light"
        :options.sync="pie"
        :init-options="initOptions"
        ref="pie"
        auto-resize
      />
    </figure>
  </div>
</template>

<script>
import { isEmpty } from 'lodash'
import { getDbstatus } from '~/api/dashboards'

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
          data: ['queries_count', 'queries_per_second_avg', 'slow_queries', 'threads', 'uptime']
        },
        series: []
      }
    }
  },

  mounted() {
    let pie = this.$refs.pie

    getDbstatus(this.$store.getters.loggedToken, { host: this.$props.host }).then(response => {
      const dbstat = response.data.mysql

      this.running = true
      this.uptime = dbstat.uptime

      pie.options.series.push({
        name: dbstat.host,
        type: 'pie',
        radius: '50%',
        center: ['50%', '50%'],
        data: [
          {value: dbstat.queries_count, name: 'queries_count'},
          {value: dbstat.queries_per_second_avg, name: 'queries_per_second_avg'},
          {value: dbstat.slow_queries, name: 'slow_queries'},
          {value: dbstat.opens, name: 'opens'}
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
