<script>
import Vuetable from 'vuetable-2';

export default {
  extends: Vuetable,
  props: {
    css: {
      default() {
        return {
          tableWrapper: '',
          tableHeaderClass: 'fixed',
          tableBodyClass: 'vuetable-semantic-no-top fixed',
          tableClass: 'ui blue selectable table-hover table-striped unstackable celled table',
          loadingClass: 'loading',
          ascendingIcon: 'fa fa-caret-up float-none',
          descendingIcon: 'fa fa-caret-down float-none',
          ascendingClass: 'sorted-asc text-nowrap',
          descendingClass: 'sorted-desc text-nowrap',
          sortableIcon: 'grey sort icon',
          handleIcon: 'grey sidebar icon',
        }
      }
    },
    httpOptions: {
      default(){
        return {
          headers: window.axios.defaults.headers
        }
      }
    }
  },
}

</script>