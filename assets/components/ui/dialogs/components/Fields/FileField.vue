<template>
    <upload-file
        v-model="editedItem[header.name]"
        :file-input-name="header.name"
        :files-exist="getAttachments(header.name)"
        :row-id="editedItem.id"
        :readonly="header.readonly"
        :accept-type="header.allowed_extensions"
        :max-file-size="header.max_file_size"
        :header="header"
        @deleteFile="deleteFile"
    ></upload-file>
</template>

<script>
    import UploadFile from '../UploadFile.vue';

    export default {
        components: { UploadFile },

        props: ['header', 'editedItem'],

        methods: {
            // Отображаем уже загруженные файлы
            getAttachments(field_name) {
                let files_exist = [];
                if ((typeof this.editedItem[field_name] === 'string') && (this.editedItem[field_name] !== '')) {
                    let files = this.editedItem[field_name].split(';');
                    files.forEach(file => {
                        files_exist.push(
                            {
                                name: file.substring(file.lastIndexOf('/') + 1),
                                dir: '/upload/' + file.substring(0, file.lastIndexOf('/') + 1),
                                url: file,
                            }
                        )
                    });
                }
                return files_exist
            },

            // Удаление файла
            deleteFile(data) {
                let files = this.editedItem[this.header.name].split(';');
                let index = files.indexOf(data.url);
                if (index > -1) {
                    files.splice(index, 1);
                }
                this.editedItem[this.header.name] = files.join(';');
            },
        }
    }
</script>

<style scoped>

</style>