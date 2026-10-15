<script setup>
import { computed, useAttrs } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: [String, Number],
  label: String,
  name: String,
  options: { type: Array, default: () => [] },
  optionLabel: { type: String, default: 'label' },
  optionValue: { type: String, default: 'value' },
  required: Boolean,
  placeholder: { type: String, default: 'Choisir ici' },
})

const emit = defineEmits(['update:modelValue', 'change'])
const attrs = useAttrs()

const normalized = computed(() =>
  (props.options || [])
    .filter((opt) => opt !== null && opt !== undefined && opt !== '')
    .map((opt) => {
      if (typeof opt === 'object') {
        const value = opt[props.optionValue] ?? opt[props.optionLabel] ?? ''
        const label = opt[props.optionLabel] ?? opt[props.optionValue] ?? ''
        return { value, label }
      }
      return { value: opt, label: opt }
    }),
)

const wrapperClass = computed(() => {
  const extra = attrs.class
  const text = Array.isArray(extra) ? extra.join(' ') : (extra || '')
  return String(text).includes('col-span') ? extra : ['sm:col-span-1', extra]
})

function onChange(event) {
  emit('update:modelValue', event.target.value)
  emit('change', event)
}
</script>

<template>
  <div :class="wrapperClass">
    <label v-if="label" :for="name" class="block text-sm font-medium text-primary-txt">
      {{ label }}
    </label>

    <div class="mt-2">
      <select
        :id="name"
        :name="name"
        :required="required"
        class="app-field block w-full"
        :value="modelValue ?? ''"
        @change="onChange"
      >
        <option :disabled="required" value="">{{ placeholder }}</option>
        <option
          v-for="opt in normalized"
          :key="`${opt.value}-${opt.label}`"
          :value="opt.value"
        >
          {{ opt.label }}
        </option>
      </select>
    </div>
  </div>
</template>
