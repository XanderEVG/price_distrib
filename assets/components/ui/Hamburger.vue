<template>
    <div>
        <div class="hamburger" @click="menuOpened = true" v-if="!menuOpened">
            <v-icon>menu</v-icon>
        </div>
        <div class="hamburger" @click="menuOpened = false" v-else>
            <v-icon>clear</v-icon>
        </div>
        <transition name="slide">
            <div class="mobile-menu" v-if="menuOpened">
                <div class="mobile-menu__wrap">
                    <user-menu :mobile="mobile"></user-menu>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>

import UserMenu from './UserMenu';

export default {
    name: 'Hamburger',
    components: { UserMenu },
    props: ['mobile', 'path'],
    data() {
        return {
            menuOpened: false
        }
    }
}
</script>

<style scope>
    
</style>