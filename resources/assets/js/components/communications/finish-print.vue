<template>
    <div class="card">
        <div class="card-body">
            <h2>Print communication successful</h2>
            <!-- Last Printed at Month ##th, YYYY -->
            <h3>Message Body</h3>
        </div>
        <div id="message" class="card-body" v-html="communication.print_content" style="position: relative; max-height: 4in; overflow: scroll">
        </div>
        <div class="card-body">
            <div class="row">
                <div class="text-center col-md-4">
                    <a :href="view_pdf_route" class="btn btn-primary">View PDF</a>
                    <div style="font-style: italics; color: red; font-size: smaller">
                        Tip: For the best results when printing for windowed envelopes, download &amp; print the PDF, ensuring that scaling is turned off
                    </div>
                </div>
                <div class="text-center col-md-4">
                    <a :href="view_print_route" class="btn btn-primary">View Summary</a>
                </div>
                <div class="text-center col-md-4">
                    <a :href="configure_email_route" class="btn btn-primary">Go Back and Email</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['view_pdf_route','view_print_route','configure_email_route'],
    computed: {
        communication() { return communication }
    }
}
</script>

<style lang="css">
</style>
