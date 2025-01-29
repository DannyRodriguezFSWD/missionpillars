<template>
    <label class="c-switch-sm mb-0 c-switch-label  c-switch-primary">
        <input :name="name" :id="name" type="checkbox" class="c-switch-input" v-bind:checked="value" v-on:input="$emit('input', $event.target.checked)">
        <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

    </label>
</template>

<script>
export default {
    props: ['name','value'],
}
</script>

<style lang="css">
</style>
