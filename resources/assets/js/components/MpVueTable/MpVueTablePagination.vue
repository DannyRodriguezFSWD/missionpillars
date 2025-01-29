<template>
  <nav v-if="tablePagination.data" aria-label="Table Navigation" class="d-flex justify-content-center">
    <ul class="pagination float-none">
      <li class="page-item" :class="{'disabled': isOnFirstPage}">
        <a class="page-link d-block d-md-none fa fa-angle-double-left" @click.prevent="loadPage(1)">
        </a>
        <a class="page-link d-none d-md-block" @click.prevent="loadPage(1)">
          First
        </a>
      </li>
      <li class="page-item" :class="{'disabled': isOnFirstPage}">
        <a class="page-link d-block d-md-none fa fa-angle-left" @click.prevent="loadPage('prev')">
        </a>
        <a class="page-link d-none d-md-block" @click.prevent="loadPage('prev')">
          Previous
        </a>
      </li>
      <li class="page-item">
        <input v-model="tablePagination.current_page" type="text" class="form-control page_input" ref="page_input"
               @keyup.enter="loadPage(tablePagination.current_page)">
      </li>
      <li class="page-item disabled" @click="$refs.page_input.focus()">
        <a class="page-link pglp">of {{ tablePagination.last_page }}</a>
      </li>
      <li class="page-item" :class="{'disabled': isOnLastPage}">
        <a class="page-link d-block d-md-none fa fa-angle-right" @click.prevent="loadPage('next')">
        </a>
        <a class="page-link d-none d-md-block" @click.prevent="loadPage('next')">
          Next
        </a>
      </li>

      <li class="page-item" :class="{'disabled': isOnLastPage}">
        <a class="page-link d-block d-md-none fa fa-angle-double-right" @click.prevent="loadPage(totalPage)">
        </a>
        <a class="page-link d-none d-md-block" @click.prevent="loadPage(totalPage)">
          Last
        </a>
      </li>
    </ul>
  </nav>
</template>

<script>
import {VuetablePaginationMixin} from 'vuetable-2'

export default {
  mixins: [VuetablePaginationMixin],
  data() {
    return {
      tablePagination :{
        current_page: 1,
        last_page: 1
      }
    }
  }
}
</script>
<style scoped>
.page_input {
  text-align: center;
  padding: 0px;
  width: 55px;
  height: 35px;
  border-radius: 0;
  border-color: #c4c9d0;
  border-left: 0;
  box-shadow: inset 1px 2px #888;
}
.pglp{
  border-left: 0;
  box-shadow: inset 0 2px #888;
}
</style>