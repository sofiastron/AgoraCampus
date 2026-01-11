<template>
  <div class="settings">
    <h2>⚙️ Paramètres</h2>

    <div class="section">
      <h3>Apparence</h3>

      <div class="theme-switch">
        <button
          :class="{ active: theme === 'light' }"
          @click="setTheme('light')"
        >
          ☀️ Clair
        </button>

        <button
          :class="{ active: theme === 'dark' }"
          @click="setTheme('dark')"
        >
          🌙 Sombre
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Parametres',
  data() {
    return {
      theme: 'light'
    }
  },
  mounted() {
    const savedTheme = localStorage.getItem('theme') || 'light'
    this.theme = savedTheme
    this.applyTheme(savedTheme)
  },
  methods: {
    setTheme(theme) {
      this.theme = theme
      localStorage.setItem('theme', theme)
      this.applyTheme(theme)
    },
    applyTheme(theme) {
      if (theme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark')
      } else {
        document.documentElement.removeAttribute('data-theme')
      }
    }
  }
}
</script>

<style scoped>
.theme-switch {
  display: flex;
  gap: 12px;
  margin-top: 10px;
}

button {
  padding: 10px 16px;
  border-radius: 10px;
  border: 1px solid #ddd;
  cursor: pointer;
  font-weight: 500;
  background: #fff;
}

button.active {
  background: #4338ca;
  color: white;
  border-color: #4338ca;
}
</style>