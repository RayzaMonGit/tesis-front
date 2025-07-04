<!--
<script>
export default {
  name: 'RegisterPage',
  // Define directamente aquí - esto es lo importante
  meta: {
    public: true,
    unauthenticatedOnly: true,
    layout: 'blank',
  }
}
</script>
-->

<script setup>

definePage({
  meta: {
    permissions: ["register_postulant"]
  },
})
// definir la lógica de tu componente con composition API
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const currentStep = ref(0)
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const router = useRouter()
const type_docs=[
    'CI',
    'PASAPORTE',
    'CARNET DE EXTRANJERO',
]
const type_grades=[
'Técnico Universitario Medio',
'Técnico Universitario Superior',
'Licenciatura',
'Maestría',
'Doctorado',
]
const items = [
  {
    title: 'Cuenta',
    subtitle: 'Detalles de la cuenta',
  },
  {
    title: 'Personal',
    subtitle: 'Detalles personales',
  },
  {
    title: 'Acadmico',
    subtitle: 'Detalles académicos',
  }
]

const confirmPassword = ref(null)
const form=ref({
  //usuario
  name: null,
    surname: null,
    email: null,
    telefono: null,
    password: null,
    designacion: null,
    gender: null,
    role_id: null,
    tipo_doc: null,
    n_doc: null,
    //postulante
    user_id:null,
    id_convocatoria:null,
    grado_academico:null,
    fecha_nacimiento:null,
    experiencia_años:null
})

const error_exsist=ref(null);
const success=ref(null);
const warning=ref(null);

