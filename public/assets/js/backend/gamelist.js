define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamelist/index' + location.search,
                    add_url: 'gamelist/add',
                    edit_url: '',
                    del_url: '',
                    multi_url: 'gamelist/multi',
                    import_url: 'gamelist/import',
                    table: 'gamelist',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'platType', title: __('游戏类型'), operate: 'LIKE'},
                        {field: 'gameType', title: __('游戏编号')},
                        {field: 'gameCode', title: __('子游戏编号'), operate: 'LIKE'},
                        {field: 'gameName', title: __('游戏名称'), operate: 'LIKE'},
                        {field: 'imageUrl', title: __('图片'), operate: 'LIKE', operate: 'LIKE', events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'is_hot', title: __('是否最火'),searchList: {"1": __('Yes'), "0": __('No')}, formatter: Table.api.formatter.toggle},
                        {field: 'is_show', title: __('是否显示'),searchList: {"1": __('Yes'), "0": __('No')}, formatter: Table.api.formatter.toggle},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
