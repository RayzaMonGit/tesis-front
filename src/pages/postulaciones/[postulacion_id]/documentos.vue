<template>
  <VRow>
    <VCol cols="12" ms="8">
      <VCard>
        <VCardItem>

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
              <!-- titulo grande y centrado de mis duemntos de postulacion -->
              <VCardText class="text-center">
                <h2 class="text-h4 font-weight-bold">Mis documentos de postulación</h2>
              </VCardText>



            </VCardText>
          </VCard>

          <VTabs v-model="currentTab" grow class="disable-tab-transition">
            <VTab v-for="(tab, index) in tabsData" :key="index">
              {{ tab }}
            </VTab>
          </VTabs>


          <VCardText>
            <VWindow v-model="currentTab">
              <VWindowItem :value="0">

                <!-- Primera pestaña: Requisitos -->
                <VTimeline>
                  <VTimelineItem v-for="requisito in requisitosLey" :key="requisito.id + 'ley'"
                    :dot-color="archivosRequisitos.ley?.[requisito.id] ? 'success' : 'primary'" size="x-small">
                    <div class="d-flex justify-space-between align-center gap-2 flex-wrap mb-2">
                      <span class="app-timeline-title">{{ requisito.nombre }}</span>
                      <span class="app-timeline-meta">Requisito: {{ requisito.req }}</span>
                    </div>

                    <div class="app-timeline-text mt-1">
                      {{ requisito.descripcion ?? 'Debe subir un documento que respalde este requisito de ley.' }}
                    </div>

                    <div class="my-2">
                      <VFileInput label="Subir archivos" accept=".pdf,.jpg,.png" prepend-icon="mdi-paperclip"
                        @change="(e) => loadFile('ley', requisito.id, e)" dense :disabled="!puedeModificar" />

                      <div v-if="archivosRequisitos.ley?.[requisito.id]?.yaSubido"
                        class="d-inline-flex align-center mt-1">
                        <VIcon icon="mdi-file-document-outline" class="me-1" />
                        <a :href="archivosRequisitos.ley?.[requisito.id].url" target="_blank"
                          class="text-primary text-decoration-underline">
                          {{ archivosRequisitos.ley?.[requisito.id].name }}
                        </a>

                      </div>
                    </div>
                  </VTimelineItem>
                  <VTimelineItem v-for="requisito in requisitosPersonalizados" :key="requisito.id + 'personalizado'"
                    :dot-color="archivosRequisitos.personalizado?.[requisito.id] ? 'success' : 'primary'"
                    size="x-small">
                    <div class="d-flex justify-space-between align-center gap-2 flex-wrap mb-2">
                      <span class="app-timeline-title">{{ requisito.nombre }}</span>
                      <span class="app-timeline-meta">Requisito: {{ requisito.tipo }}</span>
                    </div>

                    <div class="app-timeline-text mt-1">
                      {{ requisito.descripcion ?? 'Debe subir un documento que respalde este requisito de ley.' }}
                    </div>

                    <div class="my-2">
                      <VFileInput label="Subir archivos" accept=".pdf,.jpg,.png" prepend-icon="mdi-paperclip"
                        @change="(e) => loadFile('personalizado', requisito.id, e)" dense :disabled="!puedeModificar" />

                      <div v-if="archivosRequisitos.personalizado?.[requisito.id]?.yaSubido"
                        class="d-inline-flex align-center mt-1">
                        <VIcon icon="mdi-file-document-outline" class="me-1" />
                        <a :href="archivosRequisitos.personalizado?.[requisito.id].url" target="_blank"
                          class="text-primary text-decoration-underline">
                          {{ archivosRequisitos.personalizado?.[requisito.id].name }}
                        </a>

                      </div>
                    </div>
                  </VTimelineItem>

                </VTimeline>

              </VWindowItem>
              <!-- Segunda pestaña: CV o formulario evaluación-->
              <VWindowItem :value="1">
                <!-- Encabezado con progreso general -->
                <VCard class="mb-4">
                  <VCardText>
                    <div class="d-flex justify-space-between align-center flex-wrap">
                      <div>
                        <h4 class="text-h4 mb-1">Documentos para evaluación</h4>
                      </div>
                      <VChip color="primary" size="extra-large" class="d-flex align-center">
                        <VIcon icon="ri-star-fill" start />
                        Puntaje preliminar: {{ puntajeTotal }}/{{ puntajeMaximoPosible }}
                      </VChip>
                    </div>
                    <VProgressLinear v-model="progresoTotal" color="primary" height="10" class="mt-3" striped />
                  </VCardText>
                </VCard>

                <VExpansionPanels v-model="panelesAbiertos" variant="accordion">
                  <VExpansionPanel v-for="seccion in secciones" :key="seccion.id"
                    :class="{ 'seccion-completa': isSeccionCompleta(seccion.id) }" class="mb-2">
                    <!-- CABECERA DEL PANEL -->
                    <VExpansionPanelTitle>
                      <div class="d-flex justify-space-between align-center w-100">
                        <div class="d-flex align-center">
                          <VIcon :icon="isSeccionCompleta(seccion.id) ? 'mdi-check-circle' : 'mdi-folder'"
                            :color="isSeccionCompleta(seccion.id) ? 'success' : 'primary'" class="me-2" />
                          <span class="h-6 mb-1 font-weight-bold">
                            {{ seccion.titulo }}
                          </span>
                        </div>
                        <div class="d-flex align-center gap-2">
                          <span class="text-caption text-medium-emphasis">
                            Máx: {{ seccion.puntaje_max }} pts
                          </span>
                          <VProgressCircular :model-value="getProgresoSeccion(seccion.id)" size="40" width="3"
                            color="primary">
                            <small>{{ getProgresoSeccion(seccion.id) }}%</small>
                          </VProgressCircular>
                        </div>
                      </div>
                    </VExpansionPanelTitle>

                    <VExpansionPanelText class="pt-0 pb-4 px-2 bg-grey-lighten-4 rounded-b-xl">
                      <!-- ALERTA EXCESO DE PUNTAJE -->
                      <VAlert v-if="getPuntajeSeccion(seccion.id) > seccion.puntaje_max" type="error" density="compact"
                        class="mb-4">
                        ¡Has excedido el puntaje máximo de esta sección!
                      </VAlert>

                      <!-- CRITERIOS -->
                      <div v-for="criterio in seccion.criterios" :key="criterio.id"
                        class="pa-4 mb-4 rounded-lg elevation-1 bg-white">
                        <!-- CABECERA DEL CRITERIO -->
                        <div class="d-flex justify-space-between align-start mb-2">
                          <div>
                            <div class="text-title-1 font-weight-medium">{{ criterio.nombre }}</div>
                            <div class=" text-primary">{{ criterio.puntaje_por_item }}puntos por item</div>
                          </div>
                          <div class="text-right">
                            <div class="text-caption">Puntaje:</div>
                            <div class="font-weight-bold text-primary">
                              {{ getPuntajeCriterio(seccion.id, criterio.id) }}/{{ criterio.puntaje_maximo }} pts
                            </div>
                            <div class="text-caption text-medium-emphasis">
                              Archivos: {{ archivosFormulario[seccion.id]?.[criterio.id]?.length || 0 }}
                            </div>
                          </div>
                        </div>


                        <!-- ARCHIVOS SUBIDOS -->
                        <div v-if="archivosFormulario[seccion.id]?.[criterio.id]?.length" class="mt-3">
                          <VList lines="two" density="compact">
                            <VListItem v-for="(archivo, index) in archivosFormulario[seccion.id][criterio.id]"
                              :key="'subido-' + index">
                              <template #prepend>
                                <VIcon :icon="getFileIcon(archivo.name)" color="success" />
                              </template>
                              <VListItemTitle>
                                <a :href="archivo.url" target="_blank" class="text-primary text-decoration-none">
                                  {{ archivo.name }}
                                </a>
                              </VListItemTitle>
                              <VListItemSubtitle class="text-caption">
                                Subido el {{ formatDate(archivo.fecha) }}
                              </VListItemSubtitle>
                              <template #append>
                                <VBtn icon="ri-file-reduce-line" size="x-small" variant="text" color="error"
                                  @click="removeFile(seccion.id, criterio.id, index)" />
                              </template>
                            </VListItem>
                          </VList>
                        </div>
                        <!-- INPUT DE SUBIDA -->
                        <div class="d-flex align-center gap-2">
                          <VFileInput :accept="getAcceptType(criterio.tipo_documento)"
                            :label="`Subir docuemntos necesarios para la evaluación`" prepend-icon="ri-attachment-2"
                            :disabled="isFileLimitReached(seccion.id, criterio) || !puedeModificar"
                            @change="(e) => addPendiente(seccion.id, criterio, e)"
                            :multiple="getConfigCriterio(criterio).multiple"
                            counter
                            :counter-value="getConfigCriterio(criterio).maxArchivos" density="comfortable" chips
                            class="flex-grow-1" />
                          <VBtn color="primary" variant="flat" :disabled="!hasPendientes(seccion.id, criterio.id)"
                            @click="confirmarSubida(seccion.id, criterio.id)">
                            <VIcon icon="ri-upload-2-fill" start />
                            Subir
                          </VBtn>
                        </div>

                        <!-- ARCHIVOS PENDIENTES -->
                        <div v-if="archivosPendientes[seccion.id]?.[criterio.id]?.length" class="mb-3">
                          <VList lines="two" density="compact">
                            <VListItem v-for="(archivo, index) in archivosPendientes[seccion.id][criterio.id]"
                              :key="'pendiente-' + index">
                              <template #prepend>
                                <VIcon :icon="getFileIcon(archivo.name)" />
                              </template>
                              <VListItemTitle>{{ archivo.name }}</VListItemTitle>
                              <VListItemSubtitle class="text-caption">
                                {{ formatFileSize(archivo.size) }}
                              </VListItemSubtitle>
                              <template #append>
                                <VBtn icon="ri-file-close-line" size="x-small" variant="text" color="error"
                                  @click="removePendiente(seccion.id, criterio.id, index)" />
                              </template>
                            </VListItem>
                          </VList>
                        </div>

                      </div>
                    </VExpansionPanelText>
                  </VExpansionPanel>
                </VExpansionPanels>

                <!-- Resumen final -->
                <VCard class="mt-4">
                  <VCardText>
                    <div class="d-flex justify-space-between align-center flex-wrap">
                      <div>
                        <h6 class="text-h6">Resumen</h6>
                        <p class="text-caption">
                          Puntaje total alcanzado: {{ puntajeTotal }}/{{ puntajeMaximoPosible }} pts
                        </p>
                        <p v-if="estaPublicado" class="text-success">
                          <VIcon icon="ri-checkbox-circle-fill" /> Tus documentos han sido publicados y no pueden
                          modificarse
                        </p>
                      </div>


                    </div>
                  </VCardText>
                </VCard>

              </VWindowItem>
              <div class="d-flex gap-2">
                <VCardText>
                  <div class="d-flex justify-space-between align-center flex-wrap">
                    <VBtn color="primary" size="large" :loading="guardando" :disabled="!puedeEnviar || estaPublicado || !puedeEnviar"
                      @click="guardarDocumentosFormulario(false)" >
                      <VIcon icon="ri-save-line" start />
                      Guardar borrador
                    </VBtn>

                    <VBtn color="success" size="large" :loading="publicando" :disabled="!puedeEnviar || estaPublicado || !puedeEnviar"
                      @click="guardarDocumentosFormulario(true)">
                      <VIcon icon="ri-send-plane-fill" start />
                      Publicar definitivo
                    </VBtn>
                  </div>
                </VCardText>
              </div>
            </VWindow>
          </VCardText>
        </VCardItem>
      </VCard>
    </VCol>


  </VRow>

