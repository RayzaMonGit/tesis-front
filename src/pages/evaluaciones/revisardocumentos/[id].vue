<script setup>
import { useRoute } from 'vue-router'
const route = useRoute()
const postulacionId = route.params.id
console.log('ID de la postulación:', postulacionId)

import { ref, onMounted, computed, reactive } from 'vue'

// Configuración inicial
const tabsData = ['Requisitos', 'Hoja de Vida/CV']
const currentTab = ref(0)
const panelesAbiertos = ref([])
const confirmDialog = ref(false)
const opcionesEvaluacion = [
  { title: 'Aprobado', value: 'aprobado' },
  { title: 'Rechazado', value: 'rechazado' },
  { title: 'Pendiente', value: 'pendiente' },
]
const evaluacionRequisitos = reactive({
  ley: {},
  personalizado: {}
});

const comentariosRequisitos = reactive({
  ley: {},
  personalizado: {}
});

const evaluacionArchivos = ref({});
const comentariosArchivos = ref({});
const comentarioVisibleArchivos = reactive({});

const archivosRequisitos = reactive({
  ley: {},
  personalizado: {}
});

const archivosFormulario = ref({});




// Estados
const loading = ref(true)
const saving = ref(false)
const postulacion = ref(null)
const postulante = ref(null)
const convocatoria = ref(null)
const requisitosLey = ref([])
const requisitosPersonalizados = ref([])
const secciones = ref([])

// Datos de documentos


// Evaluaciones
const evaluacionId = ref(null)

const comentarioGeneral = ref('')

// Computed
const documentosCompletos = computed(() => {
  const requisitosObligatorios = [
    ...requisitosLey.value,
    ...requisitosPersonalizados.value.filter(r => r.requerido)
  ]
  return requisitosObligatorios.every(requisito => {
    return archivosRequisitos.ley[requisito.id]?.yaSubido ||
      archivosRequisitos.personalizado[requisito.id]?.yaSubido
  })
})

const puntajeTotal = computed(() => {
  return secciones.value.reduce((total, seccion) => {
    return total + getPuntajeSeccion(seccion.id)
  }, 0)
})

const puntajeAsignadoTotal = computed(() => {
  return secciones.value.reduce((total, seccion) => {
    return total + getPuntajeAsignadoSeccion(seccion.id)
  }, 0)
})

const puntajeMaximoPosible = computed(() => {
  return secciones.value.reduce((total, seccion) => {
    return total + parseFloat(seccion.puntaje_max)
  }, 0)
})

const progresoTotal = computed(() => {
  const total = puntajeMaximoPosible.value
  return total > 0 ? Math.round((puntajeAsignadoTotal.value / total) * 100) : 0
})

// Métodos
const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('es-ES', options)
}

const getFileIcon = (filename) => {
  const ext = filename.split('.').pop().toLowerCase()
  const icons = {
    pdf: 'ri-file-pdf-2-line',
    jpg: 'ri-file-image-line',
    jpeg: 'ri-file-image-line',
    png: 'ri-file-image-line',
    doc: 'ri-file-word-line',
    docx: 'ri-file-word-line',
    xls: 'ri-file-excel-line',
    xlsx: 'ri-file-excel-line',
    default: 'ri-file-3-line'
  }
  return icons[ext] || icons.default
}

const getPuntajeAsignadoSeccion = (seccionId) => {
  const seccion = secciones.value.find(s => s.id === seccionId)
  if (!seccion) return 0

  return seccion.criterios.reduce((total, criterio) => {
    return total + getPuntajeAsignadoCriterio(seccionId, criterio.id)
  }, 0)
}

const getPuntajeAsignadoCriterio = (seccionId, criterioId) => {
  const archivos = archivosFormulario.value[seccionId]?.[criterioId] || []
  if (archivos.length === 0) return 0

  const criterio = secciones.value
    .find(s => s.id === seccionId)
    ?.criterios.find(c => c.id === criterioId)

  if (!criterio) return 0

  const aprobados = evaluacionArchivos.value[seccionId]?.[criterioId]?.filter(
    estado => estado === 'aprobado'
  ).length || 0

  return aprobados * (parseFloat(criterio.puntaje_por_item) || 0)
}

