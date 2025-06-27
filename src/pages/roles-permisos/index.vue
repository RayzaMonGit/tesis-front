<script setup>
import DeleteRoleDialog from '@/components/academico/role/DeleteRoleDialog.vue';
import EditRoleDialog from '@/components/academico/role/EditRoleDialog.vue';
import { watch } from 'vue';

//import data from '@/views/js/datatable'
const data = ref([]);
const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Role', key: 'name' },
  { title: 'Permisos', key: 'permissions_pluck' },
  { title: 'Fecha', key: 'created_at' },
  { title: 'Op', key: 'action' },
]
const searchQuery = ref(null);
const isAddRoleDialogVisible = ref(false);
const isEditRoleDialogVisible=ref(false);
const isDeleteRoleDialogVisible=ref(false);
const role_Selected=ref(null);
const role_Selected_deleted=ref(null);
const list = async () => {
  const resp = await $api('/role?search='+(searchQuery.value?searchQuery.value:''), {
    method: 'GET',
    onResponseError({ response }) {
      console.log(response);
    }
  })
  //console.log(resp);

  data.value = resp.roles;
}
const editItem = (item) => {
  isEditRoleDialogVisible.value=true;
  role_Selected.value=item;
}
const deleteItem = (item) => {
isDeleteRoleDialogVisible.value=true;
role_Selected_deleted.value=item;
}
onMounted(() => {
  list();
})
watch(isEditRoleDialogVisible,(event)=>{
  console.log(event);
  if (event==false) {
    role_Selected.value=null;
  }
  })
  watch(isDeleteRoleDialogVisible,(event)=>{
  console.log(event);
  if (event==false) {
    role_Selected_deleted.value=null;
  }
  })
  definePage({
  meta: {
    permissions: ['list_rol', "register_rol",
"edit_rol",
"delete_rol"]
  },
})
</script>

<template>
  <div>
    <h1>"roles-permisos"</h1>
    <VCard title="Roles y Permisos">

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="d-flex align-center">
          <!--  lupa  -->
          <VTextField v-model="searchQuery" placeholder="Buscar Rol" style="inline-size: 330px;" density="compact"
            class="me-3"
            @keyup.enter="list"
             />
        </div>

        <VSpacer />

        <div class="d-flex gap-x-4 align-center">

          <VBtn color="primary" prepend-icon="ri-add-line" @click="isAddRoleDialogVisible = !isAddRoleDialogVisible">
            Añadir Rol
          </VBtn>
        </div>
      </VCardText>

      <VDataTable :headers="headers" :items="data" :items-per-page="5" class="text-no-wrap">
        <template #item.id="{ item }">
          <span class="text-h6">{{ item.id }}</span>
        </template>

        <template #item.permissions_pluck="{ item }">
          <ul>
            <li v-for="(permiso, index) in item.permissions_pluck" :key="item">
              {{ permiso }}
            </li>
          </ul>
        </template>

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
      <AddRoleDialog v-model:is-dialog-visible="isAddRoleDialogVisible" @addRole="list()" />
      <EditRoleDialog v-if="role_Selected" :rolSelected="role_Selected"  v-model:is-dialog-visible="isEditRoleDialogVisible" @editRole="list()" />
      <DeleteRoleDialog v-if="role_Selected_deleted" :rolSelected="role_Selected_deleted"  v-model:is-dialog-visible="isDeleteRoleDialogVisible" @deleteRole="list()" />
      
    </VCard>



  </div>
</template>
