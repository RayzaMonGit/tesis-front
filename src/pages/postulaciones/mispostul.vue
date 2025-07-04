<script setup>

definePage({
  meta: {
    permissions: ["convocatoria_para_postulantes"]
  },
})
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import EstadoBarra from './EstadoBarra.vue'

const router = useRouter()
const postulacions = ref([])
const cargando = ref(true)


const obtenerPostulanteId = async () => {
  const res = await $api('/postulantes-perfil');
  //console.log('ID del postulante:', res);
  return res.postulante_id;
};

const fetchpostulacionsAbiertas = async () => {
    //onbtenermos la informacion del usuario logueado
  console.log('postulante_id:', await obtenerPostulanteId());
    
  try {
    //llamar al metodo de la api para obtener las postulaciones del usuario segun su id
    //cargando.value = true
    const postulanteId = await obtenerPostulanteId();
const resp = await $api(`/postulaciones/postulante/${postulanteId}`, {
  method: 'GET',
})
postulacions.value = (resp.data || []).filter(post => post.convocatoria)
   
   console.log('Postulacions abiertas:', postulacions.value);
    //postulacions.value = resp.postulacions || []
  } catch (error) {
    console.error('Error al cargar postulacions abiertas:', error)
  } finally {
    cargando.value = false
  }
}
function getPorcentajeNota(post) {
  const nota = Number(post.nota_preliminar) || 0
  // Ahora tomamos el puntaje máximo desde el formulario
  const max = Number(post.convocatoria?.formulario?.puntaje_total) || 100
  console.log('conv: ',post.convocatoria)
  console.log('nota: ',nota)
  console.log('puntajetotal: ',post.convocatoria?.formulario?.puntaje_total)
  return max > 0 ? Math.round((nota / max) * 100) : 0
}

onMounted(() => {
  fetchpostulacionsAbiertas()
})
</script>
<template>
  <VCard class="mb-6">
  <VCardText>
    <h2 class="text-h4 font-weight-bold mb-6 text-primary">
      Mis postulaciones
    </h2>

    <div class="mb-6">
      <VRow class="match-height">
        <template v-for="post in postulacions"
        :key="post.id">
          <VCol
        
        cols="12"
        md="4"
        sm="6"
      >
        <VCard flat
                border>
          <VRow no-gutters>
            <!-- Información Principal -->
            <VCol cols="12" sm="12" md="12" class="pa-6">
              <div class="mb-4">
                <VCardTitle class="text-h6 text-primary font-weight-bold">
                  {{ post.convocatoria?.titulo || 'Sin título' }}
                </VCardTitle>
                
              
            
                <div class="text-body-2 text-grey-darken-1">
                  {{ post.convocatoria?.area || 'Sin área' }}
                  <VChip :color="post.convocatoria.estado === 'Abierta' ? 'success' : 'error'" size="small">
                {{ post.convocatoria.estado || 'Sin estado'}}
              </VChip>
                </div>
              </div>

              <VCardText class="text-body-2 text-wrap text-medium-emphasis">
                {{ post.convocatoria?.descripcion?.slice(0, 120) }}...
              </VCardText>

              <div class="mt-4 text-caption text-grey-darken-2">
                📅 Inicio: {{ post.convocatoria?.fecha_inicio ? new Date(post.convocatoria.fecha_inicio).toLocaleDateString() : '-' }} <br />
                🕔 Fin: {{ post.convocatoria?.fecha_fin ? new Date(post.convocatoria.fecha_fin).toLocaleDateString() : '-' }}
              </div>

              <!-- Barra de estado -->
              <div class="mt-4">
                <EstadoBarra :estado="post.estado" :nota_preliminar="getPorcentajeNota(post)" />
              </div>

              <VCardActions class="mt-5">
                <VBtn
                 class="flex-grow-1"
                  variant="outlined"
                   @click="router.push(`/postulaciones/${post.id}/documentos`)"
                >
                <template #append>
                  <VIcon icon="ri-file-text-line" class="me-2" />
                </template>
                  Ver más detalles
                </VBtn>
              </VCardActions>
            </VCol>

            
          </VRow>
        </VCard>
      </VCol>
        </template>
      
    </VRow>
    </div>

    <!-- Alerta cuando no hay postulaciones -->
    <VAlert
      v-if="!cargando && postulacions.length === 0"
      type="info"
      class="mt-6"
      border="start"
      variant="tonal"
      prominent
    >
      No hay postulaciones abiertas actualmente.
    </VAlert>
  </VCardText>
  </VCard>
</template>
