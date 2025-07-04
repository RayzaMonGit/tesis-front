<script setup>
import { onMounted } from 'vue';

const esAbierta = computed(() => estadoOriginal.value === 'Abierta');
const estadoOriginal = ref(null);


const warning = ref(null);
const success = ref(null);
const error_exists = ref(null);
const route = useRoute('convocatorias-edit-id');
const fechaInicioOriginal = ref(null);


const formularios = ref([]);

const obtenerFormularios = async () => {
  try {
    const res = await $api('/formularios-evaluacion');
    formularios.value = res;
    console.log('Formularios cargados:', formularios);
  } catch (error) {
    console.error('Error al cargar formularios:', error);
  }
};

const from = ref({
    titulo: null,
    descripcion: null,
    area: null,
    fecha_inicio: null,
    fecha_fin: null,
    plazas_disponibles: null,
    sueldo_referencial: null,
      formulario_id: null,
});
import { VExpansionPanelTitle } from 'vuetify/components';

const activeTab = ref('info');
const estados = [
    'Borrador', 'Abierta', 'Cerrado', 'Anulado'
]
// Fecha actual para validaciones
const hoy = new Date().toISOString().split('T')[0];

const error_exsist = ref(null);
const requisitosObligatorios = ref([]);
const requisitosPersonalizados = ref([]);
const FILE_DOCUMENTO = ref(null)
const NOMBRE_ARCHIVO_PREVIZUALIZA = ref(null)
const conv_selected = ref({
    titulo: '',
});
const loadDocument = ($event) => {
    const file = $event.target.files[0]
    if (!file) return

    const validTypes = [
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
    ]

    if (!validTypes.includes(file.type)) {
        warning.value = "Solo se permiten archivos PDF o Word"
        FILE_DOCUMENTO.value = null
        NOMBRE_ARCHIVO_PREVIZUALIZA.value = null
        return
    }

    FILE_DOCUMENTO.value = file

    if (file.type === "application/pdf") {
        NOMBRE_ARCHIVO_PREVIZUALIZA.value = URL.createObjectURL(file)
    } else {
        NOMBRE_ARCHIVO_PREVIZUALIZA.value = null // Word no puede mostrarse
    }
}
const edit = async () => {
    
        warning.value = null;
        if (from.value.estado === 'Abierta') {

            
        if (!confirm("¿Estás seguro de guardar? No podrás modificar los datos después.")) {
            return;
        }
    }
        // Validaciones existentes
        if (!from.value.titulo) {
            warning.value = "Se debe llenar el TÍTULO del cargo";
            return;
        } if (!from.value.area) {
            warning.value = "Se debe llenar el ÁREA del cargo";
            return;
        } if (!from.value.descripcion) {
            warning.value = "Se debe llenar la DESCRIPCIÓN del cargo";
            return;
        } if (!from.value.fecha_inicio) {
            warning.value = "Se debe llenar la FECHA INICIO del cargo";
            return;
        } if (!from.value.fecha_fin) {
            warning.value = "Se debe llenar la FECHA FIN del cargo";
            return;
        } if (!from.value.plazas_disponibles) {
            warning.value = "Se debe llenar la PLAZAS DISPONIBLES del cargo";
            return;
        } if (!FILE_DOCUMENTO.value) {
            warning.value = "Se debe seleccionar un DOCUMENTO para el cargo";
            return;
        } if (!from.value.formulario_id) {
    warning.value = "Se debe seleccionar un FORMULARIO de evaluación";
    return;
  } 

        let formData = new FormData();
        formData.append('titulo', from.value.titulo);
        formData.append('descripcion', from.value.descripcion);
        formData.append('area', from.value.area);
        formData.append('fecha_inicio', from.value.fecha_inicio);
        formData.append('fecha_fin', from.value.fecha_fin);
        formData.append('estado', from.value.estado);
        formData.append('plazas_disponibles', from.value.plazas_disponibles);
        formData.append('formulario_id', from.value.formulario_id);

        if (from.value.sueldo_referencial) {
            formData.append('sueldo_referencial', from.value.sueldo_referencial);
        }
        if (FILE_DOCUMENTO.value) {
            formData.append('documento', FILE_DOCUMENTO.value);
        }

  // Preparar y enviar los requisitos personalizados
  const requisitosLeyIds = requisitosLey.value
  .filter(req => req.seleccionado)
  .map(req => req.id)

requisitosLeyIds.forEach(id => {
  formData.append('requisitos_ley_ids[]', id)
})

// Enviar los requisitos personalizados
formData.append('requisitos_personalizados', JSON.stringify(requisitosPersonalizados.value));

console.log('Requisitos personalizados:', requisitosPersonalizados.value);

formData.append('requisitos_obligatorios', JSON.stringify(requisitosObligatorios.value));

try {
    const resp = await $api('/convocatorias/' + route.params.id + '/todos-requisitos', {
    method: 'POST',
    body: formData,
    onResponseError({ response }) {
        console.log(response)
        error_exists.value = response._data?.error || 'Error desconocido';
    }
});

        console.log(resp);
        success.value = "Convocatoria editada correctamente";
        setTimeout(() => {
            success.value = null;
            error_exists.value = null;
            warning.value = null;
        }, 1500);


    } catch (error) {
        console.error(error);
    }
}

