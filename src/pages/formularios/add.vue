<template>
  <v-container class="pa-6" max-width="1000px">
    <v-card elevation="4" class="pa-6">
      <v-card-title class="text-h5 font-weight-bold mb-4">
        Crear Formulario de Evaluación
      </v-card-title>

      <v-row dense>
        <!--nombre, puntaje maxaximo y descripcion-->
        <v-col cols="12" md="8">
          <v-text-field v-model="formulario.nombre" label="Nombre del Formulario"
            placeholder="Ej: Formulario de Calificación de méritos para docentes iterinos" outlined dense
            required></v-text-field>
        </v-col>
        <v-col cols="12" md="4">
          <v-text-field v-model="formulario.puntaje_total" label="Puntaje total del formulario" type="number"
            placeholder="Ej: 100" outlined dense></v-text-field>
        </v-col>
        <v-col cols="12">
          <v-textarea v-model="formulario.descripcion" label="Descripción"
            placeholder="Ej: Formulario para la calificación de méritos para docentes interinos bajo resolucion HCF .Nº XXXX/202X"
            outlined rows="2" auto-grow dense></v-textarea>
        </v-col>
      </v-row>

      <v-divider class="my-4"></v-divider>
      <v-alert
            v-if="sumaPuntajes > formulario.puntaje_total"
            type="error"
            class="mt-2"
            dense
            border="start"
            border-color="error"
          >
            La suma de puntajes por sección supera el puntaje total del formulario ({{ formulario.puntaje_total }}).
          </v-alert>

          <v-alert
            v-else-if="sumaPuntajes < formulario.puntaje_total"
            type="warning"
            class="mt-2"
            dense
            border="start"
            border-color="warning"
          >
            La suma de puntajes por sección es menor al puntaje total del formulario.
          </v-alert>



      <div v-for="(seccion, i) in formulario.secciones" :key="i" class="mb-6">
        

        <v-card class="pa-4" variant="outlined">
          <v-card-title class="d-flex align-center justify-space-between">
            <span class="text-subtitle-1 font-weight-medium">
              Sección {{ i + 1 }}
            </span>
            <VBtn color="error" variant="outlined" @click="eliminarSeccion(i)">
              <VIcon icon="ri-prohibited-line" />
            </VBtn>
          </v-card-title>

          <!--Secciones del formulario ttulo, puntaje max,-->
          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field 
              v-model="seccion.titulo" 
              label="Título de la Sección" 
              placeholder="Ej: CURSOS Y SEMINARIOS"
              outlined dense></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field 
                v-model.number="seccion.puntaje_max" 
                label="Puntaje Máximo de la Sección" 
                type="number"
                placeholder="Ej: 10"
                :rules="[
                  v => v >= 0 || 'Debe ser mayor o igual a 0',
                  v => v <= formulario.puntaje_total || `No puede exceder el puntaje máximo del formulario (${formulario.puntaje_total})`
                ]"
                outlined
                dense
              ></v-text-field>
            </v-col>


          </v-row>

          <v-divider class="my-3"></v-divider>
          <!--Criterios de seleccion segun seccion-->
          <div v-for="(criterio, j) in seccion.criterios" :key="j" class="mb-3">
            <v-row dense align="center">
              <v-col cols="12" md="4">
                <v-text-field 
                v-model="criterio.nombre" 
                label="Nombre del Criterio" 
                placeholder="Ej: Asistencia a congresos"
                outlined dense></v-text-field>
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field 
                v-model.number="criterio.puntaje_por_item" 
                label="Puntaje por ítem"
                placeholder="Ej: 0,5"
                 type="number" outlined
                dense></v-text-field>
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field 
                v-model.number="criterio.puntaje_maximo" 
                label="Puntaje máximo del Criterio" 
                placeholder="Ej: 2"
                type="number"
                outlined dense
                @blur="() => criterio.puntaje_maximo = criterio.puntaje_maximo ?? 0"

                :value="criterio.puntaje_maximo ?? 0"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="2" class="text-right">
                <VBtn color="error"  @click="eliminarCriterio(i, j)">
                  <v-icon>ri-delete-bin-3-line</v-icon>
                </VBtn>
              </v-col>
            </v-row>
          </div>

          <v-btn prepend-icon="mdi-plus" variant="text" color="primary" class="mt-2" @click="agregarCriterio(i)">
            Añadir Criterio
          </v-btn>
        </v-card>
      </div>

      <v-btn prepend-icon="mdi-plus-box" color="secondary" variant="tonal" class="mt-2" @click="agregarSeccion">
        Añadir Sección
      </v-btn>

      <v-divider class="my-6"></v-divider>
      <VAlert type="warning" v-if="warning" class="mt-3">
                    <strong>{{warning}}</strong>
                </VAlert>

                <VAlert type="error" v-if="error_exsist" class="mt-3">
                    <strong>hubo un error al guardar en el servidor</strong>
                </VAlert>

                <VAlert type="success" v-if="success" class="mt-3">
                    <strong>{{success}}</strong>
                </VAlert>
      <v-btn color="primary" class="mt-4" size="large" block @click="enviarFormulario">
        Guardar Formulario
      </v-btn>
    </v-card>
  </v-container>
