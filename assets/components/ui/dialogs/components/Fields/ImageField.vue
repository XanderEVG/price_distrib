<template>
    <div>
        <!-- Вывод сообщений. -->
        <v-snackbar
            v-model="notification.show"
            :color="notification.type"
            top
        >
            {{ notification.text }}
        </v-snackbar>
        <v-layout>
            <v-flex xs10 sm10 md10>
                <v-text-field
                    :label="header.ru_name"
                    @click='pickFile'
                    prepend-icon="attach_file"
                    color="teal"
                    class="pa-1 ma-1"
                    :value="image_name"
                    :rules="getRules(header)"
                    @input="clearImage($event, header.name)"
                ></v-text-field>
                <input
                    type="file"
                    style="display: none"
                    ref="image"
                    accept="image/*"
                    @change="onFilePicked($event, header.name)"
                    :readonly="header.readonly"
                >
            </v-flex>
            <v-flex xs2 sm2 md2>
                <img :src="editedItem[header.name]" height="50" ref="logoPreview"/>
            </v-flex>
        </v-layout>
    </div>
</template>

<script>
    export default {
        props: ['header', 'editedItem'],

        date() {
            return {
                image_name: '',
            }
        },

        methods: {

            //Открытие окна выбора файла для загрузки картинки
            pickFile() {
                this.$refs.image[0].click()
            },

            // Преобразование изображение в строку base64
            fileToBase64String(file) {
                return new Promise((resolve, reject) => {
                    if (file) {
                        let reader = new FileReader();
                        reader.readAsArrayBuffer(file);
                        reader.onloadend = function (oFREvent) {
                            let byteArray = new Uint8Array(oFREvent.target.result);
                            let len = byteArray.byteLength;
                            let binary = '';
                            for (let i = 0; i < len; i++) {
                                binary += String.fromCharCode(byteArray[i]);
                            }
                            byteArray = window.btoa(binary);
                            let base64String = 'data:' + file.type + ';base64,' + byteArray;
                            //console.log(base64String);
                            resolve(base64String);
                        }
                    } else {
                        reject('Пустой файл');
                    }
                })
            },

            //Загрузка картинки
            onFilePicked(e, field_name) {
                const files = e.target.files;
                if (files[0] !== undefined) {
                    const fr = new FileReader();
                    fr.readAsDataURL(files[0]);
                    fr.addEventListener('load', () => {
                        this.$refs.logoPreview[0].src = fr.result;
                        this.image_name = files[0].name;
                        // Формируем фтроку из файла
                        this.fileToBase64String(files[0])
                            .then(base64String => {
                                this.editedItem[field_name] = base64String;
                            }).catch(error => {
                            reject(error);
                            this.showNotification({
                                text: error,
                                type: 'error'
                            });
                        });
                    });
                } else {
                    this.$refs.logoPreview[0].src = ''
                }
            },

            // Очищаем модель, если поле было очищено
            clearImage(event, field_name) {
                if(!event) {
                    this.editedItem[field_name] = null
                }
            },
        }
    }
</script>

<style scoped>

</style>