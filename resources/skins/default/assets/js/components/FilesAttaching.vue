<template>
<div class="table-responsive">
    <table class="table table-sm mb-0" v-if="files.length > 0">
        <tbody>
            <tr v-for="(file, key) in files" :key="key">
                <td class="baguetteBox text-center">
                    <div class="card-file-icon" v-if="'wait' == file.state"><i class="fa fa-spinner fa-pulse text-primary"></i></div>
                    <div class="card-file-icon" v-else-if="'error' == file.state"><i class="fa fa-ban text-danger"></i></div>
                    <div v-else>
                        <a :href="file.url" class="lightbox" v-if="'image'== file.type" target="_blank">
                            <img :src="file.url" :alt="file.title" :title="file.title" class="card-file-icon" width="42" />
                        </a>
                        <a href="#mediaModal" class="media-link" data-toggle="modal" :data-src="file.url" :data-title="file.title" data-type="audio" v-else-if="'audio'== file.type">
                            <div class="card-file-icon"><i class="fa fa-music"></i></div>
                        </a>
                        <a href="#mediaModal" class="media-link" data-toggle="modal" :data-src="file.url" :data-title="file.title" data-type="video" v-else-if="'video'== file.type">
                            <div class="card-file-icon"><i class="fa fa-film"></i></div>
                        </a>
                        <div class="card-file-icon" v-else-if="'archive'== file.type"><i class="fa fa-archive"></i></div>
                        <div class="card-file-icon" v-else><i class="fa fa-file"></i></div>
                    </div>
                </td>
                <td>
                    {{ file.title }}<!--br>{{ file.url }}-->
                    <div v-html="file.message" class=""></div>
                </td>
                <td style="white-space: nowrap;">
                    <span v-if="file.id > 0">
                        <code>[[file_{{ file.id }}]]</code>
                        <code v-if="'image'== file.type">[[picture_box_{{ file.id }}]]</code>
                    </span>
                </td>
                <td style="white-space: nowrap;" class="text-right">
                    <div class="btn-group ml-auto">
                        <span v-if="file.id > 0">
                            <button type="button" class="btn btn-link text-danger" @click="deleteFile(key)"><i class="fa fa-trash"></i></button>
                        </span>
                        <span v-else>
                            <button type="button" class="btn btn-link text-primary" @click="uploadFile(key)"><i class="fa fa-upload"></i></button>
                            <button type="button" class="btn btn-link text-warning" @click="removeFile(key)"><i class="fa fa-trash"></i></button>
                        </span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <hr class="m-0" />
    <div class="card-body">
        <input type="file" ref="files" @change="handleFiles()" multiple />
    </div>
</div>
</template>

<script>
export default {
    props: {
        lang: {
            /*type: Object,
            required: false*/
        },
        file_url: {
            String,
            // required: true
        },
        attachment_id: {
            Number,
            required: true
        },
        attachment_type: {
            String,
            required: true
        },
    },

    data() {
        return {
            files: []
        }
    },

    mounted() {
        this.fetchFiles()
    },

    methods: {
        handleFiles() {
            let uploadFiles = this.$refs.files.files;

            // @need items = [item:{file,id,key,url,message,state,type}]
            Array.from(uploadFiles, (file, index) => {
                this.files.push({
                    file,
                    id: 0,
                    title: file.name,
                    key: index + this.files.length,
                    url: null,
                    message: null,
                    state: false,
                    type: 'other', // Not assign!,
                })
            });

            this.submitFiles()
        },

        submitFiles() {
            // Check how many elements in ref="files".
            if (!this.$refs.files.files.length) {
                return alert('Nothing to upload');
            }

            for (let i = 0; i < this.files.length; i++) {
                if (!this.files[i].id) this.uploadFile(i);
            }

            this.$refs.files.value = ''
        },

        removeFile(key) {
            this.files.splice(key, 1)
        },

        reindexFiles() {
            // Reindex files array.
            if (this.files.length) {
                this.files = this.files.filter((item) => {
                    return !(item.id > 0);
                })
            }

            // Check how many elements are left.
            if (!this.files.length) {
                return confirm('Upload complete. Reload this page?') ? document.location.reload(true) : true
            }
        },

        /**
         * Fetch files from server by attachment_id and attachment_type.
         *
         * @param  string  url
         * @return {Promise}
         */
        async fetchFiles() {
            try {
                const response = await axios.get(this.$props.file_url, {
                    params: {
                        attachment_id: this.$props.attachment_id,
                        attachment_type: this.$props.attachment_type,
                    }
                })

                if (!response.data.files) {
                    throw new Error(response.data.message)
                }

                this.files = response.data.files
            } catch (error) {
                console.log(error)
            }
        },

        async uploadFile(key) {
            if (!this.$props.attachment_id || !this.$props.attachment_type) {
                Notification.warning({
                    message: 'Before you can upload files, you must save the article.'
                })

                return false
            }

            let formData = new FormData();
            formData.append('file', this.files[key].file);
            formData.append('attachment_id', this.$props.attachment_id)
            formData.append('attachment_type', this.$props.attachment_type)

            this.files.splice(key, 1, Object.assign(this.files[key], {
                state: 'wait',
                message: null,
            }))

            try {
                const response = await axios({
                    method: 'post',
                    url: this.$props.file_url + '/upload',
                    data: formData,
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (!response.data.file) {
                    throw new Error(response.data.message);
                }

                this.files.splice(key, 1, Object.assign(this.files[key], {
                    id: response.data.file.id,
                    title: response.data.file.title,
                    url: response.data.file.url,
                    state: 'uploaded',
                    message: response.data.message,
                    type: response.data.file.type,
                }))
            } catch (error) {
                this.files.splice(key, 1, Object.assign(this.files[key], {
                    state: 'error',
                    message: error.message,
                }))
            }
        },

        deleteFile(key) {
            if(! confirm('Delete this file from server?')) {
                return false
            }

            this.files.splice(key, 1, Object.assign(this.files[key], {
                state: 'wait',
                message: null,
            }))

            axios
                .delete(this.$props.file_url + '/' + this.files[key].id)
                .then((response) => {
                    this.files.splice(key, 1)
                    Notification.success({
                        message: response.data.message
                    })
                })
                .catch((error) => {
                    this.files.splice(key, 1, Object.assign(this.files[key], {
                        state: 'error',
                        message: error.message,
                    }))
                })
        },
    }
}
</script>