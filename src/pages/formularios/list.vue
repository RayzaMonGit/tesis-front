<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Formularios de Evaluación</h1>

    <v-row dense>
      <v-col v-for="formulario in formularios" :key="formulario.id" cols="12" md="12" lg="6">
        <v-card class="rounded-xl shadow p-6">
          <!-- Título centrado -->
          <v-card-title class="justify-center text-2xl font-bold text-center">
            {{ formulario.nombre }}
          </v-card-title>

          <!-- Descripción -->
          <v-card-subtitle class="text-center text-base text-gray-600 mb-4">
            {{ formulario.descripcion || 'Sin descripción' }}
          </v-card-subtitle>

          <!-- Puntaje total -->
          <div class="text-center text-sm text-gray-500 mb-6">
            Puntaje total del formulario: <strong>{{ formulario.puntaje_total }}</strong>
          </div>

          <!-- Secciones -->
          <v-card-text>
            <div v-for="(seccion, i) in formulario.secciones" :key="i" class="mb-4 border-b pb-2">
              <!-- Título de la sección y puntaje -->
              <div class="d-flex align-center justify-space-between">
                <h3 class="text-lg font-semibold text-gray-800">
                  {{ i + 1 }}. {{ seccion.titulo }}
                </h3>
                <h6 class="text-lg font-semibold text-gray-800">
                  <span>
                    Hasta {{ seccion.puntaje_max }} pts
                  </span>
                </h6>
              </div>

              <!-- Criterios -->
              <ul class="mt-2 pl-4">
                <li v-for="(criterio, j) in seccion.criterios" :key="j"
                  class="flex justify-between text-sm text-gray-700 py-1">

                  <div class="d-flex align-center justify-space-between">
                    <span> {{ criterio.nombre }}</span>
                    <span class="text-gray-500 text-xs">
                      {{ criterio.puntaje_por_item }} puntos
                      <span v-if="criterio.puntaje_maximo != criterio.puntaje_por_item && criterio.puntaje_maximo!=0 "> (max. {{ criterio.puntaje_maximo }})</span>
                    </span>

                  </div>
                </li>
              </ul>
            </div>
          </v-card-text>

          <!-- Acciones -->
          <v-card-actions class="justify-end">
            <VBtn variant="outlined" color="warning" @click="abrirDialogoEdicion(formulario)" icon>
              <v-icon>ri-pencil-line</v-icon>
            </VBtn>
            <VBtn variant="outlined" color="error" @click="confirmarEliminacion(formulario)" icon>
              <v-icon>ri-delete-bin-5-line</v-icon>
            </VBtn>
          </v-card-actions>
        </v-card>

      </v-col>
      
    </v-row>

    
    <!-- Confirmación de borrado -->
    <v-dialog v-model="dialogoBorrar" max-width="500px">
      <v-card>
        <v-card-title>¿Eliminar formulario?</v-card-title>
        <v-card-text>
          ¿Deseas eliminar el formulario <strong>{{ formularioSeleccionado?.nombre }}</strong>?
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn text @click="dialogoBorrar = false">Cancelar</v-btn>
          <v-btn color="error" @click="eliminarFormulario">Eliminar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
  <!-- Edit dialog -->
  <EditFormularioDialog
    :formulario-selected="formularioSeleccionado"
    v-model:is-dialog-visible="dialogoActivo"
    @editFormulario="editFormulario"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue'
//import FormularioEditor from './Editor.vue'

const formularios = ref([])
const dialogoActivo = ref(false)
const dialogoBorrar = ref(false)
const formularioSeleccionado = ref(null)

import EditFormularioDialog from '/src/components/academico/formulario/EditFormularioDialog.vue'



// Carga inicial
const cargarFormularios = async () => {
  formularios.value = await $api('/formularios-evaluacion')
}
onMounted(cargarFormularios)

// Abrir diálogo
const abrirDialogoEdicion = (f) => {
  formularioSeleccionado.value = { ...f }
  dialogoActivo.value = true
}

// Resetear selección al cerrar
watch(dialogoActivo, visible => {
  if (!visible) formularioSeleccionado.value = null
})

// Recibir formulario editado
const editFormulario = (editForm) => {
  const idx = formularios.value.findIndex(f => f.id === editForm.id)
  if (idx !== -1) {
    formularios.value[idx] = editForm
  }
  dialogoActivo.value = false
}
const confirmarEliminacion = (formulario) => {
  formularioSeleccionado.value = formulario
  dialogoBorrar.value = true
}
/////////////////////
const eliminarFormulario = async () => {
  try {
    await $api(`/formularios-evaluacion/${formularioSeleccionado.value.id}`, {
      method: 'DELETE'
    })
    dialogoBorrar.value = false
    await cargarFormularios()
  } catch (error) {
    console.error('Error al eliminar:', error)
  }
}

const recargarFormularios = () => {
  dialogoActivo.value = false
  cargarFormularios()
}

onMounted(cargarFormularios)
</script>
