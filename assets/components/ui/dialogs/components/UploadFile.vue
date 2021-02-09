<template>
  <div>
    <input
      type="file"
      :ref="fileInputName"
      :accept="accept"
      :multiple="multiple"
      @change="onFileChange"
    />

    <v-layout row>
      <v-flex>
        <v-card outlined class="mainborder">
          <v-toolbar class="fileinput" color="white" @click="activateFilePrompt" v-if="!readonly">
            <v-btn depressed text tile right absolute>
              <v-icon color="green lighten-2">add</v-icon>
            </v-btn>
          </v-toolbar>
          <v-list dense>
            <v-list-item v-for="(item, index) in files" :key="item.name">
              <v-list-item-avatar>
                <v-icon>insert_drive_file</v-icon>
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title>
                  <a @click="downloadAttachment(item.name)">{{ item.name }}</a>
                </v-list-item-title>
                <v-list-item-subtitle :class="{'error--text': item.size/1024/1024 > maxFileSize }">
                  {{ getSize(item.size) }}
                </v-list-item-subtitle>
              </v-list-item-content>
              <v-list-item-action @click="deleteAttachment(item, index)" v-if="!readonly">
                <v-icon color="red">delete_forever</v-icon>
              </v-list-item-action>
            </v-list-item>
          </v-list>

          <lightbox ref="lightbox" :images="files" :directory="photoDir" :timeoutDuration="5000"></lightbox>
        </v-card>
      </v-flex>
    </v-layout>

    <v-text-field
        v-if="header.field_required"
        class="error_text_file"
        :rules="[v => !!v || 'Обязательно к заполнению']"
        :value="files.length || null"
    ></v-text-field>
  </div>
</template>

<script>
import "babel-polyfill";
import axios from "axios";
import Lightbox from "vue-my-photos";

