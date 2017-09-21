import 'babel-polyfill'
import Vue from 'vue'
import BootstrapVue from 'bootstrap-vue'
import App from './components/App.vue'

Vue.use(BootstrapVue)

const app = new Vue({
  computed: {
    rules() {
      let rules = this.orgRules.slice(0)

      if (this.filter.rank) {
        rules = rules.filter(item => this.filter.rank === item.rank)
      }

      return rules
    }
  },
  created() {
    let rankTest = new RegExp(this.rankPattern)

    this.orgRules.forEach(item => {
      Object.defineProperty(item, 'id', {
        get: function () {
          return String(this.key).replace(/\:/, '-')
        }
      })

      Object.defineProperty(item, 'rank', {
        get: function () {
          return String(Array.filter(this.tags, tag => (rankTest.test(tag)))[0]).replace(/^android\-/, '')
        }
      })
    })

    this.orgRules.sort((a, b) => {
      if (a.rank === b.rank) {
        return a.id > b.id ? 1 : -1;
      }

      return a.rank > b.rank ? 1 : -1;
    })

    this.$root.$on('scrollspy::activate', this.onActivate)
  },
  data() {
    return {
      orgRules: window.data.rules || [],
      language: window.data.language || '',
      languages: window.data.languages || {},
      rankPattern: window.data.ranktag || '^rank\\d$',
      paging: {
        total: window.data.total || 0,
        page: window.data.p || 1,
        size: window.data.ps || 500,
      },
      filter: {
        rank: ''
      }
    }
  },
  el: '#app',
  methods: {
    onActivate(target) {
      console.log('Receved Event: scrollspy::activate for target ', target);
    }
  },
  render (h) {
    return h(App)
  }
})
