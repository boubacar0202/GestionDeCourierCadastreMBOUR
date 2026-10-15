<script setup>
import { computed, ref, useAttrs } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: [String, Number],
  label: String,
  name: String,
  type: { type: String, default: 'text' },
  required: Boolean,
  placeholder: String,
  max: String,
  min: String,
  maxlength: [String, Number],
  minlength: [String, Number],
  autocomplete: { type: String, default: 'on' },
  readonly: Boolean,
  error: String,
  step: [String, Number],
  accept: String,
  autofocus: Boolean,
})

const emit = defineEmits(['update:modelValue', 'input', 'change'])
const attrs = useAttrs()
const field = ref(null)
const selectedFileName = ref('')

const fileAccept = computed(() => props.accept || (props.type === 'file' ? 'application/pdf' : undefined))
const fileLabel = computed(() => selectedFileName.value || 'Aucun fichier sélectionné')

const extraListeners = computed(() => {
  const listeners = {}
  Object.entries(attrs).forEach(([key, value]) => {
    if (key.startsWith('on') && key !== 'onInput' && key !== 'onChange') {
      listeners[key] = value
    }
  })
  return listeners
})

defineExpose({ focus: () => field.value?.focus() })

const wrapperClass = computed(() => {
  const extra = attrs.class
  const text = Array.isArray(extra) ? extra.join(' ') : (extra || '')
  return String(text).includes('col-span') ? extra : ['sm:col-span-1', extra]
})

function onInput(event) {
  if (props.type === 'file') return
  emit('update:modelValue', event.target.value)
  emit('input', event)
}

function onChange(event) {
  if (props.type !== 'file') {
    emit('update:modelValue', event.target.value)
  }
  emit('change', event)
  if (props.type === 'file') {
    selectedFileName.value = event.target.files?.[0]?.name || ''
  }
}

function openFilePicker() {
  field.value?.click()
}
</script>

<template>
  <div :class="wrapperClass">
    <label v-if="label" :for="name" class="block text-sm font-medium text-primary-txt">
      {{ label }}
    </label>

    <div class="relative" :class="label ? 'mt-2' : ''">
      <div v-if="type === 'file'" class="app-file">
        <input
          :id="name"
          :name="name"
          type="file"
          :required="required"
          :accept="fileAccept"
          ref="field"
          class="app-file-native"
          v-on="extraListeners"
          @change="onChange"
        />
        <button type="button" class="app-file-btn" @click="openFilePicker">
          Parcourir
        </button>
        <span class="app-file-name">{{ fileLabel }}</span>
      </div>
      <input
        v-else
        :type="type"
        :name="name"
        :id="name"
        :readonly="readonly"
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
        ref="field"
        class="app-field block w-full"
        :value="modelValue"
        v-on="extraListeners"
        @input="onInput"
        @change="onChange"
      />
      <slot v-if="type !== 'file'" name="suffix" />
    </div>

    <span v-if="error" class="mt-1 block text-sm text-red-500">{{ error }}</span>
  </div>
</template>
