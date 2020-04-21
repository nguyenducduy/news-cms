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
import { getSearchstatus } from '~/api/dashboards'

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
          data: ['command_search', 'queries', 'query_wall', 'avg_query_wall', 'uptime']
        },
        series: []
      }
    }
  },

  mounted() {
    let pie = this.$refs.pie

    getSearchstatus(this.$store.getters.loggedToken, { host: this.$props.host }).then(response => {
      const dbstat = response.data.sphinx

      this.running = true
      this.uptime = dbstat.uptime

      pie.options.series.push({
        name: dbstat.host,
        type: 'pie',
        radius: '50%',
        center: ['50%', '50%'],
        data: [
          {value: dbstat.command_search, name: 'command_search'},
          {value: dbstat.queries, name: 'queries'},
          {value: dbstat.query_wall, name: 'query_wall'},
          {value: dbstat.avg_query_wall, name: 'avg_query_wall'}
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
