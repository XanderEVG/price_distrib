<template>
  <v-container fluid class="no-pad coffie" light>
    <v-row class="full-height">
       <LoginImage />
      <v-col class="col-12 col-md-6 login-right-col d-flex">
        <v-overlay :value="overlay">
          <v-progress-circular indeterminate size="64"></v-progress-circular>
        </v-overlay>
        <v-form class="login-form">
          <div class="login-form__title">Авторизация в системе учета электронных ценников</div>
          <div class="login-form__spacer">
            <v-text-field
              solo
              autofocus
              flat
              class="login-form__input"
              label="Имя пользователя"
              v-model="credentials.username"
              :rules="[rules.required]"
              ref="login"
            >
            </v-text-field>
            <v-text-field
              solo
              flat
              class="login-form__input"
              label="Пароль"
              v-model="credentials.password"
              :rules="[rules.required]"
              v-on:keyup.enter="login"
              :append-icon="showPassword ? 'visibility' : 'visibility_off'"
              @click:append="showPassword = !showPassword"
              :type="showPassword ? 'text' : 'password'"
            ></v-text-field>
            <v-layout
              justify-space-between
              align-center
              class="t10 flexwrap_mobile"
            >
              <a href="pass_recovery" class="text-muted hidden p-l">Забыли пароль?</a>
              <v-btn
                light
                right
                color="blackblue"
                elevation="0"
                class="login-form__btn"
                @click.prevent="login"
                >Войти</v-btn
              >
            </v-layout>
          </div>
          <div class="msg msg__error" v-show="notification.show">
            <div class="msg__label">{{ notification.text }}</div>
          </div>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { LOGIN_REQUEST } from "../store/modules/auth/action-types";
import LoginImage from "../components/ui/LoginImage"
import NotificationsMixin from "../mixins/notifications.js";
import axios from 'axios';

export default {
  components: {LoginImage},
  data: () => {
    return {
      image: null,
      overlay: false,
      credentials: {
        username: "",
        password: "",
      },
      showPassword: false,
      // Правила проверки полей формы
      rules: {
        // Обязательное поле
        required: (value) => !!value || "Поле не может быть пустым",
      },
    };
  },
  methods: { 
    /**
     * Показывает/скрывает пароль.
     */
    toggleShowPassword() {
      this.showPassword = !this.showPassword;
    },
    /**
     * Аутентификация пользователя.
     */
    login() {
      this.overlay = true;
      this.$store
        .dispatch(LOGIN_REQUEST, this.credentials)
        .then(() => {
          this.$router.push("/select_city");
        })
        .catch((error) => {
          this.overlay = false;
          this.showNotification({ text: error.message, type: "error" });
        });
    }
  },
  mounted() {
    // Устанавливаем фокус на поле ввода логина.
    this.$refs.login.focus()
  },
};
</script>

<style scoped>
</style>
