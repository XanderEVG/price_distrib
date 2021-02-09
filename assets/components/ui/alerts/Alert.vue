<template>
    <div>
        <v-dialog
            v-model="alertVisible"
            max-width="440"
            :persistent="true"
        >
            <v-card>
                <v-card-title class="headline">
                    {{ alertData.title }}
                </v-card-title>

                <v-card-text>
                    {{ alertData.desc }}
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>

                    <v-btn
                        text
                        color="secondary"
                        @click="closeEmit"
                    >
                        Отмена
                    </v-btn>

                    <v-btn
                        color="primary"
                        @click="successEmit"
                    >
                        Подтверждаю
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
export default {
    name: 'Alert',
    props: {
        alertVisible: Boolean,
        alertData: Object
    },
    methods: {
        closeEmit() {
            this.$emit('alertCancel')
        },
        successEmit() {
            this.$emit('alertSuccess', this.alertData)
        }
    }
}
</script>