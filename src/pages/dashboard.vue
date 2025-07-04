<template>
  <VContainer>
    <VRow>
      <VCol cols="12" md="4">
  <VCard title="Convocatorias Registradas">
    <VCardText>
      <div class="text-h4 font-weight-bold">
        {{ convocatorias.length }}
      </div>
      <div class="text-subtitle-2">Totales en el sistema</div>

      <VSelect
        v-model="estadoSeleccionado"
        :items="Object.keys(agrupadasPorEstado)"
        label="Filtrar por estado"
        class="mt-4"
        dense
        clearable
      />
    </VCardText>
  </VCard>
</VCol>
<VCol cols="12" md="8" v-if="estadoSeleccionado">
  <VCard :title="`Convocatorias en estado: ${estadoSeleccionado}`">
    <VCardText>
      <VList>
        <VListItem
          v-for="conv in convocatoriasFiltradas"
          :key="conv.id"
        >
          <VListItemTitle>{{ conv.titulo }}</VListItemTitle>
          <VListItemSubtitle>{{ conv.area }}</VListItemSubtitle>
        </VListItem>
      </VList>
    </VCardText>
  </VCard>
</VCol>

<VCol cols="12">
  <VCard title="Duración de Convocatorias">
    <VCardText>
      <VueApexCharts
        type="rangeBar"
        height="350"
        :options="opcionesRango"
        :series="[{ data: rangoConvocatorias }]"
      />
    </VCardText>
  </VCard>
</VCol>

<!-- Tarjeta con contador -->
<!--      <VCol cols="12" md="4">
  <VCard title="Total de Usuarios">
    <VCardText class="text-center">
      <h1 class="text-h3">{{ totalUsuarios }}</h1>
      <div class="text-subtitle-1">Registrados en el sistema</div>
    </VCardText>
  </VCard>
</VCol>

<VCol
  v-for="(cantidad, rol) in conteoPorRol"
  :key="rol"
  cols="12"
  md="3"
>
  <VCard :title="rol">
    <VCardText class="text-center">
      <h1 class="text-h4">{{ cantidad }}</h1>
      <div class="text-subtitle-2">Usuarios con este rol</div>
    </VCardText>
  </VCard>
</VCol>

-->
<VCol cols="12" md="6">
  <VCard title="Usuarios por Rol">
    <VCardText>
      <VueApexCharts
        type="donut"
        :options="donutOptions"
        :series="donutSeries"
        height="350"
      />
    </VCardText>
  </VCard>
</VCol>



      <!-- Gráfico de barras -->
      <VCol cols="12" md="4">
        <VCard title="Postulaciones por Convocatoria">
          <VCardText>
            <VueApexCharts
              type="bar"
              height="250"
              :options="barChartOptions"
              :series="barSeries"
            />
          </VCardText>
        </VCard>
      </VCol>

      

    </VRow>
  </VContainer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'

const data = ref([])
const roles = ref([])

// Conteo por rol (nombre -> cantidad)
const conteoPorRol = computed(() => {
  const conteo = {}

  for (const rol of roles.value) {
    conteo[rol.name] = 0
  }

  conteo['Sin rol'] = 0

  for (const user of data.value) {
    const nombreRol = user.role?.name || 'Sin rol'
    conteo[nombreRol] = (conteo[nombreRol] || 0) + 1
  }

  return conteo
})

// donutLabels y donutSeries deben definirse después de conteoPorRol
const donutLabels = computed(() => Object.keys(conteoPorRol.value))
const donutSeries = computed(() => Object.values(conteoPorRol.value))

// donutOptions debe ser computed para que labels se actualicen correctamente
const donutOptions = computed(() => ({
  chart: { type: 'donut' },
  labels: donutLabels.value,
  legend: { position: 'bottom' },
  dataLabels: { enabled: true },
  plotOptions: {
    pie: {
      donut: {
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            fontSize: '1.2rem',
            color: '#333',
            formatter: () => donutSeries.value.reduce((a, b) => a + b, 0)
          }
        }
      }
    }
  },
  responsive: [{
    breakpoint: 480,
    options: { chart: { width: 300 }, legend: { position: 'bottom' } }
  }]
}))

// Cargar usuarios y roles
const cargarUsuarios = async () => {
  try {
    const resp = await $api('/staffs?search=')
    data.value = resp.users?.data || []
    roles.value = resp.roles || []
  } catch (error) {
    console.error('Error cargando usuarios:', error)
  }
}

onMounted(() => cargarUsuarios())