</template>
<script setup>
const estaPublicado = ref(false);

const publicando = ref(false);
const estadoPostulacion = ref('pendiente'); // Valor inicial
// Agregar esta computed property
const puedeModificar = computed(() => {
  return estadoPostulacion.value === 'pendiente';
});

//////////////////////////////////////////////////////////////////////////
const getConfigCriterio = (criterio) => {
  const puntajeItem = parseFloat(criterio.puntaje_por_item) || 0;
  let puntajeMaximo;

  // Caso 1: puntaje_maximo es válido (mayor que 0)
  if (criterio.puntaje_maximo > 0) {
    puntajeMaximo = parseFloat(criterio.puntaje_maximo);
  }
  // Caso 2: Usar puntaje máximo de la sección si no hay máximo en criterio
  else {
    const seccion = secciones.value.find(s => s.id === criterio.seccion_id);
    puntajeMaximo = seccion ? parseFloat(seccion.puntaje_max) : Infinity;
  }

  return {
    puntajeItem,
    puntajeMaximo,
    // Máximo de archivos calculado seguro:
    maxArchivos: puntajeItem > 0
      ? Math.ceil(puntajeMaximo / puntajeItem)
      : 1, // Si no hay puntaje por item, solo 1 archivo
    multiple: criterio.multiple || (puntajeItem > 0 && puntajeItem < puntajeMaximo)
  };
};