// Función actualizada para guardar evaluación
const guardarEvaluacion = async (finalizar = false) => {
  saving.value = true

  try {
    // Validar datos antes de enviar
    if (!validarDatos()) return

    // Preparar requisitos
    const requisitos = prepararRequisitos()

    // Preparar documentos
    const documentos = prepararDocumentos()

    // Preparar payload
    const payload = {
      requisitos,
      documentos,
      comentarios_generales: comentarioGeneral.value || '',
      finalizar
    }

    console.log('Enviando evaluación:', payload)
    console.log('el id de la postulacion', postulacionId)
    // Enviar al backend
    const response = await $api(`/postulaciones/${postulacionId}/evaluaciones`, {
      method: 'POST',
      body: payload
    })

    if (response.success) {
      evaluacionId.value = response.data.evaluacion_id

      // Mostrar mensaje de éxito
      showSuccessMessage(finalizar ? 'Evaluación finalizada exitosamente' : 'Borrador guardado')

      // Si se finalizó, redirigir o actualizar estado
      if (finalizar) {
        // Aquí puedes redirigir o actualizar la interfaz
        postulacion.value.estado = 'evaluada'
      }
    }

  } catch (error) {
    console.error('Error guardando evaluación:', error)
    showErrorMessage('Error al guardar la evaluación: ' + (error.message || 'Error desconocido'))
  } finally {
    saving.value = false
  }
}

// Función para validar datos antes de enviar
const validarDatos = () => {
  // Validar que al menos un requisito esté evaluado
  const tieneRequisitosEvaluados = Object.values(evaluacionRequisitos.ley).some(val => val) ||
    Object.values(evaluacionRequisitos.personalizado).some(val => val)

  if (!tieneRequisitosEvaluados) {
    showErrorMessage('Debe evaluar al menos un requisito')
    return false
  }

  return true
}

// Función para preparar requisitos
const prepararRequisitos = () => {
  const requisitos = []

  // Requisitos de ley
  for (const requisitoId in evaluacionRequisitos.ley) {
    if (evaluacionRequisitos.ley[requisitoId]) {
      let postulacion_documento_id = null
      const archivoAsociado = archivosRequisitos.ley?.[requisitoId]

      if (archivoAsociado?.id) {
        postulacion_documento_id = archivoAsociado.id
      }

      requisitos.push({
        requisito_id: parseInt(requisitoId),
        es_requisito_ley: true,
        estado: evaluacionRequisitos.ley[requisitoId],
        comentarios: comentariosRequisitos.ley[requisitoId] || '',
        postulacion_documento_id
      })
    }
  }

  // Requisitos personalizados
  for (const requisitoId in evaluacionRequisitos.personalizado) {
    if (evaluacionRequisitos.personalizado[requisitoId]) {
      let postulacion_documento_id = null
      const archivoAsociado = archivosRequisitos.personalizado?.[requisitoId]

      if (archivoAsociado?.id) {
        postulacion_documento_id = archivoAsociado.id
      }

      requisitos.push({
        requisito_id: parseInt(requisitoId),
        es_requisito_ley: false,
        estado: evaluacionRequisitos.personalizado[requisitoId],
        comentarios: comentariosRequisitos.personalizado[requisitoId] || '',
        postulacion_documento_id
      })
    }
  }

  return requisitos
}

// Función para preparar documentos del formulario
const prepararDocumentos = () => {
  const documentos = []

  for (const seccionId in evaluacionArchivos.value) {
    for (const criterioId in evaluacionArchivos.value[seccionId]) {
      const archivos = archivosFormulario.value[seccionId]?.[criterioId] || []
      const evaluaciones = evaluacionArchivos.value[seccionId][criterioId]

      archivos.forEach((archivo, index) => {
        if (evaluaciones[index]) {
          documentos.push({
            postulacion_documento_id: archivo.id,
            estado: evaluaciones[index],
            puntaje: calcularPuntajeDocumento(seccionId, criterioId, index),
            comentarios: comentariosArchivos.value[seccionId]?.[criterioId]?.[index] || ''
          })
        }
      })
    }
  }

  return documentos
}

