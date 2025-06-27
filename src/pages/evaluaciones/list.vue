<template>
  <VCard class="mb-6">
  <VCardText>
    <h2 class="text-h4 font-weight-bold mb-6 text-primary">
      Mis convocatorias asignadas
    </h2>

    <div class="mb-6">
      <VRow class="match-height">
        <template v-for="post in convocatorias"
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
                  {{ post.titulo || 'Sin título' }}
                </VCardTitle>
                <div class="text-body-2 text-grey-darken-1">
                  {{ post.area || 'Sin área' }}
                  <VChip :color="post.estado === 'Abierta' ? 'success' : 'error'" size="small">
                {{ post.estado }}
              </VChip>
                </div>
              
             <VCardText class="text-body-2 text-wrap text-medium-emphasis">
                {{ post.descripcion?.slice(0, 120) }}...
              </VCardText>

              <div class="mt-4 text-caption text-grey-darken-2">
                📅 Inicio: {{ post.fecha_inicio ? new Date(post.fecha_inicio).toLocaleDateString() : '-' }} <br />
                🕔 Fin: {{ post.fecha_fin ? new Date(post.fecha_fin).toLocaleDateString() : '-' }}
              </div>

              <VCardActions class="mt-5">
                <VBtn
                 class="flex-grow-1"
                  variant="outlined"
                   @click="$router.push(`/evaluaciones/${post.id}/infopostul`)"
                >
                <template #append>
                  <VIcon icon="ri-user-follow-line" class="me-2" />
                </template>
                  Revisar postulantes
                </VBtn>
              </VCardActions>
            
              </div>

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

<script setup>
import { $api } from '@/utils/api';
import { onMounted } from 'vue';

const postulacions = ref([]);
const convocatorias = ref([]);
const cargando = ref(true);

const obtenerPostulanteId = async () => {
  const res = await $api('/comision/convocatorias-por-evaluador');
  //console.log('ID del postulante:', res);
  console.log('Comisiones en las que esta el usuaruo:', res.convocatorias);
  //return res.postulante_id;
  //convocatorias.value = res;
  convocatorias.value = res.convocatorias || [];
};

const verpostulantes = async () => {
  try {
    const response = await $api('/convocatorias/postulantes');
    const data = await response.json();
    console.log(data);
  } catch (error) {
    console.error('Error fetching postulantes:', error);
  }
};
onMounted(() => {
  //console.log('evaluaciones-listado component mounted');
  //verpostulantes();
  obtenerPostulanteId();
});
</script>