<script>
import VueMultiselect from 'vue-multiselect';

// TODO extend this widget for specific purposes and add Ajax scripts to populate the mulitselect widget for easier implementation throughout codebase (e.g., tags-multiselect.vue)
export default {
    extends: VueMultiselect,
    props:{
        label: {default: 'name'},
        trackBy: {default: 'id'},
        multiple: {default: true},
        taggable: {default: true},
    },
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style lang="css">
/* MP-specific style overrides; primarily color #41b883 --> #20a8d8
    see node_modules/vue-multiselect/dist/vue-multiselect.min.css for original values
*/
.multiselect__spinner:after,.multiselect__spinner:before {
    border-color: var(--primary) transparent transparent;
}

.multiselect__placeholder{
  margin-bottom: 0 !important;
}

.multiselect__tag {
    background: var(--primary);
}

.multiselect__option--highlight {
    background: var(--primary);
}

.multiselect__option--highlight:after {
    background: var(--primary);
}
</style>
