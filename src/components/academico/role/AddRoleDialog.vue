<script setup>
const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
})

const emit = defineEmits(['update:isDialogVisible','addRole'])


const dialogVisibleUpdate = val => {
    emit('update:isDialogVisible', val)
}

const error_exsist=ref(null);

const LIST_PERMISSIONS = PERMISOS;
const role= ref(null);
const permissions=ref([]);
const AddPermission=(permiso)=>{
    let INDEX=permissions.value.findIndex((perm)=>perm==permiso);
    if(INDEX!=-1){
        permissions.value.splice(INDEX,1);
    }else{
        permissions.value.push(permiso);
    }
    console.log(permissions.value);
}
const warning=ref(null);
const success=ref(null);
const store = async () => {
    warning.value = null;
    if (!role.value) {
        warning.value = "Se debe llenar el nombre del rol";
        return;
    } if(permissions.value.length==0) {
        warning.value = "Seleccione al menos un permiso para el rol";
        return;
    }
    let data={
        name:role.value,
        permissions:permissions.value,
    }
    
    try {
        const resp = await $api('/role', {
          method: 'POST',
          body:data,
          onResponseError({ response }) {
            console.log(response);
            error_exsist.value = response._data.error;
          }
        })
        console.log(resp)
        if(resp.message==403){
            warning.value=resp.message_text;
        }else{
            success.value="El rol se ha creado correctamente";
            setTimeout(() => {
                success.value=null;
                warning.value=null;
                role.value=null;
                permissions.value=[];
                emit('update:isDialogVisible',false);
                emit('addRole',true);
            }, 1500);
        }

    } catch (error) {
        console.log(error);
        error_exsist.value =error;
    }


}
</script>

<template> <!-- añadir roles-->
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2">
                        Agregar un nuevo rol
                    </h4>
                </div>
                <VTextField label="Rol:" v-model="role" placeholder="Ejemplo: Administrador" />
                
                <VAlert type="warning" v-if="warning" class="mt-3">
                 <strong>{{warning}}</strong> 
                </VAlert>

                <VAlert type="error" v-if="error_exsist" class="mt-3">
                 <strong>hubo un error al guardar en el servidor</strong> 
                </VAlert>

                <VAlert type="success" v-if="success" class="mt-3">
                 <strong>{{success}}</strong> 
                </VAlert>


            </VCardText>
            <VCardText class="pa-5">
                <!-- boton crear rol-->
                <VBtn @click="store()">
                    Crear rol
                    <VIcon end icon="ri-checkbox-circle-line" />
                </VBtn>
                <!-- tabla de roles-->
                <VTable>
                    <thead>
                        <tr>
                            <th class="text-uppercase">
                                Módulo
                            </th>
                            <th class="text-uppercase">
                                Roles
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(item, index) in LIST_PERMISSIONS" :key="index">
                            <td>
                                {{ item.name }}
                            </td>
                            <td>
                                <ul>
                                    <li v-for="(permiso, index2) in item.permisos" :key="index2"
                                        style="list-style: none;">
                                        <VCheckbox :label="permiso.name" :value="permiso.permiso"
                                            @click="AddPermission(permiso.permiso)" />
                                    </li>
                                </ul>
                            </td>

                        </tr>
                    </tbody>
                </VTable>

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
