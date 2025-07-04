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


<script setup>
// Aquí puedes definir la lógica de tu componente con composition API
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import registerMultiStepsIllustration from '@images/pages/register-multi-step-illustration.png'

// para verificar email
const verificationCode = ref('')
const codeSent = ref(false)
const emailVerified = ref(false)
const sendingCode = ref(false)
const codeError = ref(null)
const showVerificationModal = ref(false)

//
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
  error_exsist.value = null;
  
  if (!form.value.name) {
    warning.value = "El campo nombre es requerido";
    return;
  }
  if (!form.value.surname) {
    warning.value = "El campo apellido es requerido";
    return;
  }
  if (!form.value.gender) {
    warning.value = "El campo genero es requerido";
    return;
  }
  if (!form.value.telefono) {
    warning.value = "El campo telefono es requerido";
    return;
  }
  if (!form.value.tipo_doc) {
    warning.value = "El campo tipo de documento es requerido";
    return;
  }
  if (!form.value.n_doc) {
    warning.value = "El campo numero de documento es requerido";
    return;
  }
  if (!form.value.grado_academico) {
    warning.value = "El campo grado academico es requerido";
    return;
  }
  if (!form.value.experiencia_años) {
    warning.value = "El campo años de experiencia es requerido";
    return;
  }
  if (!FILE_AVATAR.value) {
    warning.value = "Se debe seleccionar un AVATAR para el Usuario";
    return;
  }

  let formData = new FormData();
  formData.append('user_id', form.value.user_id);
  formData.append('name', form.value.name);
  formData.append('surname', form.value.surname);
 
  formData.append('telefono', form.value.telefono);
  formData.append('gender', form.value.gender);
  formData.append('tipo_doc', form.value.tipo_doc);
  formData.append('n_doc', form.value.n_doc);
  formData.append('grado_academico', form.value.grado_academico);
  formData.append('experiencia_años', form.value.experiencia_años);
  formData.append('avatar', FILE_AVATAR.value); 

  try {
  const resp = await $api('/registerpos', {
    method: 'POST',
    body: formData,
    onResponseError({ response }) {
      console.log(response)
      error_exsist.value = response._data?.error || 'Error en el registro'
    },
  });
if (resp.message === 403) {
  warning.value = resp.message_text;
  return;
}

if (!resp.access_token) {
  error_exsist.value = 'No se recibió token de autenticación';
  return;
}

success.value = 'El usuario se ha creado correctamente';

localStorage.setItem('token', resp.access_token);

if (resp.user) {
  localStorage.setItem('user', JSON.stringify(resp.user));
}
//const user = resp.user;
const user = JSON.parse(localStorage.getItem('user'))
const token = localStorage.getItem('token')
console.log(user.role.name); // Ejemplo: "Postulante"
console.log(user.permissions); // Array con los permisos





  setTimeout(() => {
    success.value = null;
    warning.value = null;
    error_exsist.value = null;
    router.push('/dashboard');
  }, 1500);

} catch (error) {
  console.error(error);
  error_exsist.value = 'Error al registrar usuario';
}

};

const limitPhoneDigits = (e) => {
  if (form.value.telefono && form.value.telefono.length > 8) {
    form.value.telefono = form.value.telefono.slice(0, 8)
  }
}

// función para enviar el codigo
const sendVerificationCode = async () => {
  // Validaciones previas
  if (!form.value.email) {
    warning.value = "El campo email es requerido";
    return;
  }
  if (!form.value.password) {
    warning.value = "El campo password es requerido";
    return;
  }
  if (!form.value.confirmPassword) {
    warning.value = "Debe confirmar la contraseña";
    return;
  }
  if (form.value.password !== form.value.confirmPassword) {
    warning.value = "Las contraseñas no coinciden";
    return;
  }
  if (form.value.password.length < 8) {
    warning.value = "La contraseña debe tener al menos 8 caracteres";
    return;
  }

 

  console.log("Enviando código de verificación a:", form.value.email)
  sendingCode.value = true
  codeError.value = null
  warning.value = null

  try {
    await $api('/verificacion/enviar', {
      method: 'POST',
      body: { email: form.value.email }
    })
    codeSent.value = true
    showVerificationModal.value = true
    warning.value = "Código enviado a tu correo"
  } catch (e) {
    codeError.value = "No se pudo enviar el código"
  } finally {
    sendingCode.value = false
  }
}

