import { useTheme } from 'vuetify'

export const USER_TYPE = {
    STUDENT: "student",
    TEACHER: "teacher"
}

export const LEARNING_STRAND_STATUS = ['active', 'inactive', 'draft']
export const QUESTION_TYPE = [
  {
    id: 'multiple_choice',
    name: 'Multiple Choice'
  },
  {
    id: 'true_false',
    name: 'True or False'
  },
  {
    id: 'short_answer',
    name: 'Short Answer'
  }
]

export const applyTheme = (themeMode) => {
  const theme = useTheme()
  let isDark = false

  if (themeMode === 'system') {
    isDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  } else {
    isDark = themeMode === 'dark'
  }

  theme.global.name.value = isDark ? 'dark' : 'light'

  if (isDark) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }

  localStorage.setItem('user-theme', themeMode)
}