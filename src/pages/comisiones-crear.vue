<script setup>
const convocatorias = ref([])
const evaluadores = ref([])
const evaluadores1 = ref([])
const evaluadoresSeleccionados = ref([])
const modalVisible = ref(false)
const convocatoriaSeleccionada = ref(null)
const busquedaEvaluador = ref('');
const success = ref(null)
const error_exists = ref(null)
//para agregar nuevos perfiles desde aqui
const abrirMiniDialog = ref(false)
const nuevoEvaluador = ref({ name: '', surname: '', email: '' })

const crearEvaluadorRapido = async () => {
    if (!nuevoEvaluador.value.name || !nuevoEvaluador.value.surname || !nuevoEvaluador.value.email) {
        error_exists.value = 'Completa todos los campos';
        return;
    }
    // Busca el id del rol "Evaluador"
    const evaluadorRole = roles.value.find(r => r.name === 'Evaluador')
    if (!evaluadorRole) {
        error_exists.value = 'No se encontró el rol Evaluador'
        return
    }
    const roleId = evaluadorRole.id

    try {
        const resp = await $api('/staffs', {
            method: 'POST',
            body: {
                name: nuevoEvaluador.value.name,
                surname: nuevoEvaluador.value.surname,
                email: nuevoEvaluador.value.email,
                password: '123456',
                role_id: roleId,
                gender: 'O',
                telefono: '00000000',
                tipo_doc: 'CI',
                n_doc: '00000000',
            }
        })
        evaluadores1.value.push({
            ...resp.user,
            role_name: 'Evaluador',
            avatar: null,
        })
        abrirMiniDialog.value = false
        nuevoEvaluador.value = { name: '', surname: '', email: '' }
        evaluadoresSeleccionados.value.push(resp.user.id)
    } catch (e) {
        error_exists.value = 'No se pudo crear el evaluador'
    }
}


const fetchConvocatorias = async () => {
    try {
        const resp = await $api('/convocatorias?')
        convocatorias.value = resp.data || []
    } catch (error) {
        console.error('Error al cargar convocatorias:', error)
    }
}
const abrirModal = async (conv) => {
    convocatoriaSeleccionada.value = conv
    modalVisible.value = true
    await fetchEvaluadores()
    // Traer los evaluadores asignados actualmente
    const resp = await $api(`/convocatorias/${conv.id}/comision`)
    evaluadoresSeleccionados.value = resp.evaluadores.map(e => e.id) // marcar como seleccionados
    console.log(evaluadoresSeleccionados.value)
}

const fetchEvaluadores = async () => {
    try {
        const resp = await $api('/staffs?')
        const todos = resp.users.data
        evaluadores1.value = todos.filter(user => user.role_name === 'Evaluador') // o 'Evaluador'
        console.log(evaluadores1)
    } catch (error) {
        console.error('Error al cargar evaluadores:', error)
    }
}


const getEstadoColor = (estado) => {
    if (estado === 'Abierta') return 'success'
    if (estado === 'Cerrado') return 'error'
    if (estado === 'Borrador') return 'warning'
    return 'info'
}
function avatarText(fullName) {
    if (!fullName) return ''
    return fullName
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
}

const asignarEvaluadores = async () => {
    try {
        await $api(`/convocatorias/${convocatoriaSeleccionada.value.id}/comision`, {
            method: 'POST',
            body: {
                evaluadores: evaluadoresSeleccionados.value,
            },
        })

        // Notificación
        success.value = 'Evaluadores asignados correctamente'
        setTimeout(() => {
            success.value = null
            error_exists.value = null
            fetchConvocatorias()

            modalVisible.value = false
            evaluadoresSeleccionados.value = []


        }, 1000)

    } catch (error) {
        console.error('Error al asignar evaluadores:', error)
        error_exists.value = 'Hubo un problema al asignar evaluadores'
    }
}

const evaluadoresFiltrados = computed(() => {
    if (!busquedaEvaluador.value) return evaluadores1.value

    return evaluadores1.value.filter(user =>
        `${user.name} ${user.surname} ${user.email}`
            .toLowerCase()
            .includes(busquedaEvaluador.value.toLowerCase())
    )
})

