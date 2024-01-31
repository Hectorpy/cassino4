define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dotop/index' + location.search,
                    add_url: 'dotop/add',
                    edit_url: '',
                    del_url: '',
                    multi_url: 'dotop/multi',
                    import_url: 'dotop/import',
                    table: 'dotop',
                    multi_pop_url: 'dotops/multipop',
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
                         {field: 'info', title: __('Info'), operate: 'LIKE'},
                         {field: 'orderNo', title: __('支付单号'), operate: 'LIKE'},
                        //  {field: 'type', title: __('Type'),searchList: {"0":__('充值'),"1":__('提现')}, formatter: Table.api.formatter.normal}, 
                         {field: 'username', title: __('Username'), operate: 'LIKE'},
                         {field: 'me_user', title: __('Me_user'), operate: 'LIKE'},
                        //  {field: 'moneys', title: __('真实上下分金额'), operate:'BETWEEN'},
                         {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        //  {field: 'sxf', title: __('提现手续费'), operate:'BETWEEN'},
                         {field: 'status', title: __('Status'), searchList: {"0":__('待支付'),"1":__('已充值'),"2":__('已拒绝'),"3":__('待回调通知')}, formatter: Table.api.formatter.normal},
                         {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        //  {field: 'mobile', title: __('手机号'), operate: 'LIKE'},
                        //  {field: 'bankCard', title: __('银行卡号'), operate: 'LIKE'},
                        //  {field: 'bankName', title: __('银行卡名字 '), operate: 'LIKE'},
                    //   {field: 'dai_id', title: __('Dai_id')},
     
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {
                                    confirm: '确定通过审核吗？',
                                    name:'adopt',
                                    text:'通过',
                                    title:'通过',
                                    classname: 'btn btn-xs btn-info btn-view btn-ajax',
                                    icon: 'fa fa-check',
                                     url: 'dotop/adopt?id={id}',
                                    visible:function(row){
                                        
                                        if(username >1){
                                            
                                           return false;
                                        }else{
                                            if(row['dai_id']!=0){
                                                  return true;
                                            }else{
                                                return false;
                                                //  if(row['status']==0){
                                                //     return true;
                                                // }else{
                                                    
                                                // }
                                            }
                                      
                                           
                                        }
                                     
                                    },
                                    refresh:true
                                },
                                
                                 {
                                    name: 'click',
                                    title: __('拒绝审核'),
                                    text: '拒绝审核',
                                    classname: 'btn btn-xs btn-info btn-click',
                                    // icon: 'fa fa-leaf',
                                    // dropdown: '更多',//如果包含dropdown，将会以下拉列表的形式展示
                                    click: function (e, row) {
                                        Layer.prompt({
                                            title: "拒绝原因",
                                            success: function (layero) {
                                                $("input", layero).prop("placeholder", "填写拒绝原因");
                                            }
                                        }, function (value) {
                                            
                                            
                                            // alert(value);return;
                                            Fast.api.ajax({
                                                 url: "dotop/cancel",
                                                data: {remark: value,uid: row.id},
                                            }, function (data, ret) {
                                                Layer.closeAll();
                                                $(".btn-refresh").trigger("click");
                                                //return false;
                                            });
                                            
                                        });
                                        return false;
                                    },visible:function(row){
                                           
                                        if(username >1){
                                            
                                           return false;
                                        }else{
                                              if(row['dai_id']!=0){
                                                  return true;
                                            }else{
                                                return false;
                                                //  if(row['status']==0){
                                                //     return true;
                                                // }else{
                                                    
                                                // }
                                            }
                                           
                                        }
                                     
                                    },
                                    refresh:true
                                }, 
                            ],formatter: Table.api.formatter.operate
                        }
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
        multipop: function () {
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
