<template>
  <div class="card">
    <div class="card-body">
      <h4 class="mb-0">{{ total }}</h4>
      <p>Forms</p>
      <div v-if="permissions['form-create']" class="btn-group mb-3">
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
          <i class="fa fa-list-alt"></i>
          Add New Form
          <span class="caret"></span>
        </button>
        <div class="dropdown-menu dropdown-menu-right">
          <a class="dropdown-item" v-for="template in templates" :href="`${base_url}/forms/template/${template.id}`">
            {{ template.name }}
          </a>
          <a class="dropdown-item" href="/crm/forms/create">
            Form builder
          </a>
        </div>
      </div>
      <div class="table-responsive">
        <VueTable
            ref="vuetable"
            :css="css.table"
            :api-url="apiUrl"
            :fields="fields"
            @vuetable:pagination-data="onPaginationData"
            @vuetable:loading="loading = true"
            @vuetable:load-success="loading = false"
            data-path="data"
            pagination-path=""
        >
        <span class="text-nowrap" slot="name" slot-scope="props">
          <a @click="loading = true" :href="`/crm/forms/${props.rowData.id}/edit`"
             v-if="permissions['form-update']"><h5>{{ props.rowData.name }}</h5></a>
          <h5 v-else>{{ props.rowData.name }}</h5>
        </span>
          <span class="text-nowrap" slot="date" slot-scope="props">
                                {{ props.rowData.created_at | removeTime }}
        </span>
          <span slot="accept_payments" slot-scope="props" class="text-primary"
                :class="{'text-success':props.rowData.accept_payments}">
                                {{ props.rowData.accept_payments ? 'YES' : 'NO' }}
        </span>
          <span class="text-nowrap" slot="actions" slot-scope="props">
          <a class="btn btn-primary btn-sm" :href="`/crm/forms/${props.rowData.id}`">
<i class="fa fa-search"></i> View Entries</a>
          <a @click="loading = true" v-if="permissions['form-update']" class="btn btn-primary btn-sm"
             :href="`/crm/forms/${props.rowData.id}/edit`"><i class="fa fa-edit"></i> Edit</a>
          <button class="btn btn-success btn-sm" @click="share(props.rowData)"><i
              class="fa fa-share-alt"></i> Share</button>
          <a @click="loading = true" class="btn btn-primary btn-sm" :href="`/crm/forms/${props.rowData.id}/export`"><i
              class="fa fa-folder"></i> Export</a>
          <button class="btn btn-primary btn-sm" @click="duplicateForm(props.rowData)"><i
              class="fa fa-copy"></i> Duplicate</button>
          <button v-if="permissions['form-delete']" class="btn btn-danger btn-sm" @click="deleteForm(props.rowData)"><i
              class="fa fa-trash"></i> Delete</button>
        </span>
        </VueTable>
      </div>
      <div class="vuetable-pagination text-center">
        <vuetable-pagination parent_vuetable="vuetable" ref="pagination"
                             :css="css.pagination"
                             @vuetable-pagination:change-page="onChangePage"
        ></vuetable-pagination>
        <vuetable-pagination-info ref="paginationInfo" info-class="pagination-info">
        </vuetable-pagination-info>
      </div>
    </div>
    <div class="modal fade" id="sharemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
      <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Share Form</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Form Address</label>
              <div class="input-group mb-3">
                <input class="form-control" type="text" id="form_url_input" :value="form.url" readonly/>
                <div class="input-group-append">
                  <button class="btn btn-primary mt-0"
                          @click="copy_link($event,'Form Address','Form Address Copied to Clipboard')" type="button">
                    Copy <i class="fa fa-copy"></i></button>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Link</label>
              <div class="input-group">
                <textarea readonly="" class="form-control">Go to {{ form.link }}</textarea>
                <button class="btn btn-primary mt-0"
                        @click="copy_link($event,'Anchor Link','Link Copied to Clipboard',true)" type="button">Copy <i
                    class="fa fa-copy"></i></button>
              </div>
            </div>
            <div class="form-group">
              <label>Embed</label>
              <div class="input-group">
                <textarea readonly="" class="form-control">{{ form.embed }}</textarea>
                <button class="btn btn-primary mt-0"
                        @click="copy_link($event,'Embed Link','Embed Copied to Clipboard',true)" type="button">Copy <i
                    class="fa fa-copy"></i></button>
              </div>
            </div>
            <div class="form-group">
                <label>QR-Code</label>
                <p>
                    <img :src="qr_code_link + form.url"/>
                    <button type="button" class="btn btn-primary" @click="downloadQrCode(qr_code_link + form.url)">Download <i class="fa fa-download"></i></button>
                </p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <loading v-if="loading"></loading>
  </div>
