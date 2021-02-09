/**
 * Примесь добавялет к компоненту необходимые расчеты для построения группиорванных заголовков таблиц.
 */
//import {all} from "deepmerge";

export default {
    data() {
        return {
            lvlHeaders: [[]],
            lvlIndex: 0,
            searchResult: null,
        };
    },

    methods: {
        /**
         *  Построение массива с colspan и rowspan из дерева для грида
            Массив newRawHeaders:
                [
                    {codeName: "group_1", title: "Группа 1", type: "groupField", pos: 0, sub_columns: [
                        {codeName: "column_1", title: "col1", type: "integerField", pos: 0},
                        {codeName: "column_2", title: "col2", type: "integerField", pos: 1},
                    ]},
                    {codeName: "column_3", title: "col3", type: "integerField", pos: 2}
                ]
            преобразуем в массив lvlHeaders:
                [
                    [
                        group_1 (colspan = 2),
                        column_3 (rowspan = 2)
                    ], [
                        column_1,
                        column_2
                    ]
                ]
         */
        buildLvlEntity(data) {
            data.forEach((row) => {
                if (!row.sub_columns) {
                    row.sub_columns = [];
                }
                const tmp = {
                    ru_name: row.title,
                    name: row.codeName,
                    field_type: row.type,
                    show_in_grid: (row.show_in_grid !== false),
                    colSpan: this.getColSpan(row.sub_columns, 0),
                    rowSpan: this.getRowSpan(row),
                };
                if (typeof this.lvlHeaders[this.lvlIndex] === 'undefined') {
                    this.lvlHeaders[this.lvlIndex] = [];
                }
                this.lvlHeaders[this.lvlIndex].push(tmp);

                if (row.sub_columns) {
                    ++this.lvlIndex;
                    this.buildLvlEntity(row.sub_columns);
                    --this.lvlIndex;
                }
            });
        },

        /** Построение таблицы из дерева для конструктора */
        buildLvlConstructor(data) {
            data.forEach((row) => {
                if (!row.sub_columns) {
                    row.sub_columns = [];
                }
                const tmp = {
                    codeName: row.codeName,
                    title: row.title,
                    type: row.type,
                    pos: row.pos,
                    properties: row.properties,
                    colSpan: this.getColSpan(row.sub_columns, 0),
                    rowSpan: this.getRowSpan(row),
                    lvl: this.lvlIndex,
                };
                if (typeof this.lvlHeaders[this.lvlIndex] === 'undefined') {
                    this.lvlHeaders[this.lvlIndex] = [];
                }
                this.lvlHeaders[this.lvlIndex].push(tmp);

                if (row.sub_columns) {
                    ++this.lvlIndex;
                    this.buildLvlConstructor(row.sub_columns);
                    --this.lvlIndex;
                }
            });
        },

        /** rawHeaders с учетом прав (если нет права на чтение, то скрыть */
        getRawHeadersByRights(rawHeaders) {
            const userRights = this.$store.getters.userRights.entities[this.entityId];
            if ((!userRights) || (typeof userRights.fields_rights !== 'object') || (!rawHeaders.length)) {
                return rawHeaders;
            }
            const headersByRights = _.cloneDeep(rawHeaders);
            userRights.fields_rights.forEach((fieldRight) => {
                if (!fieldRight.read) {
                    this.removeFromTree(headersByRights, fieldRight.field);
                }
            });
            return headersByRights;
        },

        /** Удаление поля из дерева группированных столбцов */
        removeFromTree(tree, codeName) {
            const header = this.searchInTree(tree, codeName);
            // Если столбец находится в группе, то удаляем его из числа колонок этой группы
            if (header.parent) {
                const parent = this.searchInTree(tree, header.parent),
                    indexHeader = parent.sub_columns.findIndex(col => col.codeName === codeName);
                parent.sub_columns.splice(indexHeader, 1);
                // Если в группе не осталось ни одного столбца, то скрываем и ее
                if (!parent.sub_columns.length) {
                    this.removeGroupOfHeaders(parent.codeName, tree);
                }
            // Если столбец находится в корне
            } else {
                const indexHeader = tree.findIndex(col => col.codeName === codeName);
                tree.splice(indexHeader, 1);
            }
        },

        /** Обработка свойства полей "Показывать в гриде" */
        getRawHeadersByVisible(headers, rawHeaders = headers) {
            for (let i = headers.length - 1; i >= 0; i -= 1) {
                const header = headers[i];
                const props = header.properties;
                if (props
                    && ((props.showGrid === false || props.showGrid === 'false')
                    || (props.archive === true || props.archive === 'true'))
                ) {
                    this.removeFromTree(rawHeaders, header.codeName);
                } else if (header.sub_columns) {
                    this.getRawHeadersByVisible(header.sub_columns, rawHeaders);
                }
            }
        },

        /** Событие удаления группы (разгруппирование) */
        removeGroupOfHeaders(codeName, headers) {
            const group = this.searchInTree(headers, codeName);
            /** Если у группы есть родитель (не на верхнем уровне) */
            if (group.parent) {
                const parent = headers.find((rh) => rh.codeName === group.parent);
                const indexGroup = parent.sub_columns.findIndex(c => c.codeName === group.codeName);
                group.sub_columns.reverse().forEach((subColumn) => {
                    subColumn.parent = parent.codeName;
                    parent.sub_columns.splice(indexGroup, 0, subColumn);
                });
                const indexFieldDelete = parent.sub_columns.findIndex((col) => col.codeName === codeName);
                parent.sub_columns.splice(indexFieldDelete, 1);
                /** Если в группе не осталось ни одного столбца, то удаляем и ее */
                if (!parent.sub_columns.length) {
                    this.removeGroupOfHeaders(parent.codeName, headers);
                }
                /** Если группа находится в корне */
            } else {
                const indexGroup = headers.findIndex(rh => rh.codeName === codeName);
                group.sub_columns.reverse().forEach((subColumn) => {
                    delete subColumn.parent;
                    headers.splice(indexGroup, 0, subColumn);
                });
                headers.splice(indexGroup + group.sub_columns.length, 1);
                /** Если в группе не осталось ни одного столбца, то удаляем и ее */
                if (!headers.length) {
                    headers = [];
                }
            }
        },

        /** Пересобирание заголовков (entity - для таблицы или конструктора) */
        rebuildedLvl(rawHeaders, entity = false) {
            const rawHeadersByRights = this.getRawHeadersByRights(rawHeaders);
            const rawHeadersByRightsAndVisible = _.cloneDeep(rawHeadersByRights);
            this.getRawHeadersByVisible(rawHeadersByRightsAndVisible);
            this.lvlHeaders = [[]];
            this.lvlIndex = 0;
            (entity) ? this.buildLvlEntity(rawHeadersByRightsAndVisible) : this.buildLvlConstructor(rawHeadersByRights);
            return this.lvlHeaders;
        },

        /** Поиск в дереве элеемнтов по кодовому имени */
        searchInTree(data, searchCodeName) {
            this.searchResult = null;
            this.findInTree(data, searchCodeName);
            return this.searchResult;
        },
        findInTree(data, searchCodeName) {
            if (!this.searchResult) {
                const result = data.find(d => d.codeName === searchCodeName);
                if (result) {
                    this.searchResult = result;
                } else {
                    for (const item of data) {
                        if (item.sub_columns) {
                            this.findInTree(item.sub_columns, searchCodeName);
                        }
                    }
                }
            }
        },

        /** Расчет colspan */
        getColSpan(data, count) {
            if (data.length) {
                data.forEach(item => {
                    if (item.sub_columns && item.sub_columns.length) {
                        count = this.getColSpan(item.sub_columns, count);
                    } else {
                        ++count;
                    }
                });
            }
            return count;
        },

        /** Расчет rowspan */
        getRowSpan(item) {
            if (item.sub_columns.length) {
                return 1;
            } else {
                return 0;
            }
        },

        /** Сортировка согласно позиции */
        /*sort(data) {
            data.sort((a, b) => {
                if (a.pos > b.pos) {
                    return 1;
                }
                if (a.pos < b.pos) {
                    return -1;
                }
                return 0;
            });
        },*/

        /** Поиск максимального значения свойства prop в дереве */
        getMaxInTree(data, prop, curMax) {
            if (!Array.isArray(data)) {
                return;
            }
            const newMax = data.reduce((max, item) => (item[prop] && (item[prop] > max)) ? item[prop] : max, curMax);
            if (newMax > curMax) {
                curMax = newMax;
            }
            for (const el of data) {
                for (const subColumn of el.sub_columns) {
                    this.getMaxInTree(subColumn, prop, curMax);
                }
            }
            return newMax;
        },

        /** Поиск максимального значения кодового имени */
        getMaxCodeName(data, prop) {
            if (!data.length) {
                return 0;
            }
            return (data.length)
                ? Math.max.apply(Math, data.map((item) =>
                    parseInt(item[prop].substring(item[prop].indexOf('_') + 1), 10)))
                : 0;
        },

        /** Поиск максимального значения свойства позиции */
        getMaxPosition(data, prop) {
            return data.reduce((max, item) => ((parseInt(item[prop], 10) > max)) ? parseInt(item[prop], 10) : max, 0);
        },

        /** Поиск минимального значения свойства позиции */
        getMinPosition(data, prop) {
            return data.reduce((min, item) => ((parseInt(item[prop], 10) < min)) ? parseInt(item[prop], 10) : min, 0);
        },

        /** Показать/скрыть столбцы в таблице */
        toggleColumnInGrid(column, headers, value) {
            column.show_in_grid = value;
            const header = headers.find(h => h.name === column.codeName);
            if (header) {
                this.$set(header, 'show_in_grid', value);
            }
            if (column.sub_columns) {
                column.sub_columns.forEach(child => this.toggleColumnInGrid(child, headers, value));
            }
        },
    },
};
