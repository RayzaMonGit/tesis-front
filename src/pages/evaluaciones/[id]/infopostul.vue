<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ofetch } from 'ofetch'

const route = useRoute()
const postulantes = ref([])
const loading = ref(true)
//devolver como numero el id de la convocatoria
const idconvocatoria = ref(parseInt(route.params.id))
const fetchPostulantes = async () => {
  try {
    const res = await $api(`/convocatorias/${route.params.id}/postulantes`)
    postulantes.value = res.postulantes 
    console.log('Postulantes:',res.postulantes)
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchPostulantes)
</script>

<template>
  <div>
    <h1 class="text-xl font-bold mb-4">Postulantes</h1>
    <VTable v-if="!loading">
      <thead>
        <tr>
            <th>Id</th>
          <th>Avatar</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>

        <tr v-for="p in postulantes" :key="p.id_postulacion">
  <td>{{ p.postulante_id }}</td>
  <td>
    <VAvatar
      size="32"
      :color="p.user?.avatar ? '' : 'primary'"
      :class="p.user?.avatar ? '' : 'v-avatar-light-bg primary--text'"
      :variant="!p.user?.avatar ? 'tonal' : undefined"
    >
      <VImg
        v-if="p.user?.avatar"
        :src="p.user.avatar"
      />
      <span
        v-else
        class="text-sm"
      >{{ avatarText(p.user?.full_name) }}</span>
    </VAvatar>
  </td>
  <td>{{ p.user?.full_name }}</td>
  <td>{{ p.user?.email }}</td>
  <td>
  <VChip
    :color="p.estado.toLowerCase() === 'en evaluacion' ? 'info' : (p.estado.toLowerCase() === 'aprobado' ? 'success' : (p.estado.toLowerCase() === 'rechazado' ? 'error' : 'grey'))"
    variant="tonal"
    size="small"
    class="text-capitalize"
  >
    {{ p.estado }}
  </VChip>
</td>
<td>
  <VTooltip v-if="p.estado.toLowerCase() !== 'en evaluacion'" location="top">
    <template #activator="{ props }">
      <span v-bind="props">
        <VBtn
          color="primary"
          size="small"
          :disabled="true"
        >
          <VIcon icon="ri-eye-line" class="me-2" />Revisar
        </VBtn>
      </span>
    </template>
    El postulante aún no mandó sus datos para ser evaluados
  </VTooltip>
  <VBtn
    v-else
    color="primary"
    size="small"
    @click="$router.push(`/evaluaciones/revisardocumentos/${p.id_postulacion}`)"
  >
    <VIcon icon="ri-eye-line" class="me-2" />Revisar
  </VBtn>
</td>
</tr>
      </tbody>
    </VTable>
    <div v-if="loading">Cargando...</div>
  </div>
</template>
