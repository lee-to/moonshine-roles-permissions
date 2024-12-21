import {crudFormQuery} from '../Support/Forms.js'

export default () => ({
  match: [],
  query: '',
  async search(route) {
    if (this.query.length > 0) {
      let query = '&query=' + this.query

      const form = this.$el.closest('form')
      const formQuery = crudFormQuery(form.querySelectorAll('[name]'))

      fetch(route + query + (formQuery.length ? '&' + formQuery : ''))
        .then(response => {
          return response.json()
        })
        .then(data => {
          this.match = data
        })
    }
  },
  select(item) {
    this.query = ''
    this.match = []

    const pivot = this.$root.querySelector('.js-pivot-table')

    if (pivot !== null) {
      const tableName = pivot.dataset.tableName.toLowerCase()

      this.$dispatch('table_row_added:' + tableName)

      const tr = pivot.querySelector('table > tbody > tr:last-child')
      tr.querySelector('.js-pivot-title').innerHTML = item.label
      tr.dataset.rowKey = item.value
      tr.querySelector('.js-pivot-checker').checked = true

      this.$dispatch('table_reindex:' + tableName)
    }
  },
  tree(checked = {}) {
    checked.forEach(value => {
      this.$el
        .querySelectorAll('input[value="' + value + '"]')
        .forEach(input => (input.checked = true))
    })
  },
  pivot() {
    this.$root.querySelectorAll('.js-pivot-title')?.forEach(function (el) {
      el.addEventListener('click', event => {
        let tr = el.closest('tr')
        let checker = tr.querySelector('.js-pivot-checker')

        checker.checked = !checker.checked
      })
    })

    this.autoCheck()
  },
  autoCheck() {
    let fields = this.$root.querySelectorAll('.js-pivot-field')

    fields.forEach(function (el, key) {
      el.addEventListener('change', event => {
        let tr = el.closest('tr')
        let checker = tr.querySelector('.js-pivot-checker')

        checker.checked = event.target.value
      })
    })
  },
  checkAll() {
    this.$root.querySelectorAll('.js-pivot-checker')?.forEach(function (el) {
      el.checked = true
    })
  },
  uncheckAll() {
    this.$root.querySelectorAll('.js-pivot-checker')?.forEach(function (el) {
      el.checked = false
    })
  },
})