// Estado reactivo
const panelesAbiertos = ref([])
const archivosPendientes = ref({})
const archivosFormulario = ref({})
const guardando = ref(false)
const secciones = ref([
  {
    id: 1,
    titulo: 'Formación Académica',
    puntaje_max: 30,
    criterios: [
      {
        id: 1,
        nombre: 'Títulos profesionales',
        descripcion: 'Suba copias de sus títulos en PDF',
        tipo_documento: 'PDF',
        puntaje_maximo: 20,
        multiple: true,
        requerido: true
      },
      {
        id: 2,
        nombre: 'Certificados de cursos',
        descripcion: 'Documentos que acrediten formación adicional',
        tipo_documento: 'PDF',
        puntaje_maximo: 10,
        multiple: true,
        requerido: false
      }
    ]
  },
  {
    id: 2,
    titulo: 'Experiencia Laboral',
    puntaje_max: 40,
    criterios: [
      {
        id: 3,
        nombre: 'Certificados laborales',
        descripcion: 'Documentos que acrediten su experiencia',
        tipo_documento: 'PDF',
        puntaje_maximo: 30,
        multiple: true,
        requerido: true
      },
      {
        id: 4,
        nombre: 'Cartas de recomendación',
        descripcion: 'Cartas de empleadores anteriores',
        tipo_documento: 'PDF',
        puntaje_maximo: 10,
        multiple: false,
        requerido: false
      }
    ]
  }
])

