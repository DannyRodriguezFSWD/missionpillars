<template>
    <span class="help-block">
        <slot></slot>
    </span>
</template>
<style>
.help-block {
    color: #f86c6b;
}
</style>

<script>
    export default {
        props: ['errors'],
        mounted() {
            console.log('ERRORS MOUNTED')
        }
    }
</script>