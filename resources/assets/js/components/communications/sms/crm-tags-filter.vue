<template>
    <div class="tags-filter">
        
            <h5>Only include contacts that have these tags for this list</h5>
            <tree :data="treeData" >
                <span class="tree-text" slot-scope="{ node }" @click="checkThisCheckbox($event)">
                <template v-if="!node.hasChildren()">
                    <input :value="node.id" v-model="tags.include" @input="onSelectTag()" type="checkbox" name="checkbox-include"/>
                    <i class="fa fa-tag"></i> {{ node.text }}
                </template>

                <template v-else>
                    <i :class="[node.expanded() ? 'icon-folder-alt' : 'icon-folder']"></i>
                    {{ node.text }}
                </template>
                </span>
            </tree>
            <h5>Do not send message to contacts that have these tags for this list</h5>
            <tree :data="treeData" >
                <span class="tree-text" slot-scope="{ node }" @click="checkThisCheckbox($event)">
                <template v-if="!node.hasChildren()">
                    <input :value="node.id" v-model="tags.exclude" @input="onSelectTag()" type="checkbox" name="checkbox-exclude"/>
                    <i class="fa fa-tag"></i> {{ node.text }}
                </template>

                <template v-else>
                    <i :class="[node.expanded() ? 'icon-folder-alt' : 'icon-folder']"></i>
                    {{ node.text }}
                </template>
                </span>
            </tree>
        
    </div>
</template>

<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex';

    export default {
        name: 'CRMCommunicationsTagsFilter',
        data: function(){
            return {
                tags:{},
                treeOptions: {}
            };
        },
        mounted() {
            this.tags = this.data.tags;
        },
        computed: {
            ...mapState([
                'treeData',
                'data'
            ])
        },
        methods: {
            ...mapMutations([
                'STATE_TAGS',
            ]),
            checkThisCheckbox: function(event){
                $(event.target).find('input[type=checkbox]').click();
            },
            onSelectTag: function(){
                this.STATE_TAGS(this.tags);
            }
        }
    }
</script>