const roles = ref([])
const searchQuery = ref(null);
const fetchRoles = async () => {
    try {
        const resp = await $api('/staffs?search=' + (searchQuery.value ? searchQuery.value : ''), {
            method: 'GET',
            onResponseError({ response }) {
                console.log(response);
            }
        })
        console.log('respuesta api', resp);

        //data.value = resp.users.data;
        roles.value = resp.roles;
        console.log(roles.value);
    } catch (e) {
        console.error('Error al cargar roles', e)
    }
}
onMounted(() => {
    fetchRoles()
    fetchConvocatorias()
})
</script>
<template>
    <div>
        <h2 class="text-h4 font-weight-bold mb-6">Asignar Comisiones Evaluadoras</h2>

        <VRow>
            <VCol v-for="conv in convocatorias" :key="conv.id" cols="12" md="6" lg="4">
                <VCard>
                    <VCardTitle>{{ conv.titulo }}</VCardTitle>
                    <VCardText>
                        Área: {{ conv.area }}<br>
                        Fecha: {{ new Date(conv.fecha_inicio).toLocaleDateString() }} - {{ new
                            Date(conv.fecha_fin).toLocaleDateString() }}<br>
                        Estado: <VChip :color="getEstadoColor(conv.estado)">{{ conv.estado }}</VChip>


                    </VCardText>
                    <VCardActions>
                        <VBtn color="primary" @click="abrirModal(conv)">Añadir Evaluadores</VBtn>
                    </VCardActions>
                    <VCardText class="pt-0">
                        <div class="v-avatar-group d-flex align-center">
                            <VAvatar v-for="(evaluator, index) in conv.evaluadores.slice(0, 5)" :key="evaluator.id"
                                :color="evaluator.avatar ? '' : 'primary'"
                                :class="evaluator.avatar ? '' : 'v-avatar-light-bg primary--text'"
                                :variant="!evaluator.avatar ? 'tonal' : undefined">
                                <VImg v-if="evaluator.avatar" :src="evaluator.avatar" />
                                <span v-else class="text-sm">
                                    {{ avatarText(`${evaluator.name} ${evaluator.surname}`) }}
                                </span>
                                <VTooltip activator="parent" location="top">
                                    {{ evaluator.name }} {{ evaluator.surname }}
                                </VTooltip>
                            </VAvatar>
                            
                            <VAvatar v-if="conv.evaluadores.length > 5"
                                :color="$vuetify.theme.current.dark ? '#383B55' : '#F0EFF0'">
                                +{{ conv.evaluadores.length - 5 }}
                            </VAvatar>


                        </div>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>

        <!-- Modal para seleccionar usuarios -->
        <VDialog v-model="modalVisible" max-width="600px">
            <VCard>
                <VCardTitle>Seleccionar Evaluadores para {{ convocatoriaSeleccionada?.titulo }}</VCardTitle>
                <VCardText>
                    <!-- Buscador -->
                    <VTextField v-model="busquedaEvaluador" label="Buscar evaluador..."
                        prepend-inner-icon="ri-search-line" dense hide-details class="mb-4" />
                    <VList>
                        <VListItem v-for="user in evaluadoresFiltrados" :key="user.id"
                            class="py-3 px-2rounded border mb-2" style="transition: background 0.3s"
                            :class="{ 'bg-grey-lighten-4': evaluadoresSeleccionados.includes(user.id) }">
                            <template #prepend>
                                <VAvatar
                                    size="40"
                                    :color="user.avatar ? '' : 'primary'"
                                    :class="user.avatar ? '' : 'v-avatar-light-bg primary--text'"
                                    :variant="!user.avatar ? 'tonal' : undefined"
                                    >
                                    <VImg v-if="user.avatar" :src="user.avatar" />
                                    <span v-else class="text-sm">
                                        {{ avatarText(`${user.name} ${user.surname}`) }}
                                    </span>
                                    <VTooltip activator="parent" location="top">
                                        {{ user.name }} {{ user.surname }}
                                    </VTooltip>
                                    </VAvatar>
                            </template>

                            <VListItemTitle class="font-weight-medium">
                                {{ user.name }} {{ user.surname }}
                            </VListItemTitle>
                            <VListItemSubtitle class="text-caption">
                                {{ user.email }}
                            </VListItemSubtitle>

                            <template #append>
                                <VCheckbox v-model="evaluadoresSeleccionados" :value="user.id" hide-details
                                    density="compact" />
                            </template>
                        </VListItem>
                        <VBtn color="secondary" class="mb-3" @click="abrirMiniDialog = true" variant="outlined" block>
                            Agregar Evaluador rápido
                        </VBtn>
                    </VList>
                </VCardText>

                <VCardActions>
                    <VSpacer />
                    <VBtn text @click="modalVisible = false">Cancelar</VBtn>
                    <VBtn color="success" @click="asignarEvaluadores">Asignar</VBtn>
                </VCardActions>



                <VAlert v-if="success" type="success" class="mb-4" border="start" variant="tonal" color="success">
                    {{ success }}
                </VAlert>

                <VAlert v-if="error_exists" type="error" class="mb-4" border="start" variant="tonal" color="error">
                    {{ error_exists }}
                </VAlert>

            </VCard>
        </VDialog>
        <VDialog v-model="abrirMiniDialog" max-width="400px">
            <VCard>
                <VCardTitle>Nuevo Evaluador</VCardTitle>
                <VCardText>
                    <VTextField v-model="nuevoEvaluador.name" label="Nombre" />
                    <VTextField v-model="nuevoEvaluador.surname" label="Apellido" />
                    <VTextField v-model="nuevoEvaluador.email" label="Email" />
                </VCardText>
                <VCardActions>
                    <VSpacer />
                    <VBtn text @click="abrirMiniDialog = false">Cancelar</VBtn>
                    <VBtn color="success" @click="crearEvaluadorRapido">Crear</VBtn>
                </VCardActions>
            </VCard>
        </VDialog>


    </div>
</template>