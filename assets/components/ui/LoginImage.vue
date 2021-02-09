<template>
    <v-col
        class="login-left-col d-flex col-12 col-md-6"
        :style="{backgroundImage: `url(https://source.unsplash.com/collection/9830444/800x600)`}"
    > 
        <div class="login-left-col__logo__wrap">
            <v-img src="/img/logo4.png" class="m_logo"></v-img>
            <div class="logo-description">
                Система учета<br>электронных ценников
            </div>
        </div>
    </v-col>
</template>

<script>
import axios from 'axios';
export default {
    name: 'LoginImage'

}
</script>