// Cálculos computados
const totalArchivosSubidos = computed(() => {
  return Object.values(archivosFormulario.value).reduce((total, seccion) => {
    return total + Object.values(seccion).reduce((subtotal, archivos) => {
      return subtotal + archivos.length
    }, 0)
  }, 0)
})

const totalArchivosRequeridos = computed(() => {
  return secciones.value.reduce((total, seccion) => {
    return total + seccion.criterios.filter(c => c.requerido).length
  }, 0)
})

const progresoTotal = computed(() => {
  const total = puntajeMaximoPosible.value
  return total > 0 ? Math.round((puntajeTotal.value / total) * 100) : 0
})

const puedeEnviar = computed(() => {
  return totalArchivosSubidos.value >= totalArchivosRequeridos.value
})

// Métodos
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

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i])
}

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('es-ES', options)
}

const getAcceptType = (tipo) => {
  return tipo === 'PDF' ? '.pdf' : '.pdf,.jpg,.jpeg,.png'
}

const getMaxFiles = (criterio) => {
  // Si el puntaje por item es IGUAL al máximo, solo 1 archivo
  if (parseFloat(criterio.puntaje_por_item) === parseFloat(criterio.puntaje_maximo)) {
    return 1;
  }
  // Si no, calcula cuántos archivos necesita para alcanzar el máximo
  return Math.ceil(parseFloat(criterio.puntaje_maximo) / parseFloat(criterio.puntaje_por_item));
};
/*
const isFileLimitReached = (seccionId, criterio) => {
  const archivosSubidos = archivosFormulario.value[seccionId]?.[criterio.id]?.length || 0
  const archivosPend = archivosPendientes.value[seccionId]?.[criterio.id]?.length || 0
  return (archivosSubidos + archivosPend) >= getMaxFiles(criterio)
}*/
const isFileLimitReached = (seccionId, criterio) => {
  // Si el puntaje máximo del criterio es 0, nunca bloquear el input
  if (!criterio.puntaje_maximo || criterio.puntaje_maximo == 0) return false;

  const archivosSubidos = archivosFormulario.value[seccionId]?.[criterio.id]?.length || 0
  const archivosPend = archivosPendientes.value[seccionId]?.[criterio.id]?.length || 0
  return (archivosSubidos + archivosPend) >= getMaxFiles(criterio)
}

