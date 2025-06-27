<script setup>
const route = useRoute()
const convocatoria = ref(null)
import { router } from '@/plugins/1.router';
import { onMounted } from 'vue';

const show = async () => {
  try {
    const resp = await $api(`/convocatorias/${route.params.id}`)
    convocatoria.value = resp.convocatoria
    console.log(resp);
  } catch (error) {
    console.error('Error al cargar la convocatoria:', error)
  }
}

function obtenerNombreArchivo(url) {
  return url.split('/').pop()
}
//para que un postulante se postule a una convocatoria
/*
const postularme = async () => {
  try {
    const resp = await $api('/postulaciones', {
      method: 'POST',
      body: {
        convocatoria_id: route.params.id,
      },
    });

    if (resp.message === 200) {
      success.value = 'Postulación enviada correctamente';
    } else {
      warning.value = 'No se pudo postular';
    }
  } catch (error) {
    console.error("Error al postular:", error);
    warning.value = 'Ocurrió un error al postularse';
  }
};*/

const obtenerPostulanteId = async () => {
  const res = await $api('/postulantes-perfil');
  console.log('ID del postulante:', res);
  return res.postulante_id;
};


const postularme = async (convocatoriaId) => {
  try {
    const postulanteId = await obtenerPostulanteId();
    
    const response = await $api('/postulaciones', {
      method: 'POST',
      body: {
        convocatoria_id: convocatoriaId,
        postulante_id: postulanteId
      }
    });

    if (response.redirect) {
      // Ya está postulado → redirige
      router.push(`/postulaciones/${response.postulacion_id}/documentos`);
      //hasta mientras una ruta  sinn iel id
      //router.push("/documentos-listado");
    } else {
      // Postulación nueva
      console.log('Postulación exitosa:', response);
      router.push(`/postulaciones/${response.postulacion_id}/documentos`);
      //hasta mientras una ruta  sinn iel id
      //router.push("/documentos-listado");
    }
  } catch (error) {
    console.error(error);
    alert('Error al postularse. Verifica si ya estás registrado como postulante.');
  }
};

onMounted(async () => {

  show();
})
</script>
<template>
  <VContainer>
    <VCard v-if="convocatoria" class="pa-6 mb-6">
      <!-- Título principal -->
      <VCardTitle class="text-h4 font-weight-bold d-flex align-center">
        <VIcon icon="ri-briefcase-line" class="me-3" />
        {{ convocatoria.titulo }}
      </VCardTitle>

      <VCardText>
        <!-- Información básica en dos columnas -->
        <VRow>
          <VCol cols="12" md="6">
            <p><strong>Área:</strong> {{ convocatoria.area }}</p>
            <p><strong>Descripción:</strong><br />{{ convocatoria.descripcion }}</p>
            <p><strong>Estado:</strong>
              <VChip :color="convocatoria.estado === 'Abierta' ? 'success' : 'grey'" size="small">
                {{ convocatoria.estado }}
              </VChip>
            </p>
          </VCol>
          <VCol cols="12" md="6">
            <p><strong>Fecha de Inicio:</strong> {{ convocatoria.fecha_inicio }}</p>
            <p><strong>Fecha de Fin:</strong> {{ convocatoria.fecha_fin }}</p>
            <p><strong>Plazas disponibles:</strong> {{ convocatoria.plazas_disponibles }}</p>
            <p><strong>Sueldo referencial:</strong> Bs. {{ convocatoria.sueldo_referencial }}</p>
          </VCol>
        </VRow>

        <VDivider class="my-4" />
<!-- 🔹 Mostrar el documento -->
<VCardText v-if="convocatoria?.documento">
          <h4 class="text-h6 mb-2"><strong>Documento de la convocatoria:</strong></h4>

          <div v-if="convocatoria.documento.endsWith('.pdf')">
            <iframe :src="convocatoria.documento" width="100%" height="500px" style="border: 1px solid #ccc;"></iframe>
          </div>

          <div v-else>
            <VIcon icon="mdi-file-word" color="primary" class="me-2" />
            <a :href="convocatoria.documento" target="_blank" rel="noopener">
              {{ obtenerNombreArchivo(convocatoria.documento) }}
            </a>
          </div>
        </VCardText>
        <!-- Documento -->
        <div v-if="convocatoria.documento">
          <p>Documento:</p>
          <div v-if="convocatoria.documento.endsWith('.pdf')">
            <VBtn :href="convocatoria.documento" target="_blank" icon variant="tonal" color="primary">
              <VIcon icon="ri-file-pdf-2-line" />
              <span class="ms-2">Ver PDF</span>
            </VBtn>
          </div>
          <div v-else>
            <VIcon icon="ri-file-word-2-line" class="me-2" color="blue" />
            <span>{{ getFileName(convocatoria.documento) }}</span>
          </div>
        </div>

        


        <VDivider class="my-6" />

        <!-- Requisitos de Ley -->
        <div v-if="convocatoria.requisitos_ley?.length">
          <p class="text-h6 font-weight-bold mb-2">
            <VIcon icon="ri-list-check-3" class="me-2" />
            Requisitos del Ministerio de Educación
          </p>
          <VList density="compact">
            <VListItem v-for="req in convocatoria.requisitos_ley" :key="req.id">
              <VListItemTitle> {{ req.descripcion }}</VListItemTitle>
              <VChip :color="req.req === 'Obligatorio' ? 'error' : 'info'" size="small" variant="outlined">
                {{ req.req }}
              </VChip>
            </VListItem>
          </VList>
        </div>

        <!-- Requisitos Personalizados -->
        <div v-if="convocatoria.requisitos?.length">
          <p class="text-h6 font-weight-bold mt-6 mb-2">
            <VIcon icon="ri-list-check-3" class="me-2" />
            Requisitos adicionales
          </p>
          <VList density="compact">
            <VListItem v-for="(req, index) in convocatoria.requisitos" :key="index">
              <VListItemTitle>{{ req.descripcion }}</VListItemTitle>
              <VChip :color="req.tipo === 'Obligatorio' ? 'error' : 'info'" size="small" variant="outlined">
                {{ req.tipo }}
              </VChip>
            </VListItem>
          </VList>
        </div>
        
        <VCol
              cols="12"
              sm="6"
            >
              <VBtn block color="success" prepend-icon="ri-verified-badge-line" @click="postularme(convocatoria.id)">
                Postularme a esta convocatoria
              </VBtn>
            </VCol>

      </VCardText>
    </VCard>

    <!-- Mostrar loader o mensaje si no hay convocatoria -->
    <div v-else class="text-center my-10">
      <VProgressCircular indeterminate color="primary" />
      <p class="mt-4">Cargando convocatoria...</p>
    </div>
  </VContainer>
</template>
