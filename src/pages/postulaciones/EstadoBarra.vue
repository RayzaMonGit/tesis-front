<template>
  <div class="estado-barra" :class="color">
    <span class="font-weight-bold">{{ textoEstado }}</span>
    <VProgressLinear
  :color="color"
  :model-value="valor"
  height="8"
  striped
/>
    <div v-if="nota_preliminar !== null" class="mt-1 text-caption">
     Progreso de mi postulación
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  estado: String,
  nota_preliminar: {
    type: Number,
    default: null
  }
})

const estados = {
  pendiente: { texto: 'Pendiente', color: 'primary', valor: 20 },
  'en evaluacion': { texto: 'En evaluación', color: 'warning', valor: 50 },
  evaluado: { texto: 'Evaluado', color: 'green', valor: 80 },
  aprobado: { texto: 'Aprobado', color: 'success', valor: 100 },
  rechazado: { texto: 'Rechazado', color: 'error', valor: 100 },
}

const estadoInfo = computed(() => estados[props.estado?.toLowerCase()] || { texto: props.estado, color: 'grey', valor: 0 })
const textoEstado = computed(() => estadoInfo.value.texto)
const color = computed(() => estadoInfo.value.color)
// Si nota_preliminar está definido, úsalo como valor, si no, usa el valor por defecto del estado
const valor = computed(() => props.nota_preliminar !== null ? props.nota_preliminar : estadoInfo.value.valor)
</script>

<style scoped>
.estado-barra {
  margin-bottom: 8px;
}
</style>