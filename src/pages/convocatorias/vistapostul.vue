<script setup>

definePage({
  meta: {
    permissions: ["convocatoria_para_postulantes"]
  },
})
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const convocatorias = ref([])
const cargando = ref(true)

const fetchConvocatoriasAbiertas = async () => {
  try {
    const resp = await $api('/convocatorias?estado=Abierta')
    convocatorias.value = resp.data || []
  } catch (error) {
    console.error('Error al cargar convocatorias abiertas:', error)
  } finally {
    cargando.value = false
  }
}

const verConvocatoria = (id) => {
  router.push(`/convocatorias/detalles/${id}`)
}


onMounted(() => {
  fetchConvocatoriasAbiertas()
})
</script>
<template>
  <div>
    <h2 class="text-h4 font-weight-bold mb-6 text-primary">
      Convocatorias Abiertas
    </h2>

    <VRow>
      <VCol
        v-for="conv in convocatorias"
        :key="conv.id"
        cols="12"
        md="6"
        class="mb-6"
      >
        <VCard class="rounded-xl elevation-4 overflow-hidden">
          <VRow no-gutters>
            <!-- Información Principal -->
            <VCol cols="12" sm="8" md="7" class="pa-6">
              <div class="mb-4">
                <VCardTitle class="text-h6 text-primary font-weight-bold">
                  {{ conv.titulo }}
                </VCardTitle>
                <div class="text-body-2 text-grey-darken-1">
                  {{ conv.area }}
                </div>
              </div>

              <VCardText class="text-body-2 text-wrap text-medium-emphasis">
                {{ conv.descripcion.slice(0, 120) }}...
              </VCardText>

              <div class="mt-4 text-caption text-grey-darken-2">
                📅 Inicio: {{ new Date(conv.fecha_inicio).toLocaleDateString() }} <br />
                🕔 Fin: {{ new Date(conv.fecha_fin).toLocaleDateString() }}
              </div>

              <VCardActions class="mt-5">
                <VBtn
                  color="primary"
                  variant="elevated"
                  prepend-icon="mdi-arrow-right-circle-outline"
                  @click="verConvocatoria(conv.id)"
                >
                  Ver más detalles
                </VBtn>
              </VCardActions>
            </VCol>

            <!-- Panel Lateral -->
            <VCol
              cols="12"
              sm="4"
              md="5"
              class="d-flex flex-column justify-center align-center bg-grey-lighten-4 px-6 py-10"
            >
              <div class="text-center">
                <p class="text-subtitle-2 mb-1">Plazas disponibles</p>
                <p class="text-h4 font-weight-bold text-success">
                  {{ conv.plazas_disponibles }}
                </p>

                <VDivider class="my-4" />

                <p class="text-subtitle-2 mb-1">Sueldo referencial</p>
                <p class="text-body-1 font-weight-medium">
                  {{ conv.sueldo_referencial ? 'Bs. ' + conv.sueldo_referencial : 'No especificado' }}
                </p>
              </div>
            </VCol>
          </VRow>
        </VCard>
      </VCol>
    </VRow>

    <!-- Alerta cuando no hay convocatorias -->
    <VAlert
      v-if="!cargando && convocatorias.length === 0"
      type="info"
      class="mt-6"
      border="start"
      variant="tonal"
      prominent
    >
      No hay convocatorias abiertas actualmente.
    </VAlert>
  </div>
</template>
