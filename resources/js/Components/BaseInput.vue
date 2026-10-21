<script setup>
import { computed, ref, useAttrs } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: [String, Number],
  label: String,
  name: String,
  id: String,
  type: { type: String, default: 'text' },
  required: Boolean,
  disabled: Boolean,
  placeholder: String,
  max: [String, Number],
  min: [String, Number],
  maxlength: [String, Number],
  minlength: [String, Number],
  autocomplete: { type: String, default: 'on' },
  readonly: Boolean,
  error: String,
  customError: String,
  step: [String, Number],
  accept: String,
  autofocus: Boolean,
  wrapperClass: String,
  inputClass: { type: String, default: '' },
  modelModifiers: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:modelValue', 'input', 'change', 'blur'])
const attrs = useAttrs()
const field = ref(null)
const selectedFileName = ref('')

const inputId = computed(() => props.id || props.name || undefined)
const messageErreur = computed(() => props.error || props.customError || '')
const fileAccept = computed(() => props.accept || (props.type === 'file' ? 'application/pdf' : undefined))
const fileLabel = computed(() => selectedFileName.value || 'Aucun fichier sélectionné')

const extraListeners = computed(() => {
  const listeners = {}
  Object.entries(attrs).forEach(([key, value]) => {
    if (key.startsWith('on') && !['onInput', 'onChange', 'onBlur'].includes(key)) {
      listeners[key] = value
    }
  })
  return listeners
})

const rootClass = computed(() => {
  const extra = attrs.class
  if (props.wrapperClass) return [props.wrapperClass, extra]
  const text = Array.isArray(extra) ? extra.join(' ') : (extra || '')
  return String(text).includes('col-span') ? extra : ['sm:col-span-1', extra]
})

const controlClass = computed(() => [
  'app-field block w-full',
  props.inputClass,
  messageErreur.value ? 'is-invalid' : '',
  props.disabled ? 'is-disabled' : '',
])

defineExpose({ focus: () => field.value?.focus() })

function castValue(raw) {
  if (!props.modelModifiers?.number) return raw
  const parsed = Number.parseFloat(String(raw))
  return Number.isNaN(parsed) ? '' : parsed
}

function onInput(event) {
  if (props.type === 'file') return
  emit('update:modelValue', castValue(event.target.value))
  emit('input', event)
}

function onChange(event) {
  if (props.type !== 'file') {
    emit('update:modelValue', castValue(event.target.value))
  }
  emit('change', event)
  if (props.type === 'file') {
    selectedFileName.value = event.target.files?.[0]?.name || ''
  }
}

function openFilePicker() {
  if (props.disabled) return
  field.value?.click()
}
</script>

<template>
  <div :class="rootClass">
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-primary-txt">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative" :class="label ? 'mt-2' : ''">
      <div v-if="type === 'file'" class="app-file" :class="{ 'is-invalid': messageErreur, 'is-disabled': disabled }">
        <input
          :id="inputId"
          :name="name"
          type="file"
          :required="required"
          :disabled="disabled"
          :accept="fileAccept"
          ref="field"
          class="app-file-native"
          v-on="extraListeners"
          @change="onChange"
        />
        <button type="button" class="app-file-btn" :disabled="disabled" @click="openFilePicker">
          Parcourir
        </button>
        <span class="app-file-name">{{ fileLabel }}</span>
      </div>
      <input
        v-else
        :type="type"
        :name="name"
        :id="inputId"
        :readonly="readonly"
        :disabled="disabled"
        :placeholder="placeholder"
        :required="required"
        :max="max"
        :min="min"
        :step="step"
        :accept="accept"
        :maxlength="maxlength"
        :minlength="minlength"
        :autofocus="autofocus"
        :autocomplete="autocomplete === 'null' ? 'on' : autocomplete"
        :aria-invalid="messageErreur ? 'true' : undefined"
        :aria-label="label || placeholder || undefined"
        ref="field"
        :class="controlClass"
        :value="modelValue"
        v-on="extraListeners"
        @input="onInput"
        @change="onChange"
        @blur="emit('blur', $event)"
      />
      <slot v-if="type !== 'file'" name="suffix" />
      <slot v-if="type !== 'file'" name="append" />
    </div>

    <span v-if="messageErreur" class="mt-1 block text-sm text-red-500">{{ messageErreur }}</span>
  </div>
</template>