</template>


<script setup>


import axios from 'axios'
import { p } from '@antfu/utils';
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'


const error_exsist = ref(null);
const success = ref(null);
const warning = ref(null);
const router = useRouter()

const formulario = ref({
  nombre: '',
  descripcion: '',
  puntaje_total: null,
  secciones: [
    {
      titulo: '',
      puntaje_max: null,
      criterios: [
        {
          nombre: '',
          puntaje_por_item: null,
          puntaje_maximo: null,
          
        }
      ],
    },
  ],

})

function agregarSeccion() {
  formulario.value.secciones.push({
    titulo: '',
    puntaje_max: 0,
    //orden: formulario.value.secciones.length + 1,
    criterios: []
  })
}
function eliminarSeccion(index) {
  formulario.value.secciones.splice(index, 1)
}
function agregarCriterio(seccionIndex) {
  formulario.value.secciones[seccionIndex].criterios.push({
    nombre: '',
    puntaje_por_item: 0,
    puntaje_maximo: 0,
    orden: formulario.value.secciones[seccionIndex].criterios.length + 1
  })
}
function eliminarCriterio(seccionIndex, criterioIndex) {
  formulario.value.secciones[seccionIndex].criterios.splice(criterioIndex, 1)
}

const enviarFormulario = async () => {
  console.log(formulario.value)
  //validaciones basicas
  warning.value = null
  if(!formulario.value.nombre){
    warning.value = 'El nombre del formulario es obligatorio'
    return;
  }
  if(!formulario.value.descripcion){
    warning.value = 'La descripcion del formulario es obligatorio'
    return;
  }
  if(!formulario.value.puntaje_total){
    warning.value = 'El puntaje total del formulario es obligatorio'
    return;
  }
  
// Validar secciones
if (!formulario.value.secciones || formulario.value.secciones.length === 0) {
  warning.value = 'Debe agregar al menos una sección'
  return
}

for (let i = 0; i < formulario.value.secciones.length; i++) {
  const seccion = formulario.value.secciones[i]

  if (!seccion.titulo) {
    warning.value = `El título de la sección #${i + 1} es obligatorio`
    return
  }

  if (seccion.puntaje_max == null || seccion.puntaje_max === '') {
    warning.value = `El puntaje máximo de la sección #${i + 1} es obligatorio`
    return
  }

  // Validar criterios
  if (!seccion.criterios || seccion.criterios.length === 0) {
    warning.value = `Debe agregar al menos un criterio en la sección #${i + 1}`
    return
  }

  for (let j = 0; j < seccion.criterios.length; j++) {
    const criterio = seccion.criterios[j]

    if (!criterio.nombre) {
      warning.value = `El nombre del criterio #${j + 1} en la sección #${i + 1} es obligatorio`
      return
    }

    if (criterio.puntaje_por_item == null || criterio.puntaje_por_item === '') {
      warning.value = `El puntaje por ítem del criterio #${j + 1} en la sección #${i + 1} es obligatorio`
      return
    }

    // max_items puede ser opcional; si quieres que sea obligatorio, descomenta lo siguiente:
    /*
    if (criterio.max_items == null || criterio.max_items === '') {
      warning.value = `La cantidad máxima de ítems del criterio #${j + 1} en la sección #${i + 1} es obligatoria`
      return
    }
    */
  }
}


const formData = {
  nombre: formulario.value.nombre,
  descripcion: formulario.value.descripcion,
  puntaje_total: Number(formulario.value.puntaje_total),
  secciones: (formulario.value.secciones || []).map((s) => ({
    titulo: s.titulo,
    puntaje_max: Number(s.puntaje_max),
    criterios: (s.criterios || []).map((c) => ({
      nombre: c.nombre,
      puntaje_por_item: Number(c.puntaje_por_item),
      puntaje_maximo: Number(c.puntaje_maximo ?? puntaje_por_item), // Si viene undefined, lo pone como 0
    })),
  })),
}


  try {
    const resp = await $api('/formularios-evaluacion', {
      method: 'POST',
      body: formData,
      onResponseError({ response }) {
        console.log(response)
        error_exsist.value = response._data?.message || 'Error al guardar el formulario'
      },
    })

    if (resp.message === 403) {
      warning.value = resp.message_text
    } else {
      success.value = 'Formulario guardado correctamente'

      setTimeout(() => {
        success.value = null;
        warning.value = null;
        error_exsist.value = null;
        router.push('/formularios/list');
      }, 1000)
    }
  } catch (error) {
    console.error(error)
    error_exsist.value = 'Error al registrar el formulario'
  }
};
//validando que la suma de los puntajes de las secciones no exceda el puntaje total del formulario
const sumaPuntajes = computed(() => {
  return formulario.value.secciones.reduce((total, seccion) => {
    return total + (seccion.puntaje_max || 0)
  }, 0)
})


</script>