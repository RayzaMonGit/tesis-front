<template>
  <div>
    <VRow>

      <VCol sm="6" cols="=12">

        <VCard title="Create Awesome 🙌">
          <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
            <div class="ma-auto pa-5 border-e">
              <!-- <VImg widt="137" height="176" src="http://localhost:5174/src/assets/images/eCommerce/2.png" /> -->
              <VImg width="137" height="176" src="http://localhost:5174/src/assets/images/eCommerce/2.png" />
            </div>

            <div>
              <VCardItem>
                <VCardTitle>Apple iPhone 11 Pro</VCardTitle>
              </VCardItem>

              <VCardText>
                Apple iPhone 11 Pro smartphone. Announced Sep 2019. Features 5.8″ display Apple A13 Bionic
              </VCardText>

            </div>

          </div>

        </VCard>
      </VCol>

      <VCol sm="6" cols="=12">

        <VCard class="px-4 py-4">
          <VRow>
            <VCol cols="12">

              <VTextarea label="Outlined" rows="2" placeholder="Placeholder Text" variant="outlined" />
            </VCol>

            <VCol cols="12">
              <VCol cols="12">
                <div class="text-caption">
                  Readonly
                </div>
                <VSlider readonly label="Readonly" :model-value="50" />
              </VCol>
            </VCol>

            <VCol cols="12">
              <VSelect :items="items" :menu-props="{ transition: 'scroll-y-transition' }" label="Label"
                placeholder="Select Item" />


            </VCol>
            <VCol cols="12">
              <VBtn variant="outlined" color="success">
                Success
              </VBtn>
            </VCol>

          </VRow>
        </VCard>

      </VCol>

    </VRow>


  </div>
</template>
<!--esto es para que no ncesite autorizACION PARA ENTRR-->
<script setup>
const items = ['Foo', 'Bar', 'Fizz', 'Buzz']
import { VCard, VCol, VRow } from 'vuetify/components';

definePage({
  meta: {
    not_authenticated: false,
  },
})
</script>