const show = async () => {
    try {
        const resp = await $api('/convocatorias/' + route.params.id, {
            method: 'GET',
            onResponseError({ response }) {
                console.log(response);
            }
        });
        
        console.log(resp);
        conv_selected.value = resp.convocatoria;

        // Información general
        from.value.titulo = conv_selected.value.titulo;
        from.value.area = conv_selected.value.area;
        from.value.descripcion = conv_selected.value.descripcion;
        from.value.fecha_inicio = new Date(conv_selected.value.fecha_inicio).toISOString().split('T')[0];
        from.value.fecha_fin = new Date(conv_selected.value.fecha_fin).toISOString().split('T')[0];
        from.value.plazas_disponibles = conv_selected.value.plazas_disponibles;
        from.value.sueldo_referencial = conv_selected.value.sueldo_referencial;
        from.value.estado = conv_selected.value.estado;
        from.value.formulario_id = conv_selected.value.formulario_id;

        estadoOriginal.value = conv_selected.value.estado;
        
        // Documento
        FILE_DOCUMENTO.value = conv_selected.value.documento;
        NOMBRE_ARCHIVO_PREVIZUALIZA.value = conv_selected.value.documento;

        fechaInicioOriginal.value = new Date(conv_selected.value.fecha_inicio).toISOString().split('T')[0];
        
        // Cargar requisitos
        cargarConvocatoria(conv_selected.value);
    } catch (error) {
        console.error(error);
    }
}


function cargarConvocatoria(data) {
  //  Requisitos de ley (se marcan luego por ID)
  // asegurar que no sea undefined
  if (!Array.isArray(data.requisitos_ley)) {
    data.requisitos_ley = [];
  }

  // Requisitos personalizados
  requisitosPersonalizados.value = data.requisitos.map(r => ({
    id: r.id,
    nombre: r.descripcion,
    tipo: r.tipo
  }));
}


const todosSeleccionados = ref(false);

function toggleTodosObligatorios() {
  requisitosLey.value.forEach(req => {
    req.seleccionado = todosSeleccionados.value;
  });
}


const mostrarDocumento = ref(false);
/*
const requisitosLeyDesdeBackend = ref([]);
const requisitosLey = ref([]);

const fetchRequisitosLey = async () => {
  try {
    const resp = await $api('/requisitosley');

    if (resp && resp.requisitos) {
      // ✅ Aquí convertimos todos los requisitos ley, marcando como "seleccionado" los que coincidan por ID
      requisitosObligatorios.value = resp.requisitos.map(req => ({
        id: req.id,
        texto: req.descripcion,
        seleccionado: requisitosLeySeleccionados.value.includes(req.id),
      }));
    }
  } catch (error) {
    console.error("Error cargando requisitos ley:", error);
  }
};*/

const requisitosLey = ref([])
const requisitosLeySeleccionados = ref([])

