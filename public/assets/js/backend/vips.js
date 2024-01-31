define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vips/index' + location.search,
                    add_url: 'vips/add',
                    edit_url: 'vips/edit',
                    del_url: 'vips/del',
                    multi_url: 'vips/multi',
                    import_url: 'vips/import',
                    table: 'vips',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'running_water', title: __('Running_water'), operate:'BETWEEN'},
                        {field: 'running_money', title: __('Running_money'), operate:'BETWEEN'},
                        {field: 'withdraw', title: __('Withdraw'), operate:'BETWEEN'},
                        {field: 'day_withdraw', title: __('Day_withdraw')},
                        {field: 'withdraw_limt', title: __('Withdraw_limt'), operate:'BETWEEN'},
                        {field: 'week', title: __('Week'), operate:'BETWEEN'},
                        {field: 'moon', title: __('Moon'), operate:'BETWEEN'},
                        {field: 'vip_up', title: __('Vip_up'), operate:'BETWEEN'},
                        {field: 'single_withdraw', title: __('Single_withdraw'), operate:'BETWEEN'},
                        {field: 'level', title: __('Level')},
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
