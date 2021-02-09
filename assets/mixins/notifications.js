/**
 * Примесь добавялет к компоненту функционал вывода сообщений.
 */
export default {
    data: () => ({
        // Пользовательское сообщение.
        notification: {
            // Текст сообщения.
            text: '',
            // Тип сообщения.
            type: '',
            // Показать/скрыть сообщение.
            show: false,
        },
    }),
    methods: {
        /**
         * Отображение сообщения.
         * @param message Сообщение.
         */
        showNotification(message) {
            this.notification.text = message.text;
            this.notification.type = message.type;
            this.notification.show = true;
        },
    },
};