// Función para calcular puntaje de un documento específico
const calcularPuntajeDocumento = (seccionId, criterioId, index) => {
  const estado = evaluacionArchivos.value[seccionId]?.[criterioId]?.[index]

  if (estado !== 'aprobado') return 0

  const criterio = secciones.value
    .find(s => s.id == seccionId)
    ?.criterios.find(c => c.id == criterioId)

  return parseFloat(criterio?.puntaje_por_item || 0)
}

// Funciones auxiliares para mostrar mensajes
const showSuccessMessage = (message) => {
  // Implementar según tu sistema de notificaciones
  console.log('SUCCESS:', message)
}

const showErrorMessage = (message) => {
  // Implementar según tu sistema de notificaciones
  console.error('ERROR:', message)
}

// Inicializar evaluaciones correctamente
const inicializarEvaluaciones = () => {
  // Inicializar evaluaciones de requisitos
  requisitosLey.value.forEach(requisito => {
    if (!evaluacionRequisitos.ley[requisito.id]) {
      evaluacionRequisitos.ley[requisito.id] = ''
    }
    if (!comentariosRequisitos.ley[requisito.id]) {
      comentariosRequisitos.ley[requisito.id] = ''
    }
  })

  requisitosPersonalizados.value.forEach(requisito => {
    if (!evaluacionRequisitos.personalizado[requisito.id]) {
      evaluacionRequisitos.personalizado[requisito.id] = ''
    }
    if (!comentariosRequisitos.personalizado[requisito.id]) {
      comentariosRequisitos.personalizado[requisito.id] = ''
    }
  })

  // Inicializar evaluaciones de documentos del formulario
  secciones.value.forEach(seccion => {
    if (!evaluacionArchivos.value[seccion.id]) {
      evaluacionArchivos.value[seccion.id] = {}
    }
    if (!comentariosArchivos.value[seccion.id]) {
      comentariosArchivos.value[seccion.id] = {}
    }

    seccion.criterios.forEach(criterio => {
      const archivos = archivosFormulario.value[seccion.id]?.[criterio.id] || []

      if (!evaluacionArchivos.value[seccion.id][criterio.id]) {
        evaluacionArchivos.value[seccion.id][criterio.id] = new Array(archivos.length).fill('')
      }
      if (!comentariosArchivos.value[seccion.id][criterio.id]) {
        comentariosArchivos.value[seccion.id][criterio.id] = new Array(archivos.length).fill('')
      }
    })
  })
}

// Cargar evaluación existente si la hay
const cargarEvaluacionExistente = async () => {
  try {
    const response = await $api(`/postulaciones/${postulacionId}/evaluaciones/mi-evaluacion`)
    console.log('datos disque', response)

    if (response.success && response.data) {
      const evaluacion = response.data
      evaluacionId.value = evaluacion.id
      comentarioGeneral.value = evaluacion.comentarios_generales || ''

      // Cargar evaluaciones de requisitos
      evaluacion.requisitos?.forEach(req => {
        const tipo = req.es_requisito_ley ? 'ley' : 'personalizado'
        const id = req.es_requisito_ley ? req.requisito_ley_id : req.requisito_id
        evaluacionRequisitos[tipo][id] = req.estado
        comentariosRequisitos[tipo][id] = req.comentarios || ''
      })

      // Cargar evaluaciones de documentos
      evaluacion.documentos?.forEach(doc => {
        // Buscar en qué sección/criterio está este documento
        const docInfo = encontrarDocumentoEnFormulario(doc.postulacion_documento_id)
        if (docInfo) {
          const index = docInfo.index
          evaluacionArchivos.value[docInfo.seccionId][docInfo.criterioId][index] = doc.estado
          comentariosArchivos.value[docInfo.seccionId][docInfo.criterioId][index] = doc.comentarios || ''
        }
      })
    }
  } catch (error) {
    console.log('No hay evaluación previa o error al cargar:', error.message)
  }
}

