/**
 * Примесь добавялет к компоненту функционал обраборти правил для полей формы.
 */
import moment from 'moment';

export default {
    data: () => ({
        thisHeader: {},
    }),

    computed: {
        /** СуперАдмин */
        isSa() {
            return this.$store.getters.isSuperAdminUser;
        },
        /** Супреадмин или администратор реестра */
        isAdmin() {
            const currentRegistry = this.$store.getters.currentRegistry;
            return this.isSa || this.$store.getters.userRights.admin.find(r => r === currentRegistry);
        },
        /** Возможные правила */
        customRules() {
            return {
                field_required: v => !!v || 'Обязательно к заполнению',
                min_length: v => (!v || v.length >= 8) || 'Не менее 8 символов',
                field_min: (v) => {
                    const vFormatting = (this.thisHeader.field_type === 'date')
                        ? moment(v, 'DD.MM.YYYY').format('YYYY-MM-DD') : v;
                    const valueCompare = this.getValueRules(this.thisHeader.field_type, this.thisHeader.field_min);
                    return !v || (vFormatting >= valueCompare.val)
                        || `Не может быть меньше ${valueCompare.valForMessage}`;
                },
                field_max: (v) => {
                    const vFormatting = (this.thisHeader.field_type === 'date')
                        ? moment(v, 'DD.MM.YYYY').format('YYYY-MM-DD') : v;
                    const valueCompare = this.getValueRules(this.thisHeader.field_type, this.thisHeader.field_max);
                    return !v || (vFormatting <= valueCompare.val)
                        || `Не может быть больше ${valueCompare.valForMessage}`;
                },
                email: v => /.+@.+\..+/.test(v) || 'Email не валидный',
                mask: v => !v || (v.indexOf('_') < 0) || 'Значение не соответствует маске ввода',
            };
        },
    },

    methods: {
        getRules(header) {
            this.thisHeader = header;
            const availRules = this.customRules;
            const rules = [];

            for (const rule in availRules) {
                if (this.thisHeader[rule] || (rule === 'mask' && header.field_mask)) {
                    rules.push(availRules[rule]);
                }
            }
            return rules;
        },

        getValueRules(type, value) {
            let val;
            let valForMessage = value;
            // Для числовых полей приводим к числовому типу для сравнения
            if (type === 'integer') {
                val = parseInt(value, 10);
            } else if (type === 'float') {
                val = parseFloat(value);
                // Для полей типа дата приводим формат для вывода в сообщении
            } else {
                val = value;
                valForMessage = moment(value).format('DD.MM.YYYY');
            }
            return {
                val,
                valForMessage,
            };
        },
    },
};