const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}


const rangoConvocatorias = computed(() => {
  return convocatorias.value.map(c => ({
    x: c.titulo,
    y: [
      new Date(c.fecha_inicio).getTime(),
      new Date(c.fecha_fin).getTime()
    ]
  }))
})

const opcionesRango = {
  chart: {
    type: 'rangeBar',
    height: 350
  },
  plotOptions: {
    bar: {
      horizontal: true,
      barHeight: '60%',
      rangeBarGroupRows: false
    }
  },
  xaxis: {
    type: 'datetime'
  },
  tooltip: {
    custom({ series, seriesIndex, dataPointIndex, w }) {
      const [start, end] = w.globals.initialSeries[seriesIndex].data[dataPointIndex].y
      return `
        <div>
          <strong>Desde:</strong> ${formatDate(start)}<br>
          <strong>Hasta:</strong> ${formatDate(end)}
        </div>
      `
    }
  }
}



// Estados agrupados
const convocatorias = ref([])
const agrupadasPorEstado = computed(() => {
  const agrupadas = {}

  for (const item of convocatorias.value) {
    const estado = item.estado || 'Desconocido'
    agrupadas[estado] = agrupadas[estado] ? agrupadas[estado] + 1 : 1
  }

  console.log("Convocatorias agrupadas por estado:", agrupadas)
  return agrupadas
})

// Estado seleccionado por el usuario
const estadoSeleccionado = ref(null)
const convocatoriasFiltradas = computed(() => {
  if (!estadoSeleccionado.value) return []
  return convocatorias.value.filter(c => c.estado === estadoSeleccionado.value)
})

// Cargar convocatorias
const cargarConvocatorias = async () => {
  try {
    const resp = await $api('/convocatorias/all', {
      method: 'GET',
      onResponseError({ response }) {
        console.error("Error API:", response)
      }
    })
    console.log("Convocatorias cargadas:", resp.data)
    convocatorias.value = resp.data.data || resp.data // según cómo venga
  } catch (error) {
    console.error('Error cargando convocatorias:', error)
  }
}

onMounted(() => {
  cargarConvocatorias()
})


const moreList = [
  { title: 'Last 28 Days', value: 'Last 28 Days' },
  { title: 'Last Month', value: 'Last Month' },
  { title: 'Last Year', value: 'Last Year' },
]
function hexToRgb(hex) {
  hex = hex.replace(/^#/, '')
  if (hex.length === 3) {
    hex = hex.split('').map(x => x + x).join('')
  }
  const num = parseInt(hex, 16)
  return [
    (num >> 16) & 255,
    (num >> 8) & 255,
    num & 255
  ].join(',')
}
// === Gráfico Donut ===
const vuetifyTheme = useTheme()
const themeColors = vuetifyTheme.current.value.colors
const variableTheme = vuetifyTheme.current.value.variables
const secondaryText = `rgba(${hexToRgb(String(themeColors['on-background']))},${variableTheme['medium-emphasis-opacity']})`
const primaryText = `rgba(${hexToRgb(String(themeColors['on-background']))},${variableTheme['high-emphasis-opacity']})`

const chartConfig = {
  chart: { sparkline: { enabled: true } },
  labels: ['USA', 'India', 'Canada', 'Japan', 'France'],
  legend: { show: false },
  tooltip: { enabled: false },
  dataLabels: { enabled: false },
  stroke: { width: 3 },
  plotOptions: {
    pie: {
      donut: {
        size: '83%',
        labels: {
          show: true,
          name: { fontSize: '0.9375rem', color: secondaryText },
          value: { fontSize: '1.75rem', color: primaryText },
          total: {
            show: true,
            label: 'Total',
            formatter: () => '89k'
          }
        }
      }
    }
  }
}

const series = [13, 18, 18, 24, 16]
const customChartLegends = [
  { title: 'USA', opacity: 1 },
  { title: 'India', opacity: 0.8 },
  { title: 'Canada', opacity: 0.6 },
  { title: 'Japan', opacity: 0.4 },
  { title: 'France', opacity: 0.2 },
]

// === Gráfico de barras ===
const barChartOptions = {
  chart: { type: 'bar' },
  xaxis: {
    categories: ['Conv 1', 'Conv 2', 'Conv 3'],
  },
  colors: ['#42a5f5'],
}

const barSeries = [
  {
    name: 'Postulaciones',
    data: [10, 25, 15],
  },
]

// === Tarjeta con contador ===
//const totalUsuarios = 125
</script>
