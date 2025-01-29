<script src="https://unpkg.com/vue-multiselect@2.1.0"></script>

<script>
import mpMultiSelect from "./multiselect.vue"

// NOTE see ../modules/tags.js and implementation in config-print.vue
export default {
    extends: mpMultiSelect,
    props: {
        /**
         * Default the vue-multiselect custom-label 
         */
        'custom-label': {
            default() { 
                return function({name, folder}) { 
                    if (folder && folder.id != 1) return `${folder.name}\\${name}`
                    else return name
                }
            }
        },
    },
}
</script>

<style lang="css">
/* Tag multiselect-specific styling... general 'Mission Pillars' styling should be in multiselect.vue */
.multiselect__option,
.multiselect__tag {
    font-size: smaller;
}
</style>