export default {
  components: { Lightbox },

  model: {
    prop: "files",
    event: "change",
  },

  props: {
    acceptType: {
      type: String,
      default: "*",
    },
    fileInputName: {
      type: String,
      required: true,
    },
    multiple: {
      type: Boolean,
      default: true,
    },
    filesExist: {
      type: Array,
      default: () => [],
    },
    rowId: Number,
    readonly: {
      type: Boolean,
      default: false,
    },
    maxFileSize: Number,
    header: Object,
  },

  data() {
    return {
      files: [],
    };
  },
  computed: {
    photoDir() {
      return this.files.length ? this.files[0].dir : "";
    },
    entityId() {
      return parseInt(this.$route.params.eid);
    },
    accept() {
      switch(this.acceptType) {
        case 'jpg,png,tiff,jpeg,bmp,gif':
          return 'image/*';
        case 'doc,ppt,xls,xlsx,docx,pptx,odt,ods,txt,rtf,odp,pdf':
          return '.doc,.ppt,.xls,.xlsx,.docx,.pptx,.odt,.ods,.txt,.rtf,.odp,.pdf';
        case 'zip,gz,rar,7z':
          return '.zip,.gz,.rar,.7z';
        default:
          return '*';
      }
    }
  },
  mounted() {
    this.setFilesExist();
  },
  watch: {
    // Обновляем прикрепленные файлы при переходе по строкам
    rowId: function () {
      this.files = [];
      this.setFilesExist();
      // Когда прикрепляем файл и нажимаем  Отмена - файл сохраняется в поле, поэтому очищаем:
      this.$refs[this.fileInputName].value = "";
    },
  },
  methods: {
    // Открытие картинки в новом окне
    showLightbox: function (upload, imageName) {
      if (upload) {
        this.$refs.lightbox.show(imageName);
      }
    },

    // Добавление нового файла
    activateFilePrompt() {
      this.$refs[this.fileInputName].click();
    },

    onFileChange() {
      // https://stackoverflow.com/questions/3144419/how-do-i-remove-a-file-from-the-filelist
      // FileList --> Array
      let inputFiles = [];
      Array.prototype.push.apply(
        inputFiles,
        this.$refs[this.fileInputName].files
      );
      if (inputFiles.length > 0) {
        inputFiles = inputFiles.filter((inputFile) => {
          const isDuplicate =
            this.files.find((file) => file.name === inputFile.name) !==
            undefined;
          return !isDuplicate;
        });
        this.setFiles(inputFiles);
      }
    },

    async setFiles(inputFiles) {
      if (this.multiple) {
        inputFiles.forEach((inputFile) => {
          // Запоминаем preview
          //let inputFile = inputFiles[0];
          this.getPreview(inputFile)
            .then((preview) => {
              inputFile.url = preview;
              inputFile.upload = false;
              this.files.push(inputFile);
            })
            .catch((error) => {
              console.log(error);
            });
        });
      } else {
        this.files = [];
        let inputFile = inputFiles[0];
        this.getPreview(inputFile)
          .then((preview) => {
            inputFile.url = preview;
            inputFile.upload = false;
            inputFile.dir = "";
            this.files.push(inputFile);
          })
          .catch((error) => {
            console.log(error);
          });
      }
      await this.$emit("change", this.files);
    },

    // Превью файла
    getPreview(file) {
      return new Promise((resolve, reject) => {
        const fr = new FileReader();
        fr.readAsDataURL(file);
        fr.addEventListener("load", () => {
          resolve(fr.result);
        });
      });
    },

    // Удаление прикрепления
    deleteAttachment(item, index) {
      if (item.upload) {
        if (confirm("Вы действительно хотите безвозвратно удалить файл c сервера?")) {
          axios.defaults.headers.common = {
            "X-CSRF-TOKEN": document
              .getElementsByName("csrf-token")[0]
              .getAttribute("content"),
          };
          axios
            .delete("/api/entities/del_file", {
              params: {
                file: item.name,
                entity_id: this.entityId,
                field: this.fileInputName,
                row_id: this.rowId,
              },
            })
            .then((response) => {
              if (response.data.rez) {
                this.files.splice(index, 1);
                this.$emit("deleteFile", {
                  url: item.url,
                });
              } else {
                this.$emit("showNotification", response.data.msg);
              }
            })
            .catch((error) => {
              this.$emit("showNotification", error);
            });
        }
      } else {
        this.files.splice(index, 1);
      }
    },

    // Взятие размера файла вместо байт в кило или мегабайтах
    getSize(size) {
      if (size === "") {
        return "";
      } else if (size < 1024) {
        return size + " байт";
      } else if (size < 1024 * 1024) {
        return Math.round(size / 1024) + "кБ";
      } else if (size < 1024 * 1024 * 1024) {
        return Math.round(size / 1024 / 1024) + "МБ";
      } else {
        return "undefined";
      }
    },

    // Установка ранее прикрепленных файлов
    setFilesExist() {
      this.filesExist.forEach((file) => {
        this.files.push({
          name: file.name,
          url: file.url,
          dir: file.dir,
          upload: true,
          size: "",
        });
      });
    },

    // Скачивание файла
    downloadAttachment(file) {
      axios.defaults.headers.common = {
        "X-CSRF-TOKEN": document
          .getElementsByName("csrf-token")[0]
          .getAttribute("content"),
      };
      axios
        .get("/api/entities/get_file", {
          params: {
            file: file,
            entity_id: this.entityId,
            field: this.fileInputName,
            row_id: this.rowId,
            check: true,
          },
        })
        .then((response) => {
          // Если результат true, то перенаправляем, иначе показываем уведомление
          if (response.data.rez) {
            window.location =
              "/api/entities/get_file?" +
              "file=" +
              file +
              "&entity_id=" +
              this.entityId +
              "&field=" +
              this.fileInputName +
              "&row_id=" +
              this.rowId;
          } else {
            this.$emit("showNotification", response.data.msg);
          }
        });
    },
  },
};
</script>

<style scoped>
input[type="file"] {
  position: absolute;
  left: -99999px;
}

/*Название вложения*/
.attach {
  white-space: normal;
  margin-left: -10px;
  margin-right: 20px;
  font-size: 15px;
  color: rgba(0, 0, 0, 0.54);
}

/*Отступ от следующего поля*/
.v-list--dense {
  margin-bottom: 20px;
}

/*Однородная тень названия вложения*/
.v-list.v-toolbar {
  box-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
}

>>>.error_text_file {
  margin-bottom: -47px;
}

>>>.error_text_file .v-input__slot {
  display: none;
}

>>>.error_text_file .v-text-field__details {
  padding: 0 0 63px 12px;
}
</style>