</template>
<script>
import VueTable from '../MpVueTable/MpVueTable'
import VuetablePagination from '../MpVueTable/MpVueTablePagination'
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'
import loading from '../Loading.vue'

export default {
  components: {
    VueTable,
    VuetablePagination,
    VuetablePaginationInfo,
    loading
  },
  props: [
    'base_url',
    'permissions_',
    'templates_',
    'qr_code_link'
  ],
  mounted() {
    this.permissions = JSON.parse(this.permissions_);
    this.templates = JSON.parse(this.templates_);
  },
  data() {
    return {
      loading: true,
      templates: [],
      permissions: [],
      form: {},
      forms: [],
      total: 0,
      apiUrl: '/crm/forms/paginate',
      fields: [
        {
          name: '__slot:name',
          title: 'Name'
        },
        {
          name: '__slot:accept_payments',
          title: 'Accept payments?'
        },
        {
          name: '__slot:date',
          title: 'Created At'
        },
        {
          name: '__slot:actions',
          title: 'Actions'
        }
      ],
      css: {
        table: {
          tableClass: 'table table-striped',
          ascendingIcon: 'glyphicon glyphicon-chevron-up',
          descendingIcon: 'glyphicon glyphicon-chevron-down'
        },
        pagination: {
          wrapperClass: 'pagination',
          activeClass: 'active',
          disabledClass: 'disabled',
          pageClass: 'page',
          linkClass: 'link',
          icons: {
            first: '',
            prev: '',
            next: '',
            last: '',
          },
        },
        icons: {
          first: 'glyphicon glyphicon-step-backward',
          prev: 'glyphicon glyphicon-chevron-left',
          next: 'glyphicon glyphicon-chevron-right',
          last: 'glyphicon glyphicon-step-forward',
        },
      },
    }
  },
  methods: {
    copy_link(e, title, text, is_text_area) {
      let el = e.target;
      if (!is_text_area) {
        el.parentElement.previousElementSibling.select()
        el.parentElement.previousElementSibling.setSelectionRange(0, 99999)
      } else {
        el.previousElementSibling.select()
        el.previousElementSibling.setSelectionRange(0, 99999)
      }
      document.execCommand('copy')
      Swal.fire(title, text, 'success')
    },
    share(form) {
      this.form = form
      this.form.url = `${this.base_url}/forms/${form.uuid}/public`
      this.form.link = `<a href="${form.url}">${form.name}</a>`
      this.form.embed = `<script src="${this.base_url}/forms/${form.uuid}/iframe"><\/script>`
      $('#sharemodal').modal('show');
    },
    deleteForm(form) {
      Swal.fire({
        title: 'Delete Form ' + form.name + '?',
        text: 'Are you sure?',
        type: 'question',
        showCancelButton: true,
        showLoaderOnConfirm: true,
        preConfirm: async () => {
          let result = await axios.delete('/crm/forms/' + form.id)
          this.$refs.vuetable.reload();
        }
      })
    },
    onChangePage(page) {
      this.$refs.vuetable.changePage(page)
    },
    onPaginationData(paginationData) {
      this.total = paginationData.total
      this.forms = paginationData.data
      this.$refs.pagination.setPaginationData(paginationData)
      this.$refs.paginationInfo.setPaginationData(paginationData)
    },
    downloadQrCode(qrCode) {
        downloadImage(qrCode);
    },
    duplicateForm(form) {
        Swal.fire({
            title: 'Duplicate Form ' + form.name + '?',
            type: 'question',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                let result = await axios.post('/crm/forms/' + form.id + '/duplicate')
                this.$refs.vuetable.reload();
            }
        })
    }
  },
  filters: {
    removeTime(date) {
      return date.split(' ')[0];
    }
  }
}
</script>
