define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'gamerecord/index' + location.search,
                    add_url: 'gamerecord/add',
                    edit_url: '',
                    del_url: '',
                    multi_url: 'gamerecord/multi',
                    import_url: 'gamerecord/import',
                    table: 'game_record',
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
                        {field: 'bet_id', title: __('Bet_id'), operate: 'LIKE'},
                        {field: 'uid', title: __('Uid')},
                        {field: 'game_type_name', title: __('Game_type_name'), operate: 'LIKE'},
                        {field: 'bet_amount', title: __('Bet_amount'), operate:'BETWEEN'},
                        {field: 'valid_bet_amount', title: __('Valid_bet_amount'), operate:'BETWEEN'},
                        {field: 'net_amount', title: __('Net_amount'), operate:'BETWEEN'},
                        {field: 'pumping_amount', title: __('Pumping_amount'), operate:'BETWEEN'},
                        {field: 'currency', title: __('Currency'), operate: 'LIKE'},
                        {field: 'create_at', title: __('Create_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'net_at', title: __('Net_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'is_fl', title: __('Is_fl')},
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