const fetchTodosRequisitos = async () => {
  const resp = await $api(`/convocatorias/${route.params.id}/todos-requisitos`)
  const convocatoria = resp

  // IDs ya seleccionados
  requisitosLeySeleccionados.value = convocatoria.requisitos_ley.map(r => r.id)

  // Mostrar todos, marcando los seleccionados
  requisitosLey.value = resp.todos_requisitos_ley.map(req => ({
    id: req.id,
    texto: req.descripcion,
    seleccionado: requisitosLeySeleccionados.value.includes(req.id),
  }))

  // Requisitos personalizados
  requisitosPersonalizados.value = convocatoria.requisitos_personalizados.map(req => ({
    id: req.id,
    nombre: req.descripcion,
    tipo: req.tipo,
  }))
}

//const requisitosLeySeleccionados = ref([]); // contiene los IDs

const fetchConvocatoria = async () => {
  try {
    const resp = await $api(`/convocatorias/${route.params.id}/todos-requisitos`);

    // ✅ guardar IDs de los requisitos ley seleccionados
    requisitosLeySeleccionados.value = resp.requisitos_ley.map(r => r.id);
/*
    requisitosPersonalizados.value = resp.requisitos_personalizados.map(req => ({
    nombre: req.descripcion,
    tipo: req.tipo,
  }));*/
requisitosLey.value.forEach(req => {
    req.seleccionado = requisitosLeySeleccionados.value.includes(req.id);
  });


  } catch (error) {
    console.error('Error obteniendo requisitos de la convocatoria:', error);
  }
};


const marcarRequisitosSeleccionados = () => {
  requisitosLey.value.forEach(req => {
    req.seleccionado = requisitosLeyDesdeBackend.value.includes(req.id);
  });
};
const nuevoRequisito = ref({
  nombre: '',
  tipo: 'Obligatorio'
});

const agregarRequisito = () => {
  if (!nuevoRequisito.value.nombre) return;

  requisitosPersonalizados.value.push({
    nombre: nuevoRequisito.value.nombre,
    tipo: nuevoRequisito.value.tipo
  });

  // Limpiar campos
  nuevoRequisito.value.nombre = '';
  nuevoRequisito.value.tipo = 'Obligatorio';
};

onMounted(async () => {
     obtenerFormularios();
    show();
    await fetchConvocatoria();    // obtiene requisitos seleccionados y personalizados
    await fetchTodosRequisitos();   // carga todos los requisitos ley y marca los seleccionados
});


definePage({
    meta: {
        permissions: 'editar-convocatoria',
    }
})
</script>
<template>
    <VCard class=" refer-and-earn-dialog pa-3 pa-sm-11">


        <VCardTitle class="text-h4 text-center">Editar Convocatoria</VCardTitle>
        <VTabs v-model="activeTab" grow>
            <VTab value="info">Información</VTab>
            <VTab value="requisitos">Requisitos</VTab>
        </VTabs>

        <VDivider />

        <VWindow v-model="activeTab">
            <!-- Información -->
            <VWindowItem value="info">
                <VCardText class="pa-5">
                    <!-- Aquí va tu formulario actual -->
                    <VRow dense>
                        <!-- TÍTULO -->
                        <VCol cols="12" md="6">
                            <VTextField v-model="from.titulo" label="Título del cargo"
                                placeholder="Ej: Cargo docente computación"
                                :rules="[v => !!v || 'Este campo es obligatorio']" required :disabled="esAbierta"/>
                        </VCol>

                        <!-- ÁREA -->
                        <VCol cols="12" md="6">
                            <VTextField v-model="from.area" label="Área/Carrera" placeholder="Ej: Sistemas informáticos"
                                :rules="[v => !!v || 'Este campo es obligatorio']" required :disabled="esAbierta" />
                        </VCol>

                        <!-- PERFIL / DESCRIPCIÓN -->
                        <VCol cols="12">
                            <VTextarea v-model="from.descripcion" label="Descripción o perfil profesional"
                                placeholder="Ej: Perfil en provisión nacional..." auto-grow rows="3"
                                :rules="[v => !!v || 'Este campo es obligatorio']" required :disabled="esAbierta"/>
                        </VCol>

                        <!-- FECHA INICIO -->
                        <VCol cols="12" md="4">
                            <VTextField v-model="from.fecha_inicio" label="Fecha de inicio" type="date"
                                :min="fechaInicioOriginal" :rules="[
                                    v => !!v || 'Obligatorio',
                                    v => v >= fechaInicioOriginal || 'La fecha de inicio no puede ser anterior a la original'
                                ]" required :disabled="esAbierta"/>
                        </VCol>

                        <!-- FECHA FIN -->
                        <VCol cols="12" md="4">
                            <VTextField v-model="from.fecha_fin" label="Fecha de finalización" type="date"
                                :min="from.fecha_inicio" :rules="[
                                    v => !!v || 'Obligatorio',
                                    v => v >= from.fecha_inicio || 'La fecha de finalización no puede ser anterior a la fecha de inicio'
                                ]" required :disabled="esAbierta"/>
                        </VCol>

                        <!-- FORMULARIO -->
            <VCol cols="12" md="4">
              <v-select v-model="from.formulario_id" :items="formularios" item-title="nombre" item-value="id"
                label="Formulario de evaluación" clearable :disabled="esAbierta"/>

            </VCol>

                        <!-- PLAZAS DISPONIBLES -->
                        <VCol cols="12" md="4">
                            <VTextField v-model="from.plazas_disponibles" label="Plazas disponibles" placeholder="Ej: 1"
                                type="number" :rules="[v => v > 0 || 'Debe ser un número mayor a 0']" required :disabled="esAbierta"/>
                        </VCol>

                        <!-- SUELDO REFERENCIAL -->
                        <VCol cols="12" md="4">
                            <VTextField v-model="from.sueldo_referencial" label="Sueldo referencial"
                                placeholder="Ej: 3500 Bs." type="number"
                                :rules="[v => v >= 0 || 'Debe ser un número válido']" :disabled="esAbierta"/>
                        </VCol>
                        <!-- ESTADO -->
                        <VCol cols="12" md="4">
                            <VSelect v-model="from.estado" :items="estados" label="Estado"
                                placeholder="Selecciona un estado" :rules="[v => !!v || 'Selecciona un estado']"
                                required :disabled="esAbierta"/>
                                
                        </VCol>
