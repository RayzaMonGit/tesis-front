<script setup>
const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
    rolSelected: {
        type: Object,
        required: true,

    },
})
const emit = defineEmits(['update:isDialogVisible', 'deleteRole'])
const dialogVisibleUpdate = val => {
    emit('update:isDialogVisible', val)
}
const error_exsist = ref(null);
const warning = ref(null);
const success = ref(null);
const deleted = async () => {

    try {
        const resp = await $api('/role/' + role_selected.value.id, {
            method: 'DELETE',
            onResponseError({ response }) {
                console.log(response);
                error_exsist.value = response._data.error;
            }
        })
        console.log(resp)

        success.value = "El rol se ha eliminado correctamente";
        emit('deleteRole',true)
        emit('update:isDialogVisible', false)

    } catch (error) {
        console.log(error);
        error_exsist.value = error;
    }


}
const role_selected = ref(null);
onMounted(() => {
    role_selected.value = props.rolSelected;
    console.log(role_selected.value);
})
</script>

<template> <!-- añadir roles-->
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2" v-if="role_selected">
                        Eliminar rol:{{ role_selected.id }}
                    </h4>
                </div>
                <p v-if="role_selected">¿Estas seguro de eliminar el rol "{{ role_selected.name }}"?</p>

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
                    Eliminar rol
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