const hasPendientes = (seccionId, criterioId) => {
  return !!archivosPendientes.value[seccionId]?.[criterioId]?.length
}

const addPendiente = (seccionId, criterio, event) => {
  const files = event.target.files || event;
  if (!files.length) return;

  const config = getConfigCriterio(criterio);
  const archivosActuales = archivosFormulario.value[seccionId]?.[criterio.id] || [];

  // Calcular puntaje actual y espacio disponible
  const puntajeActual = archivosActuales.length * config.puntajeItem;
  const espacioDisponible = config.puntajeMaximo - puntajeActual;
  const maxArchivosPermitidos = Math.floor(espacioDisponible / config.puntajeItem);

  // Filtrar archivos a agregar
  const filesToAdd = Array.from(files).slice(0, maxArchivosPermitidos);

  if (!archivosPendientes.value[seccionId]) {
    archivosPendientes.value[seccionId] = {};
  }

  archivosPendientes.value[seccionId][criterio.id] = [
    ...(archivosPendientes.value[seccionId][criterio.id] || []),
    ...filesToAdd
  ];

  event.target.value = '';
};

const removePendiente = (seccionId, criterioId, index) => {
  archivosPendientes.value[seccionId][criterioId].splice(index, 1)
}

const confirmarSubida = (seccionId, criterioId) => {
  const seccion = secciones.value.find(s => s.id === seccionId);
  const criterio = seccion.criterios.find(c => c.id === criterioId);

  // Calcular cómo cambiaría el puntaje
  const puntajeActual = getPuntajeSeccion(seccionId);
  const nuevoPuntaje = puntajeActual + parseFloat(criterio.puntaje_por_item);

  if (nuevoPuntaje > parseFloat(seccion.puntaje_max)) {
    alert(`¡Alerta! Subir este archivo excedería el puntaje máximo de la sección (${seccion.puntaje_max} pts)`);
    return;
  }
  if (!archivosPendientes.value[seccionId]?.[criterioId]?.length) return

  if (!archivosFormulario.value[seccionId]) {
    archivosFormulario.value[seccionId] = {}
  }

  if (!archivosFormulario.value[seccionId][criterioId]) {
    archivosFormulario.value[seccionId][criterioId] = []
  }

  const nuevosArchivos = archivosPendientes.value[seccionId][criterioId].map(file => ({
    name: file.name,
    size: file.size,
    type: file.type,
    fecha: new Date().toISOString(),
    url: URL.createObjectURL(file),
    file // Objeto File original
  }))

  archivosFormulario.value[seccionId][criterioId] = [
    ...archivosFormulario.value[seccionId][criterioId],
    ...nuevosArchivos
  ]

  archivosPendientes.value[seccionId][criterioId] = []
}

const removeFile = (seccionId, criterioId, index) => {
  archivosFormulario.value[seccionId][criterioId].splice(index, 1)
}

const isSeccionCompleta = (seccionId) => {
  const seccion = secciones.value.find(s => s.id === seccionId)
  if (!seccion) return false

  return seccion.criterios.filter(c => c.requerido).every(criterio => {
    return archivosFormulario.value[seccionId]?.[criterio.id]?.length > 0
  })
}

const getProgresoSeccion = (seccionId) => {
  const seccion = secciones.value.find(s => s.id === seccionId)
  if (!seccion) return 0

  const total = seccion.puntaje_max
  const completados = getPuntajeSeccion(seccionId)

  return Math.round((completados / total) * 100)
}

