define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'convert/index' + location.search,
                    add_url: 'convert/add',
                    edit_url: '',
                    del_url: '',
                    multi_url: 'convert/multi',
                    import_url: 'convert/import',
                    table: 'convert',
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
                        {field: 'user_id', title: __('用户id')},
                        {field: 'balance', title: __('金额'), operate:'BETWEEN'},
                        {field: 'orderId', title: __('订单id'), operate: 'LIKE'},
                        {field: 'type', title: __('Type')},
                        {field: 'currency', title: __('币种'), operate: 'LIKE'},
                        {field: 'addtime', title: __('添加时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