// función para verificar el código
const verifyCode = async () => {
  if (!verificationCode.value) {
    codeError.value = "Debes ingresar el código"
    return
  }
  try {
    const resp = await $api('/verificacion/verificar', {
      method: 'POST',
      body: { email: form.value.email, code: verificationCode.value }
    })
    if (resp.success) {
      // Aquí creas el usuario en la tabla users
      const userResp = await $api('/register-user', {
        method: 'POST',
        body: {
          email: form.value.email,
          password: form.value.password
        }
      })
      console.log("Usuario creado:", userResp)
      if (userResp.success) {
        form.value.user_id = userResp.user.id // Guarda el user_id para el siguiente paso
        emailVerified.value = true
        codeError.value = null
        warning.value = null
        showVerificationModal.value = false
        success.value = "¡Correo verificado y usuario creado correctamente!"
        setTimeout(() => {
          success.value = null
        }, 3000)
      } else {
        codeError.value = "No se pudo crear el usuario"
      }
    } else {
      codeError.value = "Código incorrecto"
    }
  } catch (e) {
    codeError.value = "Código incorrecto"
  }
}

// Función para cerrar el modal
const closeVerificationModal = () => {
  showVerificationModal.value = false
  verificationCode.value = ''
  codeError.value = null
}
</script>

