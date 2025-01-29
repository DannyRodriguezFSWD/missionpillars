<template>
    <div class="row">
        <div class="col-sm-12">
            <h5>Tag contacts based on actions taken below:</h5>
            Select an Action to Tag Contact Based on that Action
        </div>
        <div class="col-sm-12">
            <div class="category_wrapper" v-for="category in action_categories">
                <h3>{{category.name}}</h3>
                <div v-for="action_tag in category.action_tags" class="tag_wrapper form-group">
                    <label>
                        <input type="checkbox" name="status[]" :value="action_tag.name" v-model="action_tag.value">
                        <dfn>
                            {{ action_tag.label }}
                        </dfn>
                        {{ action_tag.description }}
                    </label>
                    <tags-multi-select :options='tag_options' v-if="action_tag.value" v-model="action_tag.selected"> </tags-multi-select>
                    <input type="hidden" :name="action_tag.name" value="action_tag.selected">
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import tagsMultiSelect from '../mp/tags-multiselect.vue'

export default {
    components: {
        tagsMultiSelect
    },
    data() {
        return {
            action_categories: [
                {
                    name: 'Email',
                    action_tags: [
                        {name: 'sent', label: 'Sent', description: 'The message has been sent.',value:false,selected:[]},
                        {name: 'error', label: 'Error', description: 'The message has not been sent because of malformed email.',value:false,selected:[]},
                        {name: 'accepted', label: 'Accepted', description: 'The message has been placed in queue.',value:false,selected:[]},
                        {name: 'rejected', label: 'Rejected', description: 'The message has been rejected by the recipient email server.',value:false,selected:[]},
                        {name: 'delivered', label: 'Delivered', description: 'The email was sent and it was accepted by the recipient email server.',value:false,selected:[]},
                        {name: 'failed', label: 'Failed', description: 'The email could not be delivered to the recipient email server.',value:false,selected:[]},
                        {name: 'opened', label: 'Opened', description: 'The email recipient opened the email.',value:false,selected:[]},
                        {name: 'clicked', label: 'Clicked', description: 'The email recipient clicked on a link in the email.',value:false,selected:[]},
                        {name: 'unsubscribed', label: 'Unsubscribed', description: 'The email recipient clicked on the unsubscribe link.',value:false,selected:[]},
                        {name: 'complained', label: 'Complained', description: 'The email recipient clicked on the spam complaint button within their email client.',value:false,selected:[]},
                    ],
                },
                {
                    name: 'Print',
                    action_tags: [
                        {name: 'printed', label: 'Printed', description: 'The message has been generated as a PDF for printing.'}
                    ]
                }
            ],
            tag_options: [],
        }
    },
    mounted() {

        self = this
        $.getJSON("/crm/ajax/tags",
        function (data) {
            self.tag_options = data
        })
    }
}
</script>

<style scoped lang="css">
h3 {
    margin-top: 15px;
}
h5 {
    font-style: italic;
}
label {
    background: none !important;
    padding-left: 0 !important;
    color: gray;
    cursor: pointer;
}
dfn {
    display: inline-block;
    width: 12ch;
    color: black;
    font-weight: bold;
}

.tag_wrapper {
    margin-left: 5ch;
}
</style>
