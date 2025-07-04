<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import avatar1 from '@images/avatars/avatar-1.png'
import { useRouter } from 'vue-router'

const userProfileList = [
  { type: 'divider' },
  {
    type: 'navItem',
    icon: 'ri-user-line',
    title: 'Profile',
    href: '#',
  },
  {
    type: 'navItem',
    icon: 'ri-settings-4-line',
    title: 'Settings',
    href: '#',
  },
  {
    type: 'navItem',
    icon: 'ri-file-text-line',
    title: 'Billing Plan',
    href: '#',
    chipsProps: {
      color: 'error',
      text: '4',
      size: 'small',
    },
  },
  { type: 'divider' },
  {
    type: 'navItem',
    icon: 'ri-money-dollar-circle-line',
    title: 'Pricing',
    href: '#',
  },
  {
    type: 'navItem',
    icon: 'ri-question-line',
    title: 'FAQ',
    href: '#',
  },
]

const user = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null;

const router = useRouter()

const logout = async () => {
  localStorage.removeItem("token");
  localStorage.removeItem("user");

  await router.push("/login");
}

// Verificar si el usuario existe antes de acceder a sus propiedades
const userAvatar = user?.avatar || avatar1;
const userName = user ? `${user.name} ${user.surname || ''}` : 'Usuario';
const userRole = user?.role?.name || '';
</script>

<template>
  <VBadge
    dot
    bordered
    location="bottom right"
    offset-x="2"
    offset-y="2"
    color="success"
    class="user-profile-badge"
  >
    <VAvatar
      class="cursor-pointer"
      size="38"
    >
      <!-- Usar la variable computada en lugar de acceder directamente -->
      <VImg :src="userAvatar" />
      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="230"
        location="bottom end"
        offset="15px"
      >
        <VList>
          <VListItem class="px-4">
            <div class="d-flex gap-x-2 align-center" v-if="user">
              <VAvatar>
                <!-- Usar la variable computada aquí también -->
                <VImg :src="userAvatar" />
              </VAvatar>

              <div>
                <div class="text-body-2 font-weight-medium text-high-emphasis">
                  {{ userName }}
                </div>
                <div class="text-capitalize text-caption text-disabled">
                  {{ userRole }}
                </div>
              </div>
            </div>
            <!-- Mostrar un estado alternativo cuando no hay usuario -->
            <div class="d-flex gap-x-2 align-center" v-else>
              <VAvatar>
                <VImg :src="avatar1" />
              </VAvatar>
              <div>
                <div class="text-body-2 font-weight-medium text-high-emphasis">
                  Invitado
                </div>
                <div class="text-caption text-disabled">
                  Sin sesión
                </div>
              </div>
            </div>
          </VListItem>

          <PerfectScrollbar :options="{ wheelPropagation: false }">
            <template
              v-for="item in userProfileList"
              :key="item.title"
            >
              <VListItem
                v-if="item.type === 'navItem'"
                :href="item.href"
                class="px-4"
              >
                <template #prepend>
                  <VIcon
                    :icon="item.icon"
                    size="22"
                  />
                </template>

                <VListItemTitle>{{ item.title }}</VListItemTitle>

                <template
                  v-if="item.chipsProps"
                  #append
                >
                  <VChip
                    v-bind="item.chipsProps"
                    variant="elevated"
                  />
                </template>
              </VListItem>

              <VDivider
                v-else
                class="my-1"
              />
            </template>

            <VListItem class="px-4">
              <VBtn
                v-if="user"
                block
                color="error"
                size="small"
                append-icon="ri-logout-box-r-line"
                @click="logout()"
              >
                Logout
              </VBtn>
              <VBtn
                v-else
                block
                color="primary"
                size="small"
                append-icon="ri-login-box-r-line"
                @click="router.push('/login')"
              >
                Login
              </VBtn>
            </VListItem>
          </PerfectScrollbar>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>

<style lang="scss">
.user-profile-badge {
  &.v-badge--bordered.v-badge--dot .v-badge__badge::after {
    color: rgb(var(--v-theme-background));
  }
}
</style>