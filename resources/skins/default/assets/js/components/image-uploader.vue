<template>
<label class="image__uploader" :for="'image__uploader_'+_uid" @click="onClickHandler">
    <image-preview v-if="value" :image_id="value" @destroy="destroy"></image-preview>
    <div v-else class="image__uploader__states">
        <input :id="'image__uploader_'+_uid" type="file" class="image__uploader__input" accept="image/*" @change="upload">

        <div v-if="'uploading' == state" class="text-primary"><i class="fa fa-4x fa-spinner fa-spin"></i></div>
        <div v-else-if="'error' == state" class="text-warning"><i class="fa fa-4x fa-exclamation-triangle"></i></div>
        <div v-else><i class="fa fa-4x fa-file-image-o"></i></div>
    </div>
</label>
</template>

<script type="text/ecmascript-6">
import File from '@/store/models/file';

import ImagePreview from '@/components/image-preview';

export default {
    name: 'image-uploader',

    components: {
        'image-preview': ImagePreview
    },

    props: {
        value: {
            type: Number,
            default: null,
        }
    },

    data() {
        return {
            state: null,
            errors: [],
        }
    },

    methods: {
        async upload(event) {
            try {
                this.state = 'uploading';
                this.errors = [];

                const data = new FormData();
                data.append('file', this.takeFileFromInput(event, 'image.*'));

                const image = await File.$create({
                    data
                });

                this.state = 'uploaded';
                this.$emit('input', image.id);
            } catch (error) {
                !error.response && console.log(error);

                this.state = 'error';
                this.destroy();
            }
        },

        /**
         * Take first file from input field.
         *
         * @return {object} File
         */
        takeFileFromInput(event, pattern) {
            const files = event.target.files;

            if (!files.length) {
                throw new Error('Необходимо выбрать файл.');
            }

            if (!files[0].type.match(pattern)) {
                throw new Error('Выбранный вами файл в данный момент не поддерживается.');
            }

            return files[0];
        },

        destroy() {
            this.$emit('input', null);
        },

        onClickHandler(event) {
            // console.log(event.target);
        },
    }
}
</script>

<style lang="scss" scoped>
.image__uploader {
    display: block;
    margin-bottom: 1.25rem;
    min-height: 40px;

    &__input {
        display: none;
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
    }

    &__states {
        cursor: pointer;
        color: #e1e1e1;
        min-height: 159px;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 2px dashed;
    }
}
</style>