const getPuntajeCriterio = (seccionId, criterioId) => {
  const archivos = archivosFormulario.value[seccionId]?.[criterioId] || [];
  if (archivos.length === 0) return 0;

  const criterio = secciones.value
    .find(s => s.id === seccionId)
    ?.criterios.find(c => c.id === criterioId);

  const config = getConfigCriterio(criterio);

  // Puntaje sin exceder el máximo
  return Math.min(
    archivos.length * config.puntajeItem,
    config.puntajeMaximo
  );
};
/*

const getPuntajeCriterio = (seccionId, criterioId) => {
  const archivos = archivosFormulario.value[seccionId]?.[criterioId] || [];
  if (archivos.length === 0) return 0;

  const criterio = secciones.value
    .find(s => s.id === seccionId)
    ?.criterios.find(c => c.id === criterioId);

  const config = getConfigCriterio(criterio);

  // Si el puntaje máximo del criterio es 0, no suma puntaje
  if (!criterio.puntaje_maximo || criterio.puntaje_maximo == 0) return 0;

  // Puntaje sin exceder el máximo
  return Math.min(
    archivos.length * config.puntajeItem,
    config.puntajeMaximo
  );
};*/


const getPuntajeSeccion = (seccionId) => {
  const seccion = secciones.value.find(s => s.id === seccionId);
  if (!seccion) return 0;

  return seccion.criterios.reduce((total, criterio) => {
    return total + getPuntajeCriterio(seccionId, criterio.id);
  }, 0);
};

const puntajeTotal = computed(() => {
  return secciones.value.reduce((total, seccion) => {
    return total + getPuntajeSeccion(seccion.id);
  }, 0);
});

const puntajeMaximoPosible = computed(() => {
  return secciones.value.reduce((total, seccion) => {
    return total + parseFloat(seccion.puntaje_max);
  }, 0);
});


