<template>
    <div id="overlay">
        <div class="spinner">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
        </div>
    </div>
</template>
<script>
    import { mapState, mapMutations, mapActions, mapGetters } from 'vuex'
    export default {
        name: 'loading',
        mounted() {
            
        },
        data: function(){
            return {
                
            }
        },
        computed: {
            
        },
    }
</script>
<style scoped>
    #overlay{ display: block; }
</style>