<VAlert type="warning" v-if="from.estado==='Abierta'" class="mt-3">
                                <strong>
                                    Esta convocatoria está en estado <b>Abierta</b> despues de guardar no podra modificar la convocatria.
                                </strong>
                                </VAlert>

                                <VAlert type="warning" v-else-if="warning" class="mt-3">
                                <strong>{{ warning }}</strong>
                                </VAlert>
                        <!-- DOCUMENTO -->
                        <VCol cols="12" md="12">
                            <VRow>
                                <VCol cols="12">
                                    <VFileInput label="Documento de convocatoria" accept=".pdf,.doc,.docx"
                                        @change="loadDocument($event)" :disabled="esAbierta"/>
                                </VCol>

                                <!-- PREVISUALIZAR SOLO PDF -->
                                <VCol cols="12" v-if="NOMBRE_ARCHIVO_PREVIZUALIZA">
                                    <iframe :src="NOMBRE_ARCHIVO_PREVIZUALIZA" type="application/pdf" width="100%"
                                        height="400px" style="border: 1px solid #ccc; border-radius: 8px;" />
                                </VCol>

                                <!-- MOSTRAR SOLO EL NOMBRE SI ES DOC/DOCX -->
                                <VCol cols="12" v-else-if="FILE_DOCUMENTO">
                                    <p class="text-caption">Archivo seleccionado: {{ FILE_DOCUMENTO.name }}</p>
                                </VCol>
                            </VRow>

                        </VCol>
                    </VRow>
                </VCardText>
            </VWindowItem>

            <!-- Requisitos -->
            <VWindowItem value="requisitos" >
                <VCardText>
                    <VRow>
                        <VCol cols="12">
                            <p class="text-h6">Requisitos para la convocatoria</p>

                            <!-- 🔹 Sección 1: Mostrar documento -->
                            <VCheckbox v-model="mostrarDocumento" label="Ver documento adjunto" />

                            <!-- Muestra solo si se seleccionó "ver" y es PDF -->
                            <VCol cols="12" v-if="mostrarDocumento && NOMBRE_ARCHIVO_PREVIZUALIZA">
                                <iframe :src="NOMBRE_ARCHIVO_PREVIZUALIZA" type="application/pdf" width="100%"
                                    height="400px" style="border: 1px solid #ccc; border-radius: 8px;" />
                            </VCol>

                            <!-- Muestra nombre si no es PDF -->
                            <VCol cols="12" v-else-if="mostrarDocumento && FILE_DOCUMENTO">
                                <p class="text-caption">Archivo seleccionado: {{ FILE_DOCUMENTO.name }}</p>
                            </VCol>

                            <VExpansionPanels variant="accordion">
                                <!-- 🔹 Sección 2: Requisitos obligatorios con seleccionar todos -->
                                <VExpansionPanel>
                                    <VExpansionPanelTitle>Requisitos obligatorios (Ministerio de Educación)
                                    </VExpansionPanelTitle>
                                    <VExpansionPanelText>
                                        <VCheckbox v-model="todosSeleccionados" label="Seleccionar / Quitar todos"
                                            @change="toggleTodosObligatorios" :disabled="esAbierta"/>
                                        <VList dense>
                                            <VListItem v-for="(req, index) in requisitosLey" :key="'ley-' + index">
  <VCheckbox v-model="req.seleccionado" :label="req.texto" hide-details :disabled="esAbierta"/>
