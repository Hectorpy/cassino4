define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bank/index' + location.search,
                    add_url: 'bank/add',
                    edit_url: 'bank/edit',
                    del_url: 'bank/del',
                    multi_url: 'bank/multi',
                    import_url: 'bank/import',
                    table: 'bank',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'firstname', title: __('Firstname'), operate: 'LIKE'},
                        {field: 'lastname', title: __('Lastname'), operate: 'LIKE'},
                        {field: 'email', title: __('Email'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'cpf', title: __('Cpf'), operate: 'LIKE'},
                        {field: 'pix', title: __('Pix'), operate: 'LIKE'},
                        {field: 'pixtype', title: __('Pixtype'), operate: 'LIKE'},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'LIKE'},
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
