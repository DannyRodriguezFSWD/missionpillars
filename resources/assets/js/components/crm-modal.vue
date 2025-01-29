<template>
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container" :style="modalContainerStyle">
                    <div class="modal-header-1" :style="modalHeaderStyle">
                        <slot name="header"></slot>
                    </div>

                    <div class="modal-body" :style="modalBodyStyle">
                        <slot name="body"></slot>
                    </div>

                    <div class="modal-footer" :style="modalFooterStyle">
                        <slot name="footer"></slot>
                    </div>
                </div>
            </div>
            
        </div>
    </transition>
</template>

<script>
    export default {
        name: 'CRMModal',
        props: [
            'modalContainerStyle',
            'modalHeaderStyle',
            'modalBodyStyle',
            'modalFooterStyle'
        ],
        mounted() {
            
        }
    }
</script>