</VListItem>


                                        </VList>
                                    </VExpansionPanelText>
                                </VExpansionPanel>

                                <!-- 🔹 Sección 3: Requisitos personalizados -->
                                <VExpansionPanel>
                                    <VExpansionPanelTitle>Requisitos personalizados</VExpansionPanelTitle>
                                    <VExpansionPanelText>
                                        <VRow>
                                            <VCol cols="6">
                                                <VTextField label="Nombre del requisito"
                                                    v-model="nuevoRequisito.nombre" :disabled="esAbierta"/>
                                            </VCol>
                                            <VCol cols="4">
                                                <VSelect :items="['Obligatorio', 'Opcional']"
                                                    v-model="nuevoRequisito.tipo" label="Tipo" :disabled="esAbierta"/>
                                            </VCol>
                                            <VCol cols="2">
                                                <VBtn @click="agregarRequisito" color="primary" :disabled="esAbierta">Agregar</VBtn>
                                            </VCol>
                                        </VRow>
                                        <VList dense>
                                            <VListItem v-for="(req, index) in requisitosPersonalizados"
                                                :key="'perso-' + index">
                                                <VListItemTitle>{{ req.nombre }} ({{ req.tipo }})</VListItemTitle>
                                                <VBtn icon="mdi-delete"
                                                    @click="requisitosPersonalizados.splice(index, 1)" :disabled="esAbierta" />
                                            </VListItem>
                                        </VList>
                                    </VExpansionPanelText>
                                </VExpansionPanel>

                            </VExpansionPanels>
                            <!--<VList>
                  <VListItem v-for="(req, index) in requisitosPersonalizados" :key="'personalizado-' + index">
                    <VListItemTitle>{{ req.nombre }} ({{ req.tipo }})</VListItemTitle>
                    <VBtn icon="mdi-delete" @click="requisitosPersonalizados.splice(index, 1)" />
                  </VListItem>
                </VList>
  -->
                        </VCol>
                    </VRow>
                </VCardText>
            </VWindowItem>

            <VAlert type="warning" v-if="warning" class="mt-3">
                <strong>{{ warning }}</strong>
            </VAlert>

            <VAlert type="error" v-if="error_exsist" class="mt-3">
                <strong>hubo un error al guardar en el servidor</strong>
            </VAlert>

            <VAlert type="success" v-if="success" class="mt-3">
                <strong>{{ success }}</strong>
            </VAlert>
        </VWindow>
        <!-- Botón Volver a lista de convovcatorias -->
        <VCardText class="pa-5 d-flex justify-space-between align-center">
  <!-- Botón Volver a la izquierda -->
  <VBtn @click="$router.push('/convocatorias/list')" color="primary">
    <VIcon start icon="ri-arrow-left-line" />
    Volver a la lista
  </VBtn>

  <!-- Botón Guardar a la derecha -->
  <VBtn @click="edit()" color="success" :disabled="esAbierta">
    Guardar convocatoria
    <VIcon end icon="ri-checkbox-circle-line" />
  </VBtn>
</VCardText>

    </VCard>


</template>