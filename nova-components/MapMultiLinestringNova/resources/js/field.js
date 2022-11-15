Nova.booting((Vue, router, store) => {
  Vue.component('index-map-multi-linestring-nova', require('./components/IndexField'))
  Vue.component('detail-map-multi-linestring-nova', require('./components/DetailField'))
  Vue.component('form-map-multi-linestring-nova', require('./components/FormField'))
  Vue.component('map-multi-linestring-nova', require('./components/MapComponent'))
})
