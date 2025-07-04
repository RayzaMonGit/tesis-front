<script setup>
import navItems from '@/navigation/vertical'
import { useConfigStore } from '@core/stores/config'
import { themeConfig } from '@themeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import NavBarI18n from '@core/components/I18n.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'
import { onMounted } from 'vue'

// SECTION: Loading Indicator
const isFallbackStateActive = ref(false)
const refLoadingIndicator = ref(null)

watch([
  isFallbackStateActive,
  refLoadingIndicator,
], () => {
  if (isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.fallbackHandle()
  if (!isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.resolveHandle()
}, { immediate: true })

// !SECTION
const configStore = useConfigStore()

// ℹ️ Provide animation name for vertical nav collapse icon.
const verticalNavHeaderActionAnimationName = ref(null)

watch([
  () => configStore.isVerticalNavCollapsed,
  () => configStore.isAppRTL,
], val => {
  if (configStore.isAppRTL)
    verticalNavHeaderActionAnimationName.value = val[0] ? 'rotate-back-180' : 'rotate-180'
  else
    verticalNavHeaderActionAnimationName.value = val[0] ? 'rotate-180' : 'rotate-back-180'
}, { immediate: true })

const navItemsV = ref([]);
onMounted(() => {
  const USER = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null;
  
  if (USER) {
    console.log(USER);
    const permissions = USER.permissions;
    console.log("Permisos del usuario:", permissions);

    navItemsV.value = []; // Limpiamos

    navItems.forEach(nav => {
      if (USER.role.name === 'Super-Admin') {
        // Super Admin ve todo
        navItemsV.value.push(nav);
        return;
      }

      const navClone = { ...nav };

      // -- HEADINGS
      if (nav.heading && nav.permissions?.length) {
        const hasPermission = nav.permissions.some(p => permissions.includes(p));
        if (hasPermission) {
          navItemsV.value.push(navClone);
        }
      }

      // -- ITEMS CON CHILDREN
      else if (nav.children?.length) {
        const filteredChildren = nav.children.filter(child => {
          if (child.permission) {
            return permissions.includes(child.permission);
          } else if (child.permissions) {
            return child.permissions.some(p => permissions.includes(p));
          }
          return false;
        });

        if (filteredChildren.length > 0) {
          navClone.children = filteredChildren;
          navItemsV.value.push(navClone);
        }
      }

      // -- ITEMS NORMALES
      else {
        if (nav.permission) {
          if (permissions.includes(nav.permission) || nav.permission === 'all') {
            navItemsV.value.push(navClone);
          }
        } else if (nav.permissions) {
          const hasPermission = nav.permissions.some(p => permissions.includes(p));
          if (hasPermission) {
            navItemsV.value.push(navClone);
          }
        }
      }
    });

    console.log("Sidebar final:", navItemsV.value);
  }
});


</script>

<template>
  <VerticalNavLayout :nav-items="navItemsV">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn id="vertical-nav-toggle-btn" class="ms-n2 d-lg-none" @click="toggleVerticalOverlayNavActive(true)">
          <VIcon icon="ri-menu-line" />
        </IconBtn>

        <NavbarThemeSwitcher />

        <VSpacer />

        <NavBarI18n v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
          :languages="themeConfig.app.i18n.langConfig" />
        <UserProfile />
      </div>
    </template>

    <AppLoadingIndicator ref="refLoadingIndicator" />

    <!-- 👉 Pages -->
    <RouterView v-slot="{ Component }">
      <Suspense :timeout="0" @fallback="isFallbackStateActive = true" @resolve="isFallbackStateActive = false">
        <Component :is="Component" />
      </Suspense>
    </RouterView>

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </VerticalNavLayout>
</template>

<style lang="scss">
@keyframes rotate-180 {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(180deg);
  }
}

@keyframes rotate-back-180 {
  from {
    transform: rotate(180deg);
  }

  to {
    transform: rotate(0deg);
  }
}

.layout-vertical-nav {
  .nav-header {
    .header-action {
      animation-duration: 0.35s;
      animation-fill-mode: forwards;
      animation-name: v-bind(verticalNavHeaderActionAnimationName);
      transform: rotate(0deg);
    }
  }
}
</style>
