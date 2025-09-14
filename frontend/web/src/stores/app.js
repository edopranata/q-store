import { defineStore } from 'pinia'
import { LocalStorage } from 'quasar'

export const useAppStore = defineStore('app', {
  state: () => ({
    // UI State
    leftDrawerOpen: false,
    rightDrawerOpen: false,
    loading: false,
    
    // App Info
    appName: 'Q-POS',
    appVersion: process.env.VUE_APP_VERSION || '1.0.0',
    
    // Breadcrumbs
    breadcrumbs: [],
    
    // Page Title
    pageTitle: '',
    
    // Notifications
    notifications: [],
    unreadNotifications: 0,
    
    // Settings
    settings: {
      language: 'en',
      currency: 'IDR',
      dateFormat: 'DD/MM/YYYY',
      timeFormat: '24h',
      timezone: 'Asia/Jakarta',
      itemsPerPage: 10,
      autoSave: true,
      soundEnabled: true
    }
  }),

  getters: {
    isLeftDrawerOpen: (state) => state.leftDrawerOpen,
    isRightDrawerOpen: (state) => state.rightDrawerOpen,
    isLoading: (state) => state.loading,
    getAppName: (state) => state.appName,
    getAppVersion: (state) => state.appVersion,
    getBreadcrumbs: (state) => state.breadcrumbs,
    getPageTitle: (state) => state.pageTitle,
    getNotifications: (state) => state.notifications,
    getUnreadNotifications: (state) => state.unreadNotifications,
    getSettings: (state) => state.settings,
    getSetting: (state) => (key) => state.settings[key]
  },

  actions: {
    /**
     * Toggle left drawer
     */
    toggleLeftDrawer() {
      this.leftDrawerOpen = !this.leftDrawerOpen
    },

    /**
     * Set left drawer state
     * @param {boolean} state - Drawer state
     */
    setLeftDrawer(state) {
      this.leftDrawerOpen = state
    },

    /**
     * Toggle right drawer
     */
    toggleRightDrawer() {
      this.rightDrawerOpen = !this.rightDrawerOpen
    },

    /**
     * Set right drawer state
     * @param {boolean} state - Drawer state
     */
    setRightDrawer(state) {
      this.rightDrawerOpen = state
    },



    /**
     * Set loading state
     * @param {boolean} state - Loading state
     */
    setLoading(state) {
      this.loading = state
    },

    /**
     * Set breadcrumbs
     * @param {Array} breadcrumbs - Breadcrumb items
     */
    setBreadcrumbs(breadcrumbs) {
      this.breadcrumbs = breadcrumbs
    },

    /**
     * Add breadcrumb
     * @param {Object} breadcrumb - Breadcrumb item
     */
    addBreadcrumb(breadcrumb) {
      this.breadcrumbs.push(breadcrumb)
    },

    /**
     * Clear breadcrumbs
     */
    clearBreadcrumbs() {
      this.breadcrumbs = []
    },

    /**
     * Set page title
     * @param {string} title - Page title
     */
    setPageTitle(title) {
      this.pageTitle = title
      document.title = `${title} - ${this.appName}`
    },

    /**
     * Add notification
     * @param {Object} notification - Notification object
     */
    addNotification(notification) {
      const newNotification = {
        id: Date.now(),
        timestamp: new Date(),
        read: false,
        ...notification
      }
      this.notifications.unshift(newNotification)
      this.unreadNotifications++
    },

    /**
     * Mark notification as read
     * @param {number} id - Notification ID
     */
    markNotificationAsRead(id) {
      const notification = this.notifications.find(n => n.id === id)
      if (notification && !notification.read) {
        notification.read = true
        this.unreadNotifications--
      }
    },

    /**
     * Mark all notifications as read
     */
    markAllNotificationsAsRead() {
      this.notifications.forEach(notification => {
        notification.read = true
      })
      this.unreadNotifications = 0
    },

    /**
     * Remove notification
     * @param {number} id - Notification ID
     */
    removeNotification(id) {
      const index = this.notifications.findIndex(n => n.id === id)
      if (index > -1) {
        const notification = this.notifications[index]
        if (!notification.read) {
          this.unreadNotifications--
        }
        this.notifications.splice(index, 1)
      }
    },

    /**
     * Clear all notifications
     */
    clearAllNotifications() {
      this.notifications = []
      this.unreadNotifications = 0
    },

    /**
     * Update setting
     * @param {string} key - Setting key
     * @param {*} value - Setting value
     */
    updateSetting(key, value) {
      this.settings[key] = value
      this.saveSettings()
    },

    /**
     * Update multiple settings
     * @param {Object} settings - Settings object
     */
    updateSettings(settings) {
      Object.assign(this.settings, settings)
      this.saveSettings()
    },

    /**
     * Save settings to LocalStorage
     */
    saveSettings() {
      LocalStorage.set('appSettings', this.settings)
    },

    /**
     * Load settings from LocalStorage
     */
    loadSettings() {
      const savedSettings = LocalStorage.getItem('appSettings')
      if (savedSettings) {
        try {
          Object.assign(this.settings, savedSettings)
        } catch (error) {
          console.error('Error loading settings:', error)
        }
      }
    },

    /**
     * Reset settings to default
     */
    resetSettings() {
      this.settings = {
        language: 'en',
        currency: 'IDR',
        dateFormat: 'DD/MM/YYYY',
        timeFormat: '24h',
        timezone: 'Asia/Jakarta',
        itemsPerPage: 10,
        autoSave: true,
        soundEnabled: true
      }
      this.saveSettings()
    },

    /**
     * Initialize app store
     */
    initialize() {
      this.loadSettings()
    }
  }
})