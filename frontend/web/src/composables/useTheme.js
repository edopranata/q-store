import { ref } from 'vue'
import { Dark, LocalStorage } from 'quasar'

const THEME_KEY = 'app-theme'
const THEME_OPTIONS = {
  LIGHT: 'light',
  DARK: 'dark',
  AUTO: 'auto'
}

// Reactive state untuk theme
const currentTheme = ref(THEME_OPTIONS.AUTO)
const isDarkMode = ref(false)

// Function untuk detect system preference
const getSystemPreference = () => {
  if (typeof window !== 'undefined' && window.matchMedia) {
    return window.matchMedia('(prefers-color-scheme: dark)').matches
  }
  return false
}

// Function untuk apply theme
const applyTheme = (theme) => {
  let shouldBeDark = false
  
  switch (theme) {
    case THEME_OPTIONS.DARK:
      shouldBeDark = true
      break
    case THEME_OPTIONS.LIGHT:
      shouldBeDark = false
      break
    case THEME_OPTIONS.AUTO:
    default:
      shouldBeDark = getSystemPreference()
      break
  }
  
  // Set Quasar Dark mode
  Dark.set(shouldBeDark)
  isDarkMode.value = shouldBeDark
  
  // Set data-theme attribute for CSS variables
  if (typeof document !== 'undefined') {
    const themeValue = shouldBeDark ? 'dark' : 'light'
    document.documentElement.setAttribute('data-theme', themeValue)
    
    // Add CSS class to body for additional styling
    document.body.classList.toggle('theme-dark', shouldBeDark)
    document.body.classList.toggle('theme-light', !shouldBeDark)
    
    // Trigger custom event for theme change
    document.dispatchEvent(new CustomEvent('theme-changed', {
      detail: { theme: themeValue, isDark: shouldBeDark }
    }))
  }
}

// Function untuk save theme preference
const saveThemePreference = (theme) => {
  LocalStorage.set(THEME_KEY, theme)
}

// Function untuk load theme preference
const loadThemePreference = () => {
  const saved = LocalStorage.getItem(THEME_KEY)
  return saved || THEME_OPTIONS.AUTO
}

// Function untuk set theme
const setTheme = (theme) => {
  if (Object.values(THEME_OPTIONS).includes(theme)) {
    currentTheme.value = theme
    applyTheme(theme)
    saveThemePreference(theme)
  }
}

// Function untuk toggle theme (light <-> dark)
const toggleTheme = () => {
  const newTheme = currentTheme.value === THEME_OPTIONS.DARK 
    ? THEME_OPTIONS.LIGHT 
    : THEME_OPTIONS.DARK
  setTheme(newTheme)
}

// Function untuk cycle through all theme options
const cycleTheme = () => {
  const themes = Object.values(THEME_OPTIONS)
  const currentIndex = themes.indexOf(currentTheme.value)
  const nextIndex = (currentIndex + 1) % themes.length
  setTheme(themes[nextIndex])
}

// Function untuk get theme icon
const getThemeIcon = (theme = currentTheme.value) => {
  switch (theme) {
    case THEME_OPTIONS.LIGHT:
      return 'light_mode'
    case THEME_OPTIONS.DARK:
      return 'dark_mode'
    case THEME_OPTIONS.AUTO:
    default:
      return 'brightness_auto'
  }
}

// Function untuk get theme label
const getThemeLabel = (theme = currentTheme.value) => {
  switch (theme) {
    case THEME_OPTIONS.LIGHT:
      return 'Light'
    case THEME_OPTIONS.DARK:
      return 'Dark'
    case THEME_OPTIONS.AUTO:
    default:
      return 'Auto'
  }
}

// Listen untuk system preference changes
const setupSystemPreferenceListener = () => {
  if (typeof window !== 'undefined' && window.matchMedia) {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    
    const handleChange = () => {
      if (currentTheme.value === THEME_OPTIONS.AUTO) {
        applyTheme(THEME_OPTIONS.AUTO)
      }
    }
    
    // Modern browsers
    if (mediaQuery.addEventListener) {
      mediaQuery.addEventListener('change', handleChange)
    } else {
      // Fallback untuk browser lama
      mediaQuery.addListener(handleChange)
    }
    
    return () => {
      if (mediaQuery.removeEventListener) {
        mediaQuery.removeEventListener('change', handleChange)
      } else {
        mediaQuery.removeListener(handleChange)
      }
    }
  }
  return () => {}
}

// Main composable function
export function useTheme() {
  // Initialize theme saat pertama kali dipanggil
  const initializeTheme = () => {
    const savedTheme = loadThemePreference()
    currentTheme.value = savedTheme
    applyTheme(savedTheme)
    
    // Setup system preference listener
    const cleanup = setupSystemPreferenceListener()
    
    // Return cleanup function
    return cleanup
  }
  
  return {
    // State
    currentTheme,
    isDarkMode,
    
    // Constants
    THEME_OPTIONS,
    
    // Methods
    setTheme,
    toggleTheme,
    cycleTheme,
    getThemeIcon,
    getThemeLabel,
    initializeTheme,
    
    // Utilities
    getSystemPreference
  }
}

// Export untuk digunakan di main.js atau plugin
export { THEME_OPTIONS, currentTheme, isDarkMode }

// Helper functions untuk CSS Variables
export function getThemeClasses(baseClasses = '', themeSpecific = {}) {
  const theme = isDarkMode.value ? 'dark' : 'light'
  const themeClasses = themeSpecific[theme] || ''
  return `${baseClasses} ${themeClasses}`.trim()
}

// Helper function untuk get CSS variable value
export function getThemeVariable(variableName) {
  if (typeof document === 'undefined') return ''
  return getComputedStyle(document.documentElement)
    .getPropertyValue(`--theme-${variableName}`)
    .trim()
}

// Helper function untuk set CSS variable value
export function setThemeVariable(variableName, value) {
  if (typeof document === 'undefined') return
  document.documentElement.style.setProperty(`--theme-${variableName}`, value)
}

// Helper function untuk get theme-aware color
export function getThemeColor(colorName) {
  return getThemeVariable(colorName)
}

// Helper function untuk create theme-aware style object
export function createThemeStyles(styles = {}) {
  const themeStyles = {}
  
  Object.keys(styles).forEach(key => {
    const value = styles[key]
    if (typeof value === 'string' && value.startsWith('--theme-')) {
      themeStyles[key] = `var(${value})`
    } else {
      themeStyles[key] = value
    }
  })
  
  return themeStyles
}

// Helper function untuk responsive theme classes
export function getResponsiveThemeClasses(breakpoints = {}) {
  const classes = []
  
  Object.keys(breakpoints).forEach(breakpoint => {
    const value = breakpoints[breakpoint]
    if (value) {
      classes.push(`${breakpoint}:${value}`)
    }
  })
  
  return classes.join(' ')
}