<script setup>
const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
    userSelected: {
        type: Object,
        required: true,

    },
})
const emit = defineEmits(['update:isDialogVisible', 'deletePostulant'])
const dialogVisibleUpdate = val => {
    emit('update:isDialogVisible', val)
}
const error_exsist = ref(null);
const warning = ref(null);
const success = ref(null);
const deleted = async () => {

    try {
        const resp = await $api('/postulantes/' + user_selected.value.id, {
            method: 'DELETE',
            onResponseError({ response }) {
                console.log(response);
                error_exsist.value = response._data.error;
            }
        })
        console.log(resp)

        success.value = "El postulante se ha eliminado correctamente";
        emit('deletePostulant',user_selected.value)
        emit('update:isDialogVisible', false)

    } catch (error) {
        console.log(error);
        error_exsist.value = error;
    }


}
const user_selected = ref(null);
onMounted(() => {
    user_selected.value = props.userSelected;
    console.log(user_selected.value);
})
</script>

<template> <!-- añadir roles-->
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2" v-if="user_selected">
                        Eliminar usuario:{{ user_selected.id }}
                    </h4>
                </div>
                <p v-if="user_selected">¿Estas seguro de eliminar el postulnte? "{{ user_selected.full_name }}"?</p>

                <VAlert type="error" v-if="error_exsist" class="mt-3">
                    <strong>hubo un error al guardar en el servidor</strong>
                </VAlert>

                <VAlert type="warning" v-if="success" class="mt-3">
                    <strong>{{ success }}</strong>
                </VAlert>


            </VCardText>
            <VCardText class="pa-5">
                <!-- boton eliminar rol-->
                <VBtn @click="deleted()" color="error">
                    Eliminar Postulante
                    <VIcon end icon="ri-checkbox-circle-line" />
                </VBtn>

            </VCardText>
        </VCard>
    </VDialog>
</template>

<style lang="scss">
.refer-link-input {
    .v-field--appended {
        padding-inline-end: 0;
    }

    .v-field__append-inner {
        padding-block-start: 0.125rem;
    }
}
</style>
