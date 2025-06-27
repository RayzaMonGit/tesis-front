<script setup>
import { onMounted } from 'vue';
import { VCol, VTextarea, VTextField } from 'vuetify/components';

const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
    roles:{
        type:Object,
        required:true,
    },
    userSelected:{
        type:Object,
        required:true,
    }
})

const emit = defineEmits(['update:isDialogVisible','editStaff'])

const dialogVisibleUpdate = val => {
    emit('update:isDialogVisible', val)
}

const error_exsist=ref(null);

const warning=ref(null);

const FILE_AVATAR=ref(null);
const IMAGEN_PREVIZUALIZA=ref(null);

const success=ref(null);
const from=ref({
    name: null,//
    surname: null,//
    email: null,//
    telefono: null,//
    password: null,//
    designacion: null,//
    gender: null,//
    role_id: null,
    tipo_doc: null,//
    n_doc: null,//

    //avatar: null,
});
const isPasswordVisible = ref(false);
const type_docs=[
    'CI',
    'PASAPORTE',
    'CARNET DE EXTRANJERO',
]

const update = async () => {
    warning.value = null;
    if (!from.value.name) {
        warning.value = "Se debe llenar el nombre del Usuario";
        return;
    } if (!from.value.surname) {
        warning.value = "Se debe llenar el apellido del Usuario";
        return;
    } if (!from.value.gender) {
        warning.value = "Se debe seleccionar el genero del Usuario";
        return;
    } if (!from.value.role_id) {
        warning.value = "Se debe seleccionar un rol para el Usuario";
        return;
    } if (!from.value.telefono) {
        warning.value = "Se debe llenar el telefono del Usuario";
        return;
    } if (!from.value.email) {
        warning.value = "Se debe llenar el email del Usuario";
        return;
    } 

    //si solo fuera texto utilizaria el let data={}
    //pero como tiene un archivo como el de imagen utilizo el FormData()
    let  formData = new FormData();
    formData.append('name', from.value.name);
    formData.append('surname', from.value.surname);
    formData.append('email', from.value.email);
    formData.append('telefono', from.value.telefono);
    if(from.value.password){
        formData.append('password', from.value.password);
    }
    if(from.value.designacion){
        formData.append('designacion', from.value.designacion);
    }
    formData.append('gender', from.value.gender);
    formData.append('role_id', from.value.role_id);
    if(from.value.tipo_doc){
        formData.append('tipo_doc', from.value.tipo_doc);
    }if(from.value.n_doc){
        formData.append('n_doc', from.value.n_doc);
    }
    if(from.value.avatar){
        //el nombre de "imagen" viene desde StaffController
        formData.append('imagen', FILE_AVATAR.value);
      
    }
    
    
    try {
        success.value=null;
        const resp = await $api('/staffs/'+props.userSelected.id, {
            //aqui tendria q ser put pero cuando manejamos 
            // iagenes o documentos deba ser post
          method: 'POST',
          body: formData,
          onResponseError({ response }) {
            console.log(response);
            error_exsist.value = response._data.error;
          }
        })
        console.log(resp)
        if(resp.message==403){
            warning.value=resp.message_text;
        }else{
            success.value="El usuario se ha editado correctamente";
            setTimeout(() => {
                warning.value=null;
                error_exsist.value=null;
                //emit('update:isDialogVisible',false);
            }, 1500);
            emit('editStaff',resp.user);
        }

    } catch (error) {
        console.log(error);
        error_exsist.value =error;
    }
}
const loadFile= ($event)=>{
    if($event.target.files[0].type.indexOf("image") < 0){
        FILE_AVATAR.value = null;
        IMAGEN_PREVIZUALIZA.value = null;
        warning.value = "SOLAMENTE PUEDEN SER ARCHIVOS DE TIPO IMAGEN";
      return;
    }
    warning.value = '';
    FILE_AVATAR.value = $event.target.files[0];
    let reader = new FileReader();
    reader.readAsDataURL(FILE_AVATAR.value);
    reader.onloadend = () => IMAGEN_PREVIZUALIZA.value = reader.result;
}
const roles=ref([]);
//para controlar los roles de los usuarios dependiendo de su rol
const isPostulante = computed(() => props.userSelected.role?.name === 'Postulante')

const filteredRoles = computed(() => {
  if (isPostulante.value) {
    return props.roles // muestra todos, pero el select estará deshabilitado
  } else {
    // excluye el rol Postulante
    return props.roles.filter(role => role.name !== 'Postulante')
  }
})

const limitPhoneDigits = (e) => {
  if (from.value.telefono && from.value.telefono.length > 8) {
    from.value.telefono = from.value.telefono.slice(0, 8)
  }
}