<template>
  <VRow
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      md="4"
      class="d-none d-md-flex align-center"
    >
      <!-- here your illustration -->
      <VImg
        :src="registerMultiStepsIllustration"
        class="auth-illustration"
        height="560px"
      />
    </VCol>

    <VCol
      cols="12"
      md="8"
      class="auth-card-v2 d-flex align-center justify-center pa-10"
      style="background-color: rgb(var(--v-theme-surface));"
    >
      <VCard
        flat
        class="mt-12"
        style="max-inline-size: 685px;"
      >
        <VForm>
          <h4 class="text-h4 mb-1">
            Registro de Usuario
          </h4>
          <p class="text-body-1 mb-5">
            Complete todos los campos para registrarse
          </p>

          <!-- Sección de cuenta -->
          <VCard class="mb-6" elevation="2">
            <VCardTitle class="text-h6 bg-primary text-white">
              Información de la cuenta
            </VCardTitle>
            <VCardText>
              <VRow>
                <VCol cols="12">
                  <VTextField
                    v-model="form.email"
                    label="Email"
                    placeholder="juanmanuel@email.com"
                    :rules="[
                      v => !!v || 'El correo es requerido',
                      v => /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v) || 'Ingrese un correo válido'
                    ]"
                    :disabled="emailVerified"
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
                      v => v.length >= 8 || 'Debe tener al menos 8 caracteres',
                      v => /[0-9]/.test(v) || 'Debe contener al menos un número',
                      v => /[a-z]/.test(v) || 'Debe contener al menos una minúscula',
                      v => /[A-Z]/.test(v) || 'Debe contener al menos una mayúscula',
                      v => /[^A-Za-z0-9]/.test(v) || 'Debe contener al menos un carácter especial'
                    ]"
                    :disabled="emailVerified"
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
                    :disabled="emailVerified"
                  />
                </VCol>
              </VRow>

              <!-- Botón de verificación de email -->
              <div class="mt-4">
                <VBtn 
                  v-if="!emailVerified"
                  color="primary" 
                  @click="sendVerificationCode" 
                  :loading="sendingCode"
                  :disabled="!form.email"
                >
                  <VIcon start icon="ri-mail-send-line" />
                  Enviar código de verificación
                </VBtn>
                <VAlert v-else type="success" class="mt-2">
                  <VIcon start icon="ri-check-line" />
                  ¡Correo verificado correctamente!
                </VAlert>
              </div>
            </VCardText>
          </VCard>

          <!-- Sección de información personal -->
          <VCard class="mb-6" elevation="2" :disabled="!emailVerified">
            <VCardTitle class="text-h6" :class="emailVerified ? 'bg-primary text-white' : 'bg-grey-lighten-2'">
              Información personal
            </VCardTitle>
            <VCardText>
              <VRow>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.name"
                    label="Nombre:"
                    placeholder="Maria"
                    :rules="[v => !!v || 'El nombre es obligatorio']" 
                    :disabled="!emailVerified"
                    required 
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.surname"
                    label="Apellido:"
                    placeholder="Doe"
                    :rules="[v => !!v || 'El apellido es obligatorio']" 
                    :disabled="!emailVerified"
                    required
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VRadioGroup
                    v-model="form.gender"
                    :disabled="!emailVerified"
                    inline
                  >
                    <VRadio label="Femenino" value="F" />
                    <VRadio label="Masculino" value="M" />
                    <VRadio label="Otro" value="O" />
                  </VRadioGroup>
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField 
                    label="Teléfono:"
                    type="number"
                    v-model="form.telefono"
                    placeholder="Ejemplo: 77777777"
                    maxlength="8"
                    :rules="[
                      v => !!v || 'El teléfono es requerido',
                      v => /^\d{8}$/.test(v) || 'Debe tener exactamente 8 dígitos'
                    ]"
                    :disabled="!emailVerified"
                    @input="limitPhoneDigits"
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VSelect
                    :items="type_docs"
                    v-model="form.tipo_doc"
                    label="Tipo de documento:"
                    placeholder="Select Item"
                    eager
                    :rules="[v => !!v || 'El tipo de documento es obligatorio']" 
                    :disabled="!emailVerified"
                    required
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField 
                    label="Nº de documento:" 
                    v-model="form.n_doc" 
                    placeholder="Ejemplo: 19999991-X" 
                    :rules="[v => !!v || 'El número de documento es requerido']" 
                    :disabled="!emailVerified"
                    required
                  />
                </VCol>

                <VCol cols="12">
                  <VRow>
                    <VCol cols="12">
                      <VFileInput 
                        label="Seleccione una imagen de perfil" 
                        @change="loadFile($event)"
                        :disabled="!emailVerified"
                      />
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
            </VCardText>
          </VCard>

          <!-- Sección de información académica -->
          <VCard class="mb-6" elevation="2" :disabled="!emailVerified">
            <VCardTitle class="text-h6" :class="emailVerified ? 'bg-primary text-white' : 'bg-grey-lighten-2'">
              Información académica
            </VCardTitle>
            <VCardText>
              <VRow>
                <VCol cols="12" md="6">
                  <VSelect
                    :items="type_grades"
                    v-model="form.grado_academico"
                    label="Seleccione su mayor grado académico:"
                    placeholder="Seleccionar"
                    :rules="[v => !!v || 'El grado académico es requerido']" 
                    :disabled="!emailVerified"
                    required
                    eager
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.experiencia_años"
                    label="Años de experiencia:"
                    placeholder="Ejemplo: 2"
                    :rules="[v => !!v || 'Los años de experiencia son obligatorios']" 
                    :disabled="!emailVerified"
                    required
                    type="number"
                  />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>

          <!-- Alertas -->
          <VAlert type="warning" v-if="warning" class="mt-3">
            <strong>{{warning}}</strong>
          </VAlert>

          <VAlert type="error" v-if="error_exsist" class="mt-3">
            <strong>Hubo un error al guardar en el servidor</strong>
          </VAlert>

          <VAlert type="success" v-if="success" class="mt-3">
            <strong>{{success}}</strong>
          </VAlert>

          <!-- Botón de registro -->
          <div class="d-flex justify-center mt-6">
            <VBtn
              color="success"
              size="large"
              append-icon="ri-check-line"
              @click="store()"
              :disabled="!emailVerified"
            >
              Registrarme
            </VBtn>
          </div>
        </VForm>
      </VCard>
    </VCol>
  </VRow>

  <!-- Modal de verificación de código -->
  <VDialog
    v-model="showVerificationModal"
    max-width="500"
    persistent
  >
    <VCard>
      <VCardTitle class="text-h5 text-center bg-primary text-white">
        <VIcon start icon="ri-mail-check-line" />
        Verificación de correo
      </VCardTitle>
      
      <VCardText class="pa-6">
        <div class="text-center mb-4">
          <VIcon 
            icon="ri-mail-send-fill" 
            size="64" 
            color="primary"
            class="mb-2"
          />
          <p class="text-body-1">
            Hemos enviado un código de verificación a:
          </p>
          <p class="text-h6 text-primary">{{ form.email }}</p>
        </div>

        <VTextField
          v-model="verificationCode"
          label="Código de verificación"
          placeholder="Ingresa el código de 6 dígitos"
          variant="outlined"
          class="mb-4"
          :error="!!codeError"
          :error-messages="codeError"
        />

        <VAlert v-if="warning" type="info" class="mb-4">
          {{ warning }}
        </VAlert>
      </VCardText>

      <VCardActions class="pa-6 pt-0 d-flex flex-column align-center">
  <VBtn
    color="success"
    variant="flat"
    @click="verifyCode"
    :disabled="!verificationCode"
    class="mb-2"
    block
  >
    <VIcon start icon="ri-check-line" />
    Verificar código
  </VBtn>
  <div class="d-flex justify-space-between w-100">
    <VBtn
      color="error"
      variant="text"
      @click="closeVerificationModal"
      class="me-2"
      style="min-width: 120px;"
    >
      <VIcon start icon="ri-close-line" />
      Cancelar
    </VBtn>
    <VBtn
      color="primary"
      variant="text"
      @click="sendVerificationCode"
      :loading="sendingCode"
      style="min-width: 120px;"
    >
      <VIcon start icon="ri-refresh-line" />
      Reenviar código
    </VBtn>
  </div>
</VCardActions>
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

.auth-wrapper {
  min-height: 100vh;
}
</style>