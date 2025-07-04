<template>
  <v-card>
    <v-card-title>Historial de Cambios</v-card-title>
    <v-card-text>
      <v-table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Acción</th>
            <th>Cambios</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="registro in auditoria" :key="registro.id">
            <td>{{ registro.created_at }}</td>
            <td>{{ registro.usuario }}</td>
            <td>{{ registro.accion }}</td>
            <td>
              <ul>
                <li v-for="(cambio, campo) in registro.cambios" :key="campo">
                  <strong>{{ campo }}:</strong>
                  <span v-if="typeof cambio === 'object' && cambio.antes !== undefined">
                    <span class="text-error">[Antes: {{ cambio.antes }}]</span>
                    <span class="text-success">[Después: {{ cambio.despues }}]</span>
                  </span>
                  <span v-else>{{ cambio }}</span>
                </li>
              </ul>
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const auditoria = ref([])
const route = useRoute()

onMounted(async () => {
  // Cambia la URL según tu endpoint real
  const convocatoriaId = route.params.id
  const res = await $api(`/convocatorias/auditoria/todas`)
  console.log('listaauditorias: ',res.data)
  auditoria.value = await res.data
})
</script>