const guardarDocumentosFormulario = async (publicar = false) => {
  if (publicar) {
    publicando.value = true;
  } else {
    guardando.value = true;
  }
  //const notafinal=progresoTotal.value;
  const notafinal = puntajeTotal.value;
  console.log('Nota preliminar (nota real):', notafinal);
  try {
    // 1. Guardar requisitos básicos
    await guardarRequisitos();

    // 2. Guardar documentos del currículum (formulario)
    for (const seccionId in archivosFormulario.value) {
      for (const criterioId in archivosFormulario.value[seccionId]) {
        const archivos = archivosFormulario.value[seccionId][criterioId];
        
        for (const archivo of archivos) {
          // Solo subir archivos nuevos (que tienen objeto File)
          if (archivo.file) {
            const formData = new FormData();
            formData.append('postulacion_id', postulacionId);
            formData.append('seccion_id', seccionId);
            formData.append('criterio_id', criterioId);
            formData.append('archivo', archivo.file);
            formData.append('nombre', archivo.name);
            formData.append('tipo', 'curriculum'); // Para diferenciarlos
            
            
            await $api('/postulacion-documentos', {
              method: 'POST',
              body: formData,
              headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('access_token')}`
              }
            });
          }
          await $api(`/postulaciones/${postulacionId}/cambiar-estado`, {
      method: 'POST',
      body: { 
        nota_preliminar: notafinal,
      },
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
      }
    });
        }
      }
    }

    if (publicar) {
      // Actualizar estado a "en evaluacion"
      await $api(`/postulaciones/${postulacionId}/cambiar-estado`, {
        method: 'POST',
        body: { estado: 'en evaluacion',
          nota_preliminar: notafinal,
        },
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('access_token')}`
        }
      });
      estadoPostulacion.value = 'en evaluacion';
    }
    
  } catch (error) {
    console.error(`Error al ${publicar ? 'publicar' : 'guardar'} documentos:`, error);
  } finally {
    if (publicar) {
      publicando.value = false;
    } else {
      guardando.value = false;
    }
  }
};


////////////////////////////////////////////////////////////////////////////////////////////////////////
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { VCardText, VExpansionPanelTitle } from 'vuetify/components';

const tabsData = ['Requisitos', 'Hoja de Vida/CV']
const currentTab = ref(0)
const panelStatus = ref(0)

const route = useRoute()
const postulacionId = route.params.postulacion_id

console.log('ID de postulación recibido:', postulacionId)

const postulacion = ref(null)
const postulante = ref(null)
const usuario = ref(null)
const convocatoria = ref(null)
const requisitosLey = ref([])
const requisitosPersonalizados = ref([])
const evaluadores = ref([])
const formulario = ref(null)
//const secciones = ref([])
const loading = ref(true)
const requisitosTotales = ref([])

//const archivosRequisitos = ref({});

const eliminarDocumentoPrevio = async (postulacionId, requisitoId) => {
  try {
    await $api('/postulacion-documentos/eliminar', {
      method: 'DELETE',
      body: {
        postulacion_id: postulacionId,
        requisito_id: requisitoId,
      },
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('access_token')}`,
      }
    });
    console.log(`🗑️ Documento previo eliminado para requisito ${requisitoId}`);
  } catch (err) {
    console.warn(`⚠️ No se pudo eliminar el documento previo para requisito ${requisitoId}`, err);
  }
};


const from = ref({
  postulacion_id: parseInt(postulacionId),
  requisito_id: null,
  //archivo: null,
  nombre: '',
  es_requisito_ley: false,
  es_requisito_personalizado: false,
})


const archivosRequisitos = reactive({
  ley: {},
  personalizado: {},
});


function loadFile(tipo, id, event) {
  const file = event?.target?.files?.[0] || event?.[0]; // compatible con VFileInput y nativo

  if (file instanceof File) {
    archivosRequisitos[tipo][id] = file;
    console.log(`📎 Archivo cargado para requisito [${tipo}] ${id}:`, file);
  } else {
    console.warn(`❌ Archivo inválido para requisito [${tipo}] ${id}`, event);
    archivosRequisitos[tipo][id] = null;
  }
}



const guardarRequisitos = async () => {
  guardando.value = true;

  // Guardar requisitos de ley
  for (const [requisitoId, archivo] of Object.entries(archivosRequisitos.ley)) {
    if (!(archivo instanceof File)) continue;
    await eliminarDocumentoPrevio(postulacionId, requisitoId);

    const formData = new FormData();


    formData.append('postulacion_id', postulacionId);
    formData.append('requisito_id', requisitoId);
    formData.append('archivo', archivo);
    formData.append('nombre', archivo.name);
    formData.append('es_requisito_ley', '1');
    formData.append('es_requisito_personalizado', '0');

    try {
      const res = await $api('/postulacion-documentos', {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          //'Authorization': `Bearer ${localStorage.getItem('access_token')}`
        }
      });
      console.log(`✅ Guardado requisito de ley ${requisitoId}`, res);
      cargarDocumentosGuardados();
    } catch (err) {
      console.error(`❌ Error guardando requisito de ley ${requisitoId}`, err);
    }
  }

  // Guardar requisitos personalizados
  for (const [requisitoId, archivo] of Object.entries(archivosRequisitos.personalizado)) {
    if (!(archivo instanceof File)) continue;

    const formData = new FormData();
    formData.append('postulacion_id', postulacionId);
    formData.append('requisito_id', requisitoId);
    formData.append('archivo', archivo);
    formData.append('nombre', archivo.name);
    formData.append('es_requisito_ley', '0');
    formData.append('es_requisito_personalizado', '1');

    try {
      const res = await $api('/postulacion-documentos', {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('access_token')}`
        }
      });
      console.log(`✅ Guardado requisito personalizado ${requisitoId}`, res);
    } catch (err) {
      console.error(`❌ Error guardando requisito personalizado ${requisitoId}`, err);
    }
  }

  guardando.value = false;
};


