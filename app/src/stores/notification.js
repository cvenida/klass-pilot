import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
  const show = ref(false)
  const message = ref('')
  const color = ref('info')
  const timeout = ref(4000)

  function notify(text, type = 'info', duration = 4000) {
    message.value = text
    color.value = type
    timeout.value = duration
    show.value = true
  }

  function success(text, duration) {
    notify(text, 'success', duration)
  }

  function error(text, duration) {
    notify(text, 'error', duration)
  }

  function warning(text, duration) {
    notify(text, 'warning', duration)
  }

  function info(text, duration) {
    notify(text, 'info', duration)
  }

  return { show, message, color, timeout, notify, success, error, warning, info }
})