// Función auxiliar para encontrar documento en el formulario
const encontrarDocumentoEnFormulario = (documentoId) => {
  for (const seccionId in archivosFormulario.value) {
    for (const criterioId in archivosFormulario.value[seccionId]) {
      const archivos = archivosFormulario.value[seccionId][criterioId]
      const index = archivos.findIndex(archivo => archivo.id === documentoId)
      if (index !== -1) {
        return { seccionId, criterioId, index }
      }
    }
  }
  return null
}


const USER = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null;

const cargarPostulacionantiguio = async () => {
  try {
    //console.log('Usuario actual:', USER);
    loading.value = true;
    const response = await $api(`/postulaciones/${postulacionId}`);
    console.log('Datos de la postulación:', response);

    // Datos básicos
    postulacion.value = response.postulacion;
    postulante.value = response.postulante;
    convocatoria.value = response.postulacion.convocatoria;
    /*console.log('Convocatoria:', convocatoria.value);
    console.log('Postulante:', postulante.value.user.id);
    console.log('Postulación:', postulacion.value);*/

    // Requisitos
    requisitosLey.value = response.requisitos.ley || [];
    requisitosPersonalizados.value = response.requisitos.personalizados || [];

    // Justo después de cargar los requisitos:
    requisitosLey.value.forEach(requisito => {
      evaluacionRequisitos.ley[requisito.id] = '';
      comentariosRequisitos.ley[requisito.id] = '';
    });
    requisitosPersonalizados.value.forEach(requisito => {
      evaluacionRequisitos.personalizado[requisito.id] = '';
      comentariosRequisitos.personalizado[requisito.id] = '';
    });



    //console.log('Requisitos de ley:', requisitosLey.value);
    console.log('Requisitos personalizados:', requisitosPersonalizados.value);

    // Formulario y secciones
    secciones.value = response.formulario?.secciones || [];
    //console.log('Secciones del formulario:', secciones.value);

    // Documentos - separar requisitos de formulario
    procesarDocumentos(response.documentos);

  } catch (error) {
    console.error('Error cargando postulación:', error);
  } finally {
    loading.value = false;
  }
};
// Actualizar la función de carga principal
const cargarPostulacion = async () => {
  try {
    loading.value = true

    // Cargar datos de la postulación
    const response = await $api(`/postulaciones/${postulacionId}`)

    // Asignar datos básicos
    postulacion.value = response.postulacion
    postulante.value = response.postulante
    convocatoria.value = response.postulacion.convocatoria
    requisitosLey.value = response.requisitos.ley || []
    requisitosPersonalizados.value = response.requisitos.personalizados || []
    secciones.value = response.formulario?.secciones || []

    // Procesar documentos
    procesarDocumentos(response.documentos)

    // Inicializar estructuras de evaluación
    inicializarEvaluaciones()

    // Cargar evaluación existente si la hay
    await cargarEvaluacionExistente()

  } catch (error) {
    console.error('Error cargando postulación:', error)
    showErrorMessage('Error al cargar la postulación')
  } finally {
    loading.value = false
  }
}
const procesarDocumentos = (docs) => {
  // Limpiar estructuras manteniendo la reactividad
  Object.keys(archivosRequisitos.ley).forEach(k => delete archivosRequisitos.ley[k]);
  Object.keys(archivosRequisitos.personalizado).forEach(k => delete archivosRequisitos.personalizado[k]);
  archivosFormulario.value = {};

  console.log('Procesando documentos:', docs);

  // Procesar documentos de requisitos de ley
  if (Array.isArray(docs.requisitos_ley)) {
    docs.requisitos_ley.forEach(doc => {
      archivosRequisitos.ley[doc.requisito_id] = {
        id: doc.id,
        name: doc.nombre,
        url: doc.archivo,
        yaSubido: true
      };
    });
    //mostrar los ids de archivos de requisitos de ley en consola
    console.log('IDs de archivos de requisitos de ley:', Object.keys(archivosRequisitos.ley));
    console.log('Archivos de requisitos de ley procesados:', archivosRequisitos.ley);
  }

  // Procesar documentos de requisitos personalizados
  if (Array.isArray(docs.requisitos_personalizados)) {
    docs.requisitos_personalizados.forEach(doc => {
      archivosRequisitos.personalizado[doc.requisito_id] = {
        id: doc.id,
        name: doc.nombre,
        url: doc.archivo,
        yaSubido: true
      };
    });
  }

  // Procesar documentos de formulario/curriculum
  if (Array.isArray(docs.formulario_curriculum)) {
    docs.formulario_curriculum.forEach(doc => {
      const seccionId = doc.seccion_id;
      const criterioId = doc.criterio_id;
      // Inicializar archivosFormulario.value
      if (!archivosFormulario.value[seccionId]) {
        archivosFormulario.value[seccionId] = {};
      }
      if (!archivosFormulario.value[seccionId][criterioId]) {
        archivosFormulario.value[seccionId][criterioId] = [];
      }
      // Inicializar evaluacionArchivos.value (opcional, si lo usas)
      if (!evaluacionArchivos.value[seccionId]) {
        evaluacionArchivos.value[seccionId] = {};
      }
      if (!evaluacionArchivos.value[seccionId][criterioId]) {
        evaluacionArchivos.value[seccionId][criterioId] = [];
      }
      archivosFormulario.value[seccionId][criterioId].push({
        id: doc.id,
        name: doc.nombre,
        url: doc.archivo,
        fecha: doc.created_at,
        yaSubido: true
      });
    });
  }
  //mostrar los archivos en consola
  /*console.log('Archivos de requisitos de ley:', archivosRequisitos.ley);
  console.log('Archivos de requisitos personalizados:', archivosRequisitos.personalizado);
  */console.log('Archivos de formulario:', archivosFormulario.value);
  //  console.log('Requisitos personalizados:', requisitosPersonalizados.value);
  //console.log('Archivos personalizados:', docs.requisitos_personalizados);
};