const requisitos = async () => {
  try {
    console.log('Cargando convocatoria para postulación:', postulacionId)
    const response = await $api(`/postulaciones/${postulacionId}`)
    console.log('Respuesta de la API:', response)
    
    // Postulación completa
    postulacion.value = response.postulacion
    //console.log('Datos de la postulación:', postulacion.value)

    // Subdatos
    postulante.value= postulacion.value.postulante
    //console.log('Datos del postulante:', postulante.value)

    usuario.value = postulacion.value.postulante?.user ?? null
    //console.log('Datos del usuario:', usuario.value)

    convocatoria.value = postulacion.value.convocatoria
    //console.log('Datos de la convocatoria:', convocatoria.value)


    // Subdatos de la convocatoria
    requisitosLey.value = postulacion.value.convocatoria?.requisitos_ley ?? []
    requisitosPersonalizados.value = postulacion.value.convocatoria?.requisitos ?? []
    evaluadores.value = postulacion.value.convocatoria?.evaluadores ?? []

    // Formulario y secciones
    formulario.value = postulacion.value.convocatoria?.formulario ?? null
    secciones.value = postulacion.value.convocatoria?.formulario?.secciones ?? []
    /*
        console.log('Postulación cargada:', postulacion.value)
        console.log('Postulante:', postulante.value)
        console.log('Usuario:', usuario.value)
        console.log('Convocatoria:', convocatoria.value)
        */console.log('Requisitos de ley:', requisitosLey.value)
    console.log('Requisitos personalizados:', requisitosPersonalizados.value)
    /*console.log('Evaluadores:', evaluadores.value)
    console.log('Formulario:', formulario.value)
    console.log('Secciones del formulario:', secciones.value)
*/

    requisitosTotales.value = [
      ...requisitosLey.value.map(r => ({
        id: r.id,
        descripcion: r.descripcion,
        //es_requisito_ley: true,
        //es_requisito_personalizado: false,
      })),
      ...requisitosPersonalizados.value.map(r => ({
        id: r.id,
        descripcion: r.descripcion,
        //es_requisito_ley: false,
        //es_requisito_personalizado: true,
      })),
    ]

  } catch (error) {
    console.error('Error al cargar la convocatoria:', error)
  } finally {
    loading.value = false
  }
}

const cargarDocumentosGuardados = async () => {
  try {
    const res = await $api(`/postulaciones/${postulacionId}/documentos`);
    const postulacionRes = await $api(`/postulaciones/${postulacionId}`);
    
    console.log('Documentos previos cargados:', res.data);
    console.log('Estado de la postulación:', postulacionRes.postulacion.estado);
    
    estadoPostulacion.value = postulacionRes.postulacion.estado || 'pendiente';

    res.data.forEach(doc => {
      // Documentos de CV (tienen seccion_id y criterio_id)
      if (doc.seccion_id && doc.criterio_id) {
        if (!archivosFormulario.value[doc.seccion_id]) {
          archivosFormulario.value[doc.seccion_id] = {};
        }
        if (!archivosFormulario.value[doc.seccion_id][doc.criterio_id]) {
          archivosFormulario.value[doc.seccion_id][doc.criterio_id] = [];
        }
        
        archivosFormulario.value[doc.seccion_id][doc.criterio_id].push({
          name: doc.nombre,
          url: doc.archivo,
          fecha: doc.created_at,
          yaSubido: true // Importante para identificar que ya está en servidor
        });
      } 
      // Documentos de requisitos (tienen requisito_id)
      else if (doc.requisito_id) {
        const tipo = doc.es_requisito_ley ? 'ley' : 'personalizado';
        if (!archivosRequisitos[tipo]) archivosRequisitos[tipo] = {};
        
        archivosRequisitos[tipo][doc.requisito_id] = {
          name: doc.nombre,
          url: doc.archivo,
          yaSubido: true
        };
      }
    });

    console.log('Archivos formulario cargados:', archivosFormulario.value);
    console.log('Archivos requisitos cargados:', archivosRequisitos);
    
  } catch (err) {
    console.error('Error al cargar documentos previos:', err);
  }
};


onMounted(async () => {
  requisitos()
  await cargarDocumentosGuardados()


})
</script>

<style scoped>
.criterio-card {
  padding: 16px;
  border-radius: 8px;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  transition: all 0.3s ease;
}

.criterio-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.seccion-completa {
  border-left: 4px solid rgb(var(--v-theme-success));
}

.v-list-item__prepend {
  margin-right: 12px;
}

@media (prefers-color-scheme: dark) {
  .criterio-card {
    background-color: rgba(var(--v-theme-on-surface), 0.02);
  }
}
</style>