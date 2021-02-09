<template>
    <v-text-field
        v-if="!header.login & !header.password"
        v-model="editedItem[header.name]"
        solo
        flat
        class="text-input"
        :rules="getRules(header)"
        @focus="(header.field_mask) ? checkMask($event, header.field_mask) : null"
        :readonly="header.readonly"
    ></v-text-field>

    <v-text-field
        v-else-if="header.login"
        v-model="editedItem[header.name]"
        :rules="getRules(header)"
        type="text"
        readonly
    >
        <template v-slot:append>
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn icon v-on="on">
                        <v-icon color="teal lighten-1" @click="generateLogin('fio', editedItem.first_name)">perm_data_setting</v-icon>
                    </v-btn>
                </template>
                <span>Генерировать по ФИО</span>
            </v-tooltip>
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn icon v-on="on">
                        <v-icon color="primary lighten-1" @click="generateLogin('email', editedItem.email)">email</v-icon>
                    </v-btn>
                </template>
                <span>Генерировать по E-mail</span>
            </v-tooltip>
        </template>
    </v-text-field>

    <v-text-field
        v-else-if="header.password"
        hint="Не менее 8 символов"
        class="input-group&#45;&#45;focused pa-1 ma-1"randomPassword
        :rules="(eventCreate) ? [customRules.field_required, customRules.min_length] : []"
        v-model="editedItem[header.name]"
        color="teal"
        :append-icon="show_pwd ? 'visibility' : 'visibility_off'"
        :type="show_pwd ? 'text' : 'password'"
        @click:append="() => (show_pwd = !show_pwd)"
        append-outer-icon="build"
        @click:append-outer="
            editedItem[header.name] = randomPassword(8,false);
            show_pwd = !show_pwd"
        color:append-outer="teal"
        :readonly="header.readonly"
    ></v-text-field>
</template>

<script>
    import Inputmask from "inputmask";

    export default {
        props: ['header', 'editedItem', 'eventCreate'],

        data() {
            return {
                show_pwd: false,
            }
        },

        methods: {
            // Установка маски, если необходимо
            checkMask(event, mask) {
                if(!mask) {
                    return;
                }

                let selector = event.target;
                let im = new Inputmask(mask);
                im.mask(selector);
            },

            // Генерация логина по ФИО или почте
            generateLogin(of_field, value) {
                // Если значение не задано
                if(!value) {
                    return false
                }
                // Генерация по ФИО
                if (of_field === 'fio') {
                    function str_replace(search, replace, subject) {

                        if (!(replace instanceof Array)) {
                            replace = new Array(replace);
                            if (search instanceof Array) {
                                //If search  is an array and replace  is a string,
                                // then this replacement string is used for every value of search
                                while (search.length > replace.length) {
                                    replace[replace.length] = replace[0];
                                }
                            }
                        }

                        if (!(search instanceof Array)) search = new Array(search);
                        while (search.length > replace.length) {
                            //If replace has fewer values than search ,
                            // then an empty string is used for the rest of replacement values
                            replace[replace.length] = '';
                        }

                        if (subject instanceof Array) {
                            //If subject is an array,
                            // then the search and replace is performed with every entry of subject ,
                            // and the return value is an array as well.
                            for (k in subject) {
                                subject[k] = str_replace(search, replace, subject[k]);
                            }
                            return subject;
                        }

                        for (let k = 0; k < search.length; k++) {
                            let i = subject.indexOf(search[k]);
                            while (i > -1) {
                                subject = subject.replace(search[k], replace[k]);
                                i = subject.indexOf(search[k], i);
                            }
                        }

                        return subject;

                    }

                    let rus = new Array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' '),
                        lat = new Array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' '),
                        fio = value.split(' '),
                        login = fio[0] + (fio[1] ? fio[1].charAt(0) : '') + (fio[2] ? fio[2].charAt(0) : '');
                    login = str_replace(rus, lat, login);
                    this.editedItem.login = login
                    // Генерация по Логину
                } else {
                    this.editedItem.login = value.substr(0,value.indexOf('@')) || value;
                }
            },

            //Формирование случайного пароля
            randomPassword(length, special) {
                let iteration = 0;
                let password = '';
                let randomNumber;

                if (special == undefined) {
                    let special = false;
                }

                while (iteration < length) {
                    randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
                    if (!special) {
                        if ((randomNumber >= 33) && (randomNumber <= 47)) {
                            continue;
                        }
                        if ((randomNumber >= 58) && (randomNumber <= 64)) {
                            continue;
                        }
                        if ((randomNumber >= 91) && (randomNumber <= 96)) {
                            continue;
                        }
                        if ((randomNumber >= 123) && (randomNumber <= 126)) {
                            continue;
                        }
                    }
                    iteration++;
                    password += String.fromCharCode(randomNumber);
                }
                return password;
            },

        }
    }
</script>

<style scoped>

</style>