onMounted(()=>{
    //LOS MISMOS QUE EN LA BASDE DE DATOS
    roles.value=props.roles;
    from.value.name=props.userSelected.name;
    from.value.surname=props.userSelected.surname;
    from.value.email=props.userSelected.email;
    from.value.telefono=props.userSelected.telefono;
    from.value.designacion=props.userSelected.designacion;
    from.value.gender=props.userSelected.gender;
    from.value.role_id=parseInt(props.userSelected.role_id);
    from.value.tipo_doc=props.userSelected.tipo_doc;
    from.value.n_doc=props.userSelected.n_doc;
    IMAGEN_PREVIZUALIZA.value=props.userSelected.avatar;
})
</script>

<template> <!-- añadir roles-->
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2">
                        Editar el usuario: {{ props.userSelected.id }} 
                    </h4>
                </div>
                <VRow>
                    <!--Nombre y apellido-->
                    <!--Nombre y apellido-->
                    <VCol cols="6">
                        <VTextField 
                        label="Nombre:" 
                        v-model="from.name" 
                        placeholder="Ejemplo: Maria"  
                        :rules="[v => !!v || 'El nombre es obligatorio']" required />
                       
                    </VCol>
                    <VCol cols="6">
                        <VTextField 
                        label="Apellido:" 
                        v-model="from.surname" 
                        placeholder="Ejemplo: Sanchez" 
                        :rules="[v => !!v || 'El apellido es obligatorio']" required />
                    </VCol>
                     
                     <!--Genero designacion  y rol-->
                    <VCol cols="3">
                        <VRadioGroup
                        v-model="from.gender"
                        inline>

                        <VRadio
                        label="Femenino"
                        value="F"
                        />
                        <VRadio
                        label="Masculino"
                        value="M"
                        />
                        <VRadio
                        label="Otro"
                        value="O"
                        />

                     </VRadioGroup>
                    </VCol>
                    <VCol cols="4">
                        <VTextarea
                        v-model="from.designacion"
                        label="Designacion:"
                        placeholder="Texto de ejemplo"
                        />
                    </VCol>
                    <VCol cols="5">
                        <VSelect
                        :items="filteredRoles"
                        v-model="from.role_id"
                        label="Rol:"
                        item-title="name"
                        item-value="id"
                        placeholder="Seleccionar rol"
                        :disabled="isPostulante"
                        eager
                        :rules="[v => !!v || 'Por favor seleccionar un rol']" required 
                    />
                    </VCol>
                    <!--Telefono tipodoc y numdoc-->
                    <VCol cols="4">
                        <VTextField 
                            label="Teléfono:"
                            type="number"
                            v-model="from.telefono"
                            placeholder="Ejemplo: 77777777"
                            maxlength="8"
                            :rules="[
                            v => !!v || 'El teléfono es requerido',
                            v => /^\d{8}$/.test(v) || 'Debe tener exactamente 8 dígitos'
                            ]"
                            @input="limitPhoneDigits"
                        />
                        </VCol>

                    <VCol cols="4">
                        <VSelect
                        :items="type_docs"
                        v-model="from.tipo_doc"
                        label="Tipo de documento:"
                        placeholder="Select Item"
                        eager
                        :rules="[v => !!v || 'Por favor seleccionr una opción']" required 
                    />
                    </VCol>
                    <VCol cols="4">
                        <VTextField 
                        label="Nº de documento:" 
                        v-model="from.n_doc" 
                        placeholder="Ejemplo: 19999991-X" 
                        :rules="[v => !!v || 'Este campo es obligatorio']" required />
                    </VCol>
                    <!--avatar-->
                    <VCol cols="6">
                        <VRow>
                            <VCol cols="12">
                                <VFileInput 
                                label="File input" 
                                @change="loadFile($event)" />
                            </VCol>
                            <VCol cols="12" v-if="IMAGEN_PREVIZUALIZA">
                                <VImg
                                    width="137"
                                    height="176"
                                    :src="IMAGEN_PREVIZUALIZA"
                                />
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol cols="6">
                        
                    </VCol>
                     <!--Email y contraseña-->
                     <VCol cols="6">
                        <VTextField 
                            v-model="from.email"
                            label="Email"
                            placeholder="ejemplo@correo.com"
                            :rules="[
                            v => !!v || 'El correo es requerido',
                            v => /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v) || 'Ingrese un correo válido'
                            ]"
                        />
                        </VCol>

                        <VCol cols="6">
                            <VTextField
                                v-model="from.password"
                                label="Contraseña:"
                                placeholder="············"
                                :type="isPasswordVisible ? 'text' : 'password'"
                                :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                                @click:append-inner="isPasswordVisible = !isPasswordVisible"
                                :rules="[
                                v => !!v || 'La contraseña es obligatoria',
                                v => v.length >= 6 || 'Debe tener al menos 8 caracteres'
                                ]"
                            />
                            </VCol>
                </VRow>


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
                <VBtn @click="update()">
                    Editar Usuario
                    <VIcon end icon="ri-checkbox-circle-line" />
                </VBtn>
                <!-- tabla de roles-->


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
