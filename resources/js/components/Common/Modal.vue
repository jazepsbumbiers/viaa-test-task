<template>
    <b-modal
        v-model="visible"
        :title="title"
        :ok-title="okTitle"
        :ok-variant="okVariant"
        :ok-disabled="okDisabled"
        :cancel-title="cancelTitle"
        :cancel-variant="cancelVariant"
        :cancel-disabled="cancelDisabled"
        :hide-header-close="hideHeaderClose"
        :size="size"
        :centered="centered"
        :static="!lazyLoad"
        :no-close-on-backdrop="noCloseOnBackdrop"
        :no-close-on-esc="noCloseOnEsc"
        no-enforce-focus
        v-bind="{...$attrs}"
        @cancel="$emit('cancel-pressed')"
        @close="$emit('close-pressed')"
        @ok.prevent="$emit('ok-pressed')"
    >
        <slot />

        <template
            v-for="(_, slot) of $scopedSlots"
            #[slot]="scope"
        >
            <slot
                :name="slot"
                v-bind="scope"
            />
        </template>
    </b-modal>
</template>

<script>
    export default {
        props: {
            value: {
                type: Boolean,
                default: false,
            },
            title: {
                type: String,
                default: '',
            },
            okTitle: {
                type: String,
                default() {
                    return 'Save';
                }
            },
            okVariant: {
                type: String,
                default: 'success',
            },
            okDisabled: {
                type: Boolean,
                default: false,
            },
            cancelTitle: {
                type: String,
                default() {
                    return 'Close';
                },
            },
            cancelVariant: {
                type: String,
                default: 'secondary',
            },
            cancelDisabled: {
                type: Boolean,
                default: false,
            },
            hideHeaderClose: {
                type: Boolean,
                default: false,
            },
            size: {
                type: String,
                default: 'lg',
            },
            centered: {
                type: Boolean,
                default: true,
            },
            lazyLoad: {
                type: Boolean,
                default: true,
            },
            noCloseOnBackdrop: {
                type: Boolean,
                default: true,
            },
            noCloseOnEsc: {
                type: Boolean,
                default: true,
            },
        },
        data() {
            return {
                visible: false,
            };
        },
        watch: {
            value(v) {
                this.visible = v;
            },
            visible(v) {
                this.$emit('input', v);
            },
        },
    };
</script>