const FILE_AVATAR=ref(null);
const IMAGEN_PREVIZUALIZA=ref(null);
const loadFile= ($event)=>{
    /*console.log(IMAGEN_PREVIZUALIZA.value);
    console.log(FILE_AVATAR.value);*/
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
const store = async () => {
warning.value = null;
if(!form.value.email){
    warning.value = "El campo email es requerido";
    return;
}
if(!form.value.password){
    warning.value = "El campo password es requerido";
    return;
}
if(!form.value.name){
    warning.value = "El campo nombre es requerido";
    return;
}
if(!form.value.surname){
    warning.value = "El campo apellido es requerido";
    return;
}
if(!form.value.gender){
    warning.value = "El campo genero es requerido";
    return;
}
if(!form.value.telefono){
    warning.value = "El campo telefono es requerido";
    return;
}
if(!form.value.tipo_doc){
    warning.value = "El campo tipo de documento es requerido";
    return;
}
if(!form.value.n_doc){
    warning.value = "El campo numero de documento es requerido";
    return;
}
if(!form.value.grado_academico){
    warning.value = "El campo grado academico es requerido";
    return;
}
if(!form.value.experiencia_años){
    warning.value = "El campo años de experiencia es requerido";
    return;
}
if (!FILE_AVATAR.value) {
        warning.value = "Se debe seleccionar un AVATR para el Usuario";
        return;
    }
let formData = new FormData();
formData.append('name', form.value.name);
formData.append('surname', form.value.surname); 
formData.append('email', form.value.email);
formData.append('telefono', form.value.telefono); 
formData.append('password', form.value.password);
formData.append('gender', form.value.gender);
formData.append('tipo_doc', form.value.tipo_doc);
formData.append('n_doc', form.value.n_doc);   
formData.append('grado_academico', form.value.grado_academico);
formData.append('experiencia_años', form.value.experiencia_años);
formData.append('imagen', FILE_AVATAR.value);

try {
  const resp = await $api('/register', {
    method: 'POST',
    body: formData,
    onResponseError({ response }) {
      console.log(response)
      error_exsist.value = response._data?.error || 'Error en el registro'
    },
  })

  if (resp.message === 403) {
    warning.value = resp.message_text
  } else {
    success.value = 'El usuario se ha creado correctamente'

    setTimeout(() => {
      success.value = null
      warning.value = null
      error_exsist.value = null
      // Limpia campos si lo necesitas
      // fieldsClean()
      // Redirige al paso 2 del formulario de postulantes
      router.push('/postulantes/list')
    }, 1500)
  }
  
  // eslint-disable-next-line no-alert
  //alert('Usuario re..!!')
} catch (error) {
  console.error(error)
  error_exsist.value = 'Error al registrar usuario'
}


}
const limitPhoneDigits = (e) => {
  if (form.value.telefono && form.value.telefono.length > 8) {
    form.value.telefono = form.value.telefono.slice(0, 8)
  }
}

</script>


<template>

  <VCard
        class="pa-5 pa-sm-8 mx-auto"
  max-width="1100"
      >
        <AppStepper
          v-model:current-step="currentStep"
          :items="items"
          :direction="$vuetify.display.smAndUp ? 'horizontal' : 'vertical'"
          class="mb-6"
        />

        <VWindow
          v-model="currentStep"
          class="disable-tab-transition"
          style="max-inline-size: 685px;"
        >
          <VForm>
            <VWindowItem>
              <h4 class="text-h4 mb-1">
                Información de la cuenta
              </h4>
              <p class="text-body-1 mb-5">
                Ingrese los detalles de su cuenta
              </p>

              <VRow>
                <VCol
                  cols="12"
                  md="12"
                >
                  <VTextField
                    v-model="form.email"
                    label="Email"
                    placeholder="juanmanuel@email.com"
                    :rules="[
                            v => !!v || 'El correo es requerido',
                            v => /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v) || 'Ingrese un correo válido'
                            ]"
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.password"
                    label="Password"
                    placeholder="............"
                    :type="isPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                    @click:append-inner="isPasswordVisible = !isPasswordVisible"
                    :rules="[
                      v => !!v || 'La contraseña es obligatoria',
                      v => v.length >= 6 || 'Debe tener al menos 6 caracteres'
                    ]"
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.confirmPassword"
                    label="Confirmar contraseña"
                    placeholder="Repetir contraseña"
                    :type="isConfirmPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isConfirmPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                    @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                    :rules="[
                      v => !!v || 'La confirmación es obligatoria',
                      v => v === form.password || 'Las contraseñas no coinciden'
                    ]"
                  />
                </VCol>

              </VRow>
            </VWindowItem>

            <VWindowItem>
              <h4 class="text-h4 mb-1">
                Información personal
              </h4>
              <p class="text-body-1 mb-5">
                Ingrese los detalles de su información personal
              </p>

              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.name"
                    label="Nombre:"
                    placeholder="Maria"
                    :rules="[v => !!v || 'El nombre es obligatorio']" required 
                    
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="form.surname"
                    label="Apellido:"
                    placeholder="Doe"
                    :rules="[v => !!v || 'El apellido es obligatorio']" required 
                  />
                </VCol>


                <VCol
                  cols="12"
                  md="6"
                >
                <VRadioGroup
                        v-model="form.gender"
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

                <VCol cols="12" md="6">
                  <VTextField 
                        label="Telefono:" 
                        type="number"
                        v-model="form.telefono" 
                        placeholder="Ejemplo: 77777777"
                        maxlength="8"
                        :rules="[
                            v => !!v || 'El telefono es obligatorio',
                            v => /^\d{8}$/.test(v) || 'Debe tener exactamente 8 dígitos'
                        ]"
                        @imput ="limitPhoneDigits"
                        ></VTextField>
                </VCol>

                <VCol cols="12" md="6">
                  <VSelect
                        :items="type_docs"
                        v-model="form.tipo_doc"
                        label="Tipo de documento:"
                        placeholder="Select Item"
                        eager
                    />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                <VTextField 
                        label="Nº de documento:" 
                        v-model="form.n_doc" 
                        placeholder="Ejemplo: 19999991-X"
                        :rules="[v => !!v || 'El numero de documento es obligatorio']" required
                         />
                </VCol>
                <VCol
                  cols="12">
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

                
              </VRow>
            </VWindowItem>
            <VWindowItem>
              <h4 class="text-h4 mb-1">
                Información de la cuenta
              </h4>
              <p class="text-body-1 mb-5">
                Ingrese los detalles de su cuenta
              </p>

              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  
                  <VSelect
                    :items="type_grades"
                    v-model="form.grado_academico"
                    label="Grado académico:"
                    placeholder="Seleccionar"
                    :rules="[v => !!v || 'El grado académico es obligatorio']" required
                    eager></VSelect>
                </VCol>

              

                <VCol
                  cols="12"
                  md="6"
                >
          
                  <VTextField
                    v-model="form.experiencia_años"
                    label="Años de experiencia:"
                    placeholder="Ejemplo: 2"
                    type="number"
                    :rules="[v => !!v || 'Los años de experiencia son obligatorios']" required
                    ></VTextField>
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
            </VWindowItem>
            
          </VForm>
        </VWindow>

        <div class="d-flex justify-space-between mt-6">
          <VBtn
            color="secondary"
            variant="outlined"
            :disabled="currentStep === 0"
            @click="currentStep--"
          >
            <VIcon
              icon="ri-arrow-left-line"
              start
              class="flip-in-rtl"
            />
            Anterior
          </VBtn>

          <VBtn
            v-if="items.length - 1 === currentStep"
            color="success"
            append-icon="ri-check-line"
            @click="store()"
          >
            Registrarme
          </VBtn>

          <VBtn
            v-else
            @click="currentStep++"
          >
            Siguiente

            <VIcon
              icon="ri-arrow-right-line"
              end
              class="flip-in-rtl"
            />
          </VBtn>
        </div>
  </VCard>

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