const comentarioVisible = reactive({
  ley: null,
  personalizado: null,
})

function mostrarComentario(tipo, id) {
  comentarioVisible[tipo] = comentarioVisible[tipo] === id ? null : id
}

function mostrarComentarioArchivo(seccionId, criterioId, index) {
  if (!comentarioVisibleArchivos[seccionId]) {
    comentarioVisibleArchivos[seccionId] = {};
  }
  if (!comentarioVisibleArchivos[seccionId][criterioId]) {
    comentarioVisibleArchivos[seccionId][criterioId] = {};
  }
  // Toggle el comentario para ese archivo
  comentarioVisibleArchivos[seccionId][criterioId][index] =
    !comentarioVisibleArchivos[seccionId][criterioId][index];
}
const visorArchivo = ref({ visible: false, url: '', tipo: '' })

function abrirVisorArchivo(archivo) {
  const ext = archivo.name.split('.').pop().toLowerCase()
  visorArchivo.value.tipo = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(ext) ? 'imagen'
    : (ext === 'pdf' ? 'pdf' : 'otro')
  visorArchivo.value.url = archivo.url
  visorArchivo.value.visible = true
}
onMounted(() => {
  cargarPostulacion()
})
////////////////////////////////////////////////////////////////////

</script>

<template>
  <VRow>
    <VCol cols="12">
      <!-- Encabezado de postulación -->
      <VCard v-if="convocatoria" class="pa-6 mb-6">
        <VCardTitle class="text-h4 font-weight-bold d-flex align-center">
          <VIcon icon="ri-briefcase-line" class="me-3" />
          {{ convocatoria.titulo }} - Evaluación
        </VCardTitle>

        <VCardText>
          <VRow>
            <VCol cols="12" md="6">
              <p><strong>Postulante:</strong> {{ postulante?.user.name }} {{ postulante?.user.surname }}</p>
              <p><strong>Área:</strong> {{ convocatoria.area }}</p>
              <p><strong>Estado:</strong>
                <VChip :color="postulacion?.estado === 'en evaluacion' ? 'warning' : 'success'" size="small">
                  {{ postulacion?.estado }}
                </VChip>
              </p>
            </VCol>
            <VCol cols="12" md="6">
              <p><strong>Nota preliminar:</strong> {{ postulacion?.nota_preliminar || '0' }}%</p>
              <p><strong>Fecha postulación:</strong> {{ formatDate(postulacion?.created_at) }}</p>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <!-- Pestañas -->
      <VTabs v-model="currentTab" grow class="disable-tab-transition">
        <VTab v-for="(tab, index) in tabsData" :key="index">{{ tab }}</VTab>
      </VTabs>

      <!-- Contenido de pestañas -->
      <VCardText>
        <VWindow v-model="currentTab">

          <!-- Pestaña Requisitos -->
          <VWindowItem :value="0">
            <VTimeline>
              <!-- Requisitos de ley -->
              <VTimelineItem v-for="requisito in requisitosLey" :key="requisito.id + 'ley'"
                :dot-color="archivosRequisitos.ley?.[requisito.id] ? 'success' : 'error'" size="x-small">

                <div class="d-flex justify-space-between align-center gap-2 flex-wrap mb-2">
                  <span class="app-timeline-title">{{ requisito.descripcion }}</span>
                  <span class="app-timeline-meta">{{ requisito.req }}</span>
                </div>

                <div v-if="archivosRequisitos.ley[requisito.id]" class="mt-3">
                  <VCard class="mb-3">
                    <VCardText>
                      <div class="d-flex justify-space-between align-center">
                        <div>
                          <VIcon icon="mdi-file-document-outline" class="me-1" />
                          <a href="javascript:void(0)" class="text-primary text-decoration-underline"
                            @click="abrirVisorArchivo(archivosRequisitos.ley[requisito.id])">
                            {{ archivosRequisitos.ley[requisito.id].name }}
                          </a>
                          <!--<a :href="archivosRequisitos.ley[requisito.id].url" target="_blank"
                            class="text-primary text-decoration-underline">
                            {{ archivosRequisitos.ley[requisito.id].name }}
                          </a>-->
                        </div>
                        <div class="d-flex align-center gap-2">
                          <VSelect v-model="evaluacionRequisitos.ley[requisito.id]" :items="opcionesEvaluacion"
                            label="Estado" density="compact" style="width: 150px" />
                          <VBtn icon="ri-chat-1-line" variant="text" color="info"
                            @click="mostrarComentario('ley', requisito.id)" />
                        </div>
                      </div>
                    </VCardText>
                  </VCard>

                  <VTextarea v-if="comentarioVisible.ley === requisito.id"
                    v-model="comentariosRequisitos.ley[requisito.id]" label="Comentarios"
                    placeholder="Ingrese comentarios sobre este documento" rows="2" class="mt-2" />
                </div>
                <div v-else class="text-error" v-if="requisito.req === 'Obligatorio'">
                  <VIcon icon="mdi-alert-circle-outline" /> Documento obligatorio no enviado
                </div>
                <div v-else class="text-warning">
                  <VIcon icon="mdi-information-outline" /> Documento opcional no enviado
                </div>
              </VTimelineItem>

              <!-- Requisitos personalizados -->
              <VTimelineItem v-for="requisito in requisitosPersonalizados" :key="'per-' + requisito.id"
                :dot-color="archivosRequisitos.personalizado?.[requisito.id] ? 'success' : (requisito.tipo === 'Obligatorio' ? 'error' : 'warning')"
                size="x-small">
                <div class="d-flex justify-space-between align-center gap-2 flex-wrap mb-2">
                  <span class="app-timeline-title">{{ requisito.descripcion }}</span>
                  <span class="app-timeline-meta">{{ requisito.tipo }}</span>
                </div>

                <div v-if="archivosRequisitos.personalizado[requisito.id]" class="mt-3">
                  <VCard class="mb-3">
                    <VCardText>
                      <div class="d-flex justify-space-between align-center">
                        <div>
                          <VIcon icon="mdi-file-document-outline" class="me-1" />
                          <a href="javascript:void(0)" class="text-primary text-decoration-underline"
                            @click="abrirVisorArchivo(archivosRequisitos.personalizado[requisito.id])">
                            {{ archivosRequisitos.personalizado[requisito.id].name }}
                          </a>
                          <!--<a :href="archivosRequisitos.personalizado[requisito.id].url" target="_blank"
                            class="text-primary text-decoration-underline">
                            {{ archivosRequisitos.personalizado[requisito.id].name }}
                          </a>-->
                        </div>
                        <div class="d-flex align-center gap-2">
                          <VSelect v-model="evaluacionRequisitos.personalizado[requisito.id]"
                            :items="opcionesEvaluacion" label="Estado" density="compact" style="width: 150px" />
                          <VBtn icon="ri-chat-1-line" variant="text" color="info"
                            @click="mostrarComentario('personalizado', requisito.id)" />
                        </div>
                      </div>
                    </VCardText>
                  </VCard>

                  <VTextarea v-if="comentarioVisible.personalizado === requisito.id"
                    v-model="comentariosRequisitos.personalizado[requisito.id]" label="Comentarios"
                    placeholder="Ingrese comentarios sobre este documento" rows="2" class="mt-2" />
                </div>
                <div v-else class="text-error" v-if="requisito.tipo === 'Obligatorio'">
                  <VIcon icon="mdi-alert-circle-outline" /> Documento obligatorio no enviado
                </div>
                <div v-else class="text-warning">
                  <VIcon icon="mdi-information-outline" /> Documento opcional no enviado
                </div>
              </VTimelineItem>
            </VTimeline>
          </VWindowItem>

          <!-- Pestaña CV/Formulario -->
          <VWindowItem :value="1">
            <VCard class="mb-4">
              <VCardText>
                <div class="d-flex justify-space-between align-center flex-wrap">
                  <div>
                    <h4 class="text-h4 mb-1">Evaluación de documentos</h4>
                    <p class="mb-0">Puntaje asignado: {{ puntajeAsignadoTotal }}/{{ puntajeMaximoPosible }} pts</p>
                  </div>
                  <VChip color="primary" size="large" class="d-flex align-center">
                    <VIcon icon="ri-star-fill" start />
                    Progreso: {{ progresoTotal }}%
                  </VChip>
                </div>
                <VProgressLinear :model-value="progresoTotal" color="success" height="10" class="mt-3" striped />
              </VCardText>
            </VCard>

            <VExpansionPanels v-model="panelesAbiertos" variant="accordion">
              <VExpansionPanel v-for="seccion in secciones" :key="seccion.id" class="mb-2">
                <VExpansionPanelTitle>
                  <div class="d-flex justify-space-between align-center w-100">
                    <div class="d-flex align-center">
                      <VIcon icon="mdi-folder" color="primary" class="me-2" />
                      <span class="h-6 mb-1 font-weight-bold">{{ seccion.titulo }}</span>
                    </div>
                    <div class="d-flex align-center gap-2">
                      <span class="text-caption">
                        Pts: {{ getPuntajeAsignadoSeccion(seccion.id) }}/{{ seccion.puntaje_max }}
                      </span>
                    </div>
                  </div>
                </VExpansionPanelTitle>

                <VExpansionPanelText class="pt-0 pb-4 px-2 bg-grey-lighten-4 rounded-b-xl">
                  <div v-for="criterio in seccion.criterios" :key="criterio.id"
                    class="pa-4 mb-4 rounded-lg elevation-1 bg-white">
                    <div class="d-flex justify-space-between align-start mb-2">
                      <div>
                        <div class="text-title-1 font-weight-medium">{{ criterio.nombre }}</div>
                        <div class="text-primary">
                          {{ criterio.puntaje_por_item }} pts por item (Máx: {{ criterio.puntaje_maximo }} pts)
                        </div>
                      </div>
                      <div class="text-right font-weight-bold text-primary">
                        Pts: {{ getPuntajeAsignadoCriterio(seccion.id, criterio.id) }}/{{ criterio.puntaje_maximo }}
                      </div>
                    </div>

                    <!-- Documentos -->
                    <div v-if="archivosFormulario[Number(seccion.id)]?.[Number(criterio.id)]?.length" class="mt-3">
                      <VList lines="two" density="compact">
                        <VListItem
                          v-for="(archivo, index) in archivosFormulario[Number(seccion.id)][Number(criterio.id)]"
                          :key="'doc-' + index">
                          <template #prepend>
                            <VIcon :icon="getFileIcon(archivo.name)" color="success" />
                          </template>
                          <VListItemTitle>

                            <a href="javascript:void(0)" class="text-primary text-decoration-underline"
   @click="abrirVisorArchivo(archivo)">
  {{ archivo.name }}
