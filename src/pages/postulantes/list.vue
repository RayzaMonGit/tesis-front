<script setup>
import { watch } from 'vue';
const data = ref([]);
const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Avatar', key: 'imagen' },
  { title: 'Nombre y Apellido', key: 'full_name' },
  { title: 'Telefono', key: 'telefono' },
  { title: 'Email', key: 'email' },
  { title: 'Documento', key: 'document_full', },
  { title: 'Grado academico', key: 'grado_academico' },
  { title: 'Experiencia', key: 'experiencia_años' },
  { title: 'Op', key: 'action' },
]
const searchQuery = ref(null);
const isEditpostulDialogVisible = ref(false);
const isDeletepostulDialogVisible = ref(false);
const postulante_Selected = ref(null);
const postulante_Selected_deleted = ref(null);

const list = async () => {
  const resp = await $api('/postulantes?search=' + (searchQuery.value ?? ''), {
    method: 'GET',
    onResponseError({ response }) {
      console.log(response)
    }
  })
  console.log(resp);
  data.value = resp.data

}
const editpostulant = (editUser) => {
  let INDEX = data.value.findIndex((user) => user.id == editUser.id);
  if (INDEX != -1) {
    data.value[INDEX] = editUser;
  }
}
const deletePostulante = (User) => {
  let INDEX = data.value.findIndex((user) => user.id == User.id);
  if (INDEX != -1) {
    data.value.splice(INDEX, 1);
  }
}
const editItem = (item) => {
  isEditpostulDialogVisible.value = true;
  postulante_Selected.value = item;
}
const deleteItem = (item) => {
  isDeletepostulDialogVisible.value = true;
  postulante_Selected_deleted.value = item;
}
const onPostulantDeleted = () => {
  list(); // vuelve a cargar la lista completa desde la API
}


watch(isEditpostulDialogVisible, (event) => {
  console.log(event);
  if (event == false) {
    postulante_Selected.value = null;
  }
})
watch(isDeletepostulDialogVisible, (event) => {
  console.log(event);
  if (event == false) {
    postulante_Selected_deleted.value = null;
  }
})

onMounted(() => {
  list();
})
</script>
<template>
  <div>
    <h1>"Postulantes"</h1>
    <VCard title="Usuarios del sistema con rol de postulante">

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="d-flex align-center">
          <!--  lupa  -->
          <VTextField v-model="searchQuery" placeholder="Buscar Postulante" style="inline-size: 300px;" density="compact"
            class="me-3" @keyup.enter="list" />
        </div>

        <VSpacer />

        <!--
        <div class="d-flex gap-x-4 align-center">
          <VBtn color="primary" prepend-icon="ri-add-line" @click="isAddStaffDialogVisible = !isAddStaffDialogVisible">
            Añadir usuario
          </VBtn>
        </div>-->
      </VCardText>

      <VDataTable :headers="headers" :items="data" :items-per-page="5" class="text-no-wrap">
        <!-- id -->

        <template #item.id="{ item }">
          <span class="text-h6">{{ item.id }}</span>
        </template>
        <!-- avatar -->
        <template #item.imagen="{ item }">
          <div class="d-flex align-center">
            <VAvatar size="32" :color="item.avatar ? '' : 'primary'"
              :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
              :variant="!item.avatar ? 'tonal' : undefined">
              <VImg v-if="item.avatar" :src="item.avatar" />
              <span v-else class="text-sm">{{ avatarText(item.full_name) }}</span>

            </VAvatar>
          </div>
        </template>
        <!-- doc -->
        <template #item.document_full="{ item }">
          <div class="d-flex align-center">
            <div class="d-flex flex-column ms-3">
              <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.n_doc }}</span>
              <small>{{ item.tipo_doc }}</small>
            </div>
          </div>
        </template>
        <template #item.grado_academico="{ item }">
          <span>{{ item.grado_academico || '-' }}</span>
        </template>

        <template #item.experiencia_años="{ item }">
          <span>{{ item.experiencia_años || '0' }}</span>
        </template>

        <template #item.full_name="{ item }">
          <span>{{ item.name }} {{ item.surname }}</span>
        </template>

        <!-- botones -->
        <template #item.action="{ item }">
          <div class="d-flex gap-1">
            <!--boton editar-->
            <IconBtn size="small" @click="editItem(item)">
              <VIcon icon="ri-pencil-line" />
            </IconBtn>
            <!--boton borrar-->
            <IconBtn size="small" @click="deleteItem(item)">
              <VIcon icon="ri-delete-bin-line" />
            </IconBtn>
          </div>
        </template>
      </VDataTable>
      <!-- <AddStaffDialog v-if="roles.length>0" v-model:is-dialog-visible="isAddStaffDialogVisible" :roles="roles" @addStaff="addStaff" />
      el nombre q se pone en el vif es el mismo que 
      esta en EditStaff Dialog.vue en la parte de props-->
      <EditPostulantDialog v-if="postulante_Selected" :userSelected="postulante_Selected"
        v-model:is-dialog-visible="isEditpostulDialogVisible" @editpostulant="editpostulant" />
        <DeletePostulantDialog
  v-if="postulante_Selected_deleted"
  :userSelected="postulante_Selected_deleted"
  @deletePostulant="onPostulantDeleted"
  v-model:is-dialog-visible="isDeletepostulDialogVisible"
/>

    </VCard>



  </div>
</template>
