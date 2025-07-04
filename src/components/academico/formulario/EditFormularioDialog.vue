<template>
    <v-dialog v-model="isDialogVisible" max-width="1000px">
        <v-card elevation="4" class="pa-6">
            <v-card-title class="text-h5 font-weight-bold mb-4">
                Crear Formulario de Evaluación
            </v-card-title>
            <v-row dense>
                <!--nombre, puntaje maxaximo y descripcion-->
                <v-col cols="12" md="8">
                    <v-text-field v-model="localForm.nombre" label="Nombre del Formulario"
                        placeholder="Ej: Formulario de Calificación de méritos para docentes iterinos" outlined dense
                        required></v-text-field>
                </v-col>

                <v-col cols="12" md="4">
                    <v-text-field v-model="localForm.puntaje_total" label="Puntaje total del formulario" type="number"
                        placeholder="Ej: 100" outlined dense></v-text-field>
                </v-col>

                <v-col cols="12">
                    <v-textarea v-model="localForm.descripcion" label="Descripción"
                        placeholder="Ej: Formulario para la calificación de méritos para docentes interinos bajo resolucion HCF .Nº XXXX/202X"
                        outlined rows="2" auto-grow dense></v-textarea>
                </v-col>
            </v-row>
            <v-divider class="my-4"></v-divider>

            <!-- Suma de puntajes -->
          <v-alert
            v-if="sumaPuntajes > localForm.puntaje_total"
            type="error"
            dense
          >
            La suma de puntajes de secciones ({{ sumaPuntajes }}) supera el total
            ({{ localForm.puntaje_total }}).
          </v-alert>

        

            <!-- Secciones -->

            <div v-for="(seccion, i) in localForm.secciones" :key="i" class="mb-6">

                <v-card class="pa-4" variant="outlined">
                    <v-card-title class="d-flex align-center justify-space-between">
                        <span class="text-subtitle-1 font-weight-medium">
                            Sección {{ i + 1 }}
                        </span>
                        <VBtn color="error" variant="outlined" @click="removeSection(i)">
                            <VIcon icon="ri-prohibited-line" />
                        </VBtn>
                    </v-card-title>

                    <v-row dense>
                        <v-col cols="12" md="6">
                            <v-text-field v-model="seccion.titulo" label="Título de la Sección"
                                placeholder="Ej: CURSOS Y SEMINARIOS" outlined dense />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-text-field v-model.number="seccion.puntaje_max" label="Puntaje Máximo de la Sección"
                                placeholder="Ej: 10" type="number" :rules="[
                                    v => v >= 0 || 'Debe ser mayor o igual a 0',
                                    v => v <= (localForm.puntaje_total ?? Infinity)
                                        || `No puede exceder el puntaje máximo del formulario (${localForm.puntaje_total})`
                                ]" outlined dense />
                        </v-col>
                    </v-row>

                    <v-divider class="my-3" />

                    <!-- Criterios -->
                    <div v-for="(criterio, j) in seccion.criterios" :key="j" class="mb-3">
                        <v-row dense align="center">
                            <v-col cols="12" md="4">
                                <v-text-field v-model="criterio.nombre" label="Nombre del Criterio"
                                    placeholder="Ej: Asistencia a congresos" outlined dense />
                            </v-col>
                            <v-col cols="12" md="3">
                                <v-text-field v-model.number="criterio.puntaje_por_item" label="Puntaje por Item"
                                    placeholder="Ej: 0,5" type="number" outlined dense />
                            </v-col>
                            <v-col cols="12" md="3">
                                <v-text-field v-model.number="criterio.puntaje_maximo" label="Puntaje máximo del Criterio"
                                    placeholder="Ej: 2" type="number" outlined dense />
                            </v-col>
                            <v-col cols="12" md="2" class="text-right">
                                <VBtn color="error" @click="removeCriterion(i, j)">
                                    <v-icon>ri-delete-bin-3-line</v-icon>
                                </VBtn>
                            </v-col>
                        </v-row>

                        <!-- Sólo muestro puntos y max_items si max_items > 0 
                        <div v-if="criterio.max_items > 0" class="text-right text-xs text-gray-500">
                            {{ criterio.puntaje_por_item }} puntos (max. {{ criterio.max_items }})
                        </div>-->
                    </div>

                    <v-btn prepend-icon="mdi-plus" variant="text" color="primary" class="mt-2"
                        @click="addCriterion(i)">
                        Añadir Criterio
                    </v-btn>
                </v-card>
            </div>
            <v-btn prepend-icon="mdi-plus-box" color="secondary" variant="tonal" class="mt-2" @click="addSection">
                    Añadir Sección
                </v-btn>


            <v-card-actions>
                <v-spacer />
                <v-btn text @click="close">Cancelar</v-btn>
                <v-btn color="primary" @click="save">Guardar</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { toRefs, reactive, watch, computed, ref } from 'vue'

const props = defineProps({
  formularioSelected: Object,
  isDialogVisible: Boolean
})
const emit = defineEmits(['update:isDialogVisible', 'editFormulario'])

const { formularioSelected, isDialogVisible } = toRefs(props)
const formRef = ref(null)

// Creamos una copia reactiva
const localForm = reactive({
  id: null,
  nombre: '',
  descripcion: '',
  puntaje_total: 0,
  secciones: []
})

// Cuando el diálogo se abre, cargamos datos
watch(isDialogVisible, (visible) => {
  if (visible && formularioSelected.value) {
    Object.assign(localForm, {
      id: formularioSelected.value.id,
      nombre: formularioSelected.value.nombre,
      descripcion: formularioSelected.value.descripcion,
      puntaje_total: formularioSelected.value.puntaje_total,
      secciones: JSON.parse(
        JSON.stringify(formularioSelected.value.secciones || [])
      )
    })
  }
})

// Cálculo de suma de puntajes
const sumaPuntajes = computed(() =>
  localForm.secciones.reduce(
    (sum, s) => sum + (Number(s.puntaje_max) || 0),
    0
  )
)

// Métodos para secciones y criterios
function addSection() {
  localForm.secciones.push({
    titulo: '',
    puntaje_max: 0,
    criterios: []
  })
}

function removeSection(i) {
  localForm.secciones.splice(i, 1)
}

function addCriterion(sectionIndex) {
  localForm.secciones[sectionIndex].criterios.push({
    nombre: '',
    puntaje_por_item: 0,
    puntaje_maximo: 0
  })
}

function removeCriterion(sectionIndex, critIndex) {
  localForm.secciones[sectionIndex].criterios.splice(critIndex, 1)
}

// Cerrar diálogo
function close() {
  emit('update:isDialogVisible', false)
}
function limpiarCriterios(form) {
  form.secciones.forEach(seccion => {
    seccion.criterios = seccion.criterios.map(criterio => ({
      ...criterio,
      puntaje_por_item: Number(criterio.puntaje_por_item) || 0,
      puntaje_maximo: Number(criterio.puntaje_maximo) || 0,
    }))
  })
}
// Guardar cambios
async function save() {
  limpiarCriterios(localForm)
  await $api(`/formularios-evaluacion/${localForm.id}`, {
    method: 'POST',
    body: localForm
  })
  console.log('Formulario guardado:', localForm)
  emit('editFormulario', JSON.parse(JSON.stringify(localForm)))
  close()
}
</script>