</a>
                            <!--<a :href="archivo.url" target="_blank" class="text-primary text-decoration-none">
                              {{ archivo.name }}
                            </a>-->
                          </VListItemTitle>
                          <VListItemSubtitle class="text-caption">
                            Subido el {{ formatDate(archivo.fecha) }}
                          </VListItemSubtitle>
                          <template #append>
                            <div class="d-flex align-center gap-2">
                              <VSelect v-model="evaluacionArchivos[seccion.id][criterio.id][index]"
                                :items="opcionesEvaluacion" label="Estado" density="compact" style="width: 150px" />
                              <VBtn icon="ri-chat-1-line" variant="text" color="info"
                                @click="mostrarComentarioArchivo(seccion.id, criterio.id, index)" />
                            </div>
                          </template>
                          <!-- Comentario SOLO aquí, dentro del v-for de archivos -->
                          <VTextarea v-if="comentarioVisibleArchivos[seccion.id]?.[criterio.id]?.[index]"
                            v-model="comentariosArchivos[seccion.id][criterio.id][index]" label="Comentarios"
                            placeholder="Ingrese comentarios sobre este documento" rows="2" class="mt-2" />
                        </VListItem>
                      </VList>


                    </div>
                    <div v-else class="text-warning">
                      <VIcon icon="mdi-information-outline" /> No se han subido documentos
                    </div>

                  </div>
                </VExpansionPanelText>
              </VExpansionPanel>
            </VExpansionPanels>

          </VWindowItem>
        </VWindow>
        <VCard class="mt-4">
          <VCardText>
            <VTextarea v-model="comentarioGeneral" label="Comentarios generales"
              placeholder="Ingrese observaciones generales sobre la postulación" rows="3" />
          </VCardText>
        </VCard>
      </VCardText>


      <!-- Botones de acción -->
      <VCard class="mt-4">
        <VCardText>
          <div class="d-flex justify-end gap-4">
            <VBtn color="secondary" :loading="saving" @click="guardarEvaluacion(false)">
              <VIcon icon="ri-save-line" start />
              Guardar borrador
            </VBtn>
            <VBtn color="success" :loading="saving" @click="guardarEvaluacion(true)">
              <VIcon icon="ri-check-line" start />
              Finalizar evaluación
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
<!--visor de imagenes-->
<VDialog v-model="visorArchivo.visible" max-width="800">
  <VCard>
    <VCardTitle>
      <span>Vista previa de archivo</span>
      <VBtn color="error" icon="ri-close-circle-line" @click="visorArchivo.visible = false" class="float-right" />
    </VCardTitle>
    <VCardText>
      <div v-if="visorArchivo.tipo === 'imagen'" class="text-center">
        <img :src="visorArchivo.url" alt="Imagen" style="max-width:100%;max-height:70vh;" />
      </div>
      <div v-else-if="visorArchivo.tipo === 'pdf'" class="text-center">
        <iframe :src="visorArchivo.url" style="width:100%;height:70vh;" frameborder="0"></iframe>
      </div>
      <div v-else>
        <p>No se puede previsualizar este tipo de archivo.</p>
        <VBtn :href="visorArchivo.url" target="_blank" color="primary">Descargar</VBtn>
      </div>
    </VCardText>
  </VCard>
</VDialog>
  <!-- Diálogo de confirmación -->
  <VDialog v-model="confirmDialog" max-width="500">
    <VCard>
      <VCardTitle class="text-h5">Confirmar evaluación</VCardTitle>
      <VCardText>
        ¿Está seguro que desea finalizar la evaluación? Esta acción no podrá deshacerse.
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn color="error" @click="confirmDialog = false">Cancelar</VBtn>
        <VBtn color="success" @click="guardarEvaluacion(true)">Confirmar</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>


<style scoped>
/*------------------------------------------------------------------------------ */
.v-list-item__prepend {
  margin-right: 12px;
}

.app-timeline-title {
  font-weight: 600;
  font-size: 1rem;
}

.app-timeline-meta {
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  font-size: 0.75rem;
}

.app-timeline-text {
  font-size: 0.875rem;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
}
</style>