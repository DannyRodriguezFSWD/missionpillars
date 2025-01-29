<template>
     <div class="card">
         <div class="card-body">
             <h2>Email communication successful</h2>
             <h3>Message Body</h3>
         </div>
         <div id="message" class="card-body" v-html="communication.content"  style="position: relative; max-height: 4in; overflow: scroll">
         </div>
         <div class="card-body">
             <div class="row">
                 <div class="text-center col-md-4">
                     <a :href="view_email_route" class="btn btn-primary">View Summary</a>
                 </div>
                 <div class="text-center col-md-6">
                     <a :href="configure_print_route" class="btn btn-primary">Go Back and Print</a>
                 </div>
             </div>
         </div>
     </div>
</template>

<script>
export default {
    props: ['view_email_route','configure_print_route'],
    computed: {
        communication() { return communication }
    }
}
</script>

<style lang="css">
</style>


@push('scripts')
@endpush

@endsection
