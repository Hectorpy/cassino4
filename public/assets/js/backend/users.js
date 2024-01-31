


define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'users/index' + location.search,
                    add_url: 'users/add',
                    edit_url: 'users/edit',
                    del_url: '',
                    multi_url: 'users/multi',
                    import_url: 'users/import',
                    table: 'users',
                    multi_pop_url: 'users/multipop',
                    detail_url: 'users/detail',
                }
            });
            var info=0;
            var table = $("#table");
       table.on('load-success.bs.table', function (e, data) {
                //这里可以获取从服务端获取的JSON数据
                info= data.info
            });

/**
 * 批量弹窗
 */
$(document).on("click", ".btn-multi-pop", function (e) {
    // 获取选中的列表ID
    var ids = Table.api.selectedids(table);
    Fast.api.open($.fn.bootstrapTable.defaults.extend.multi_pop_url + "?ids=" + ids, '批量生成弹窗')
});


/**
 * 批量弹窗
 */
$(document).on("click", ".btn-detail", function (e) {
    // 获取选中的列表ID
    var ids = Table.api.selectedids(table);
    Fast.api.open($.fn.bootstrapTable.defaults.extend.detail_url + "?ids=" + ids, '批量生成弹窗')
});




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
                        {field: 'pid', title: __('Pid')},
                         {field: 'stat', title: __('是否封禁'), searchList: {"1": __('Yes'), "0": __('No')}, formatter: Table.api.formatter.toggle},
                        
                        // {field: 'group_id', title: __('Group_id')},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        // {field: 'password', title: __('Password'), operate: 'LIKE'},
                        // {field: 'salt', title: __('Salt'), operate: 'LIKE'},
                        // {field: 'email', title: __('Email'), operate: 'LIKE'},
                        // {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        // {field: 'avatar', title: __('Avatar'), operate: 'LIKE', events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'level', title: __('Level')},
                        // {field: 'gender', title: __('Gender')},
                        // {field: 'birthday', title: __('Birthday'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        // {field: 'bio', title: __('Bio'), operate: 'LIKE'},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        // {field: 'score', title: __('Score')},
                        // {field: 'successions', title: __('Successions')},
                        // {field: 'maxsuccessions', title: __('Maxsuccessions')},
                        // {field: 'prevtime', title: __('Prevtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'logintime', title: __('Logintime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'loginip', title: __('Loginip'), operate: 'LIKE'},
                        // {field: 'loginfailure', title: __('Loginfailure')},
                        {field: 'joinip', title: __('Joinip'), operate: 'LIKE'},
                        {field: 'jointime', title: __('Jointime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'token', title: __('Token'), operate: 'LIKE'},
                        // {field: 'status', title: __('Status'), searchList: {"30":__('Status 30')}, formatter: Table.api.formatter.status},
                        {field: 'verification', title: __('Verification'), operate: 'LIKE'},
                       {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                
                                    {
                                    confirm: '确定一键回收吗？每个玩家每分钟内请求不能超过 2 次。请求超时时间必须设置大于 30 秒。部分游戏平台在游戏中无法转换额度。',
                                    name:'adopt',
                                    text:'回收',
                                    title:'回收',
                                    classname: 'btn btn-xs btn-info btn-view btn-ajax',
                                    icon: 'fa fa-check',
                                     url: 'users/Transfer?id={id}',
                                    visible:function(row){
                                        
                                        if(username >1){
                                            
                                           return false;
                                        }else{
                                            return true;
                                        }
                                     
                                    },
                                    refresh:true
                                },
                           
                                  {
                                    name: 'click',
                                    title: __('上下分'),
                                    text: '上下分',
                                    classname: 'btn btn-xs btn-primary btn-click',
                                      visible:function(row){
                                          
                                     
                                        if(username==1){
                                            
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    },
                                    // icon: 'fa fa-leaf',
                                    // dropdown: '更多',//如果包含dropdown，将会以下拉列表的形式展示
                                    
                                      click: function (e, row) {
                                 
                                          
                                 layer.open({
                                        title: '上下分管理',
                                        btn: ['确定'],
                                        area: ['800px', '550px'] //自定义文本域宽高
                                            ,
                                        content: '<div class="form-group">' +
                                            '<label class="control-label col-xs-12 col-sm-2">类型</label>' +
                                            '<div class="col-xs-12 col-sm-8">' +
                                            '<select name="range" id="range" class="form-control selectpicker">' +
                                            '<option value="0">充值</option>' +
                                            '<option value="1">扣除</option>' +
                                            '</select>' +
                                            '</div>' +
                                            '</div>' +
                            
                                            
                            
                                            '<div class="form-group">' +
                                            ' <label class="control-label col-xs-12 col-sm-2">金额</label>' +
                                            '<div class="col-xs-12 col-sm-8">' +
                                            ' <input id="coins" data-rule="required" class="form-control" name="coins" type="number" value="">' +
                                            '</div>' +
                                            '</div>'+
                                            
                                               '<div class="form-group">' +
                                            ' <label class="control-label col-xs-12 col-sm-2">备注</label>' +
                                            '<div class="col-xs-12 col-sm-8">' +
                                            ' <input id="bz" data-rule="required" class="form-control" name="bz" type="number" value="">' +
                                            '</div>' +
                                            '</div>',
                                        yes: function(index, layero) {
                                            var range = $('#range').val();
                                            var moneys = $('#coins').val()
                                             var coins = $('#bz').val()
                                           
                                          
                                             if (!moneys) {
                                                layer.msg('请填写金额~', {
                                                    type: 1,
                                                });
                                                return false;
                                            }
                                              if (!coins) {
                                                layer.msg('请填写备注~', {
                                                    type: 1,
                                                });
                                                return false;
                                            }
                                             Fast.api.ajax({
                                                url: "/NHujKZCbMg.php/Users/accesss",
                                                data: {uid: row.id,range:range,money:moneys,coin:coins,types:0},
                                            }, function (data, ret) {
                                                Layer.closeAll();
                                                $(".btn-refresh").trigger("click");
                                                //return false;
                                            });
                                        }
                                    })
                                     
                                        return false;
                                    },
                                
                               
                                
                                },
                                   {
                                      name: 'click',
                                    title: __('上分申请'),
                                    text: '上分申请',
                                    classname: 'btn btn-xs btn-primary btn-click',
                                      visible:function(row){
                                          
                                     
                                        if(username >1){
                                            
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    },
                                    // icon: 'fa fa-leaf',
                                    // dropdown: '更多',//如果包含dropdown，将会以下拉列表的形式展示
                                    
                                      click: function (e, row) {
                                 
                                          
                                 layer.open({
                                        title: '上分提交申请',
                                        btn: ['确定'],
                                        area: ['800px', '550px'] //自定义文本域宽高
                                            ,
                                        content: '<div class="form-group">' +
                                            '<label class="control-label col-xs-12 col-sm-2">类型</label>' +
                                            '<div class="col-xs-12 col-sm-8">' +
                                            '<select name="range" id="range" class="form-control selectpicker">' +
                                            '<option value="0">充值</option>' +
                                            '<option value="1">扣除</option>' +
                                            '</select>' +
                                            '</div>' +
                                            '</div>' +
                            
                                            
                                            '<div class="form-group">' +
                                            ' <label class="control-label col-xs-12 col-sm-2">金额</label>' +
                                            '<div class="col-xs-12 col-sm-8">' +
                                            ' <input id="coins" data-rule="required" class="form-control" name="coins" type="number" value="">' +
                                            '</div>' +
                                            '</div>'+
                                            
                                               '<div class="form-group">' +
                                            ' <label class="control-label col-xs-12 col-sm-2">备注</label>' +
                                            '<div class="col-xs-12 col-sm-8">' +
                                            ' <input id="bz" data-rule="required" class="form-control" name="bz" type="number" value="">' +
                                            '</div>' +
                                            '</div>',
                                        yes: function(index, layero) {
                                            var range = $('#range').val();
                                            var moneys = $('#coins').val()
                                             var coins = $('#bz').val()
                                           
                                          
                                             if (!moneys) {
                                                layer.msg('请填写金额~', {
                                                    type: 1,
                                                });
                                                return false;
                                            }
                                              if (!coins) {
                                                layer.msg('请填写备注~', {
                                                    type: 1,
                                                });
                                                return false;
                                            }
                                             Fast.api.ajax({
                                                url: "/NHujKZCbMg.php/Users/access",
                                                data: {uid: row.id,range:range,money:moneys,coin:coins,types:1},
                                            }, function (data, ret) {
                                                Layer.closeAll();
                                                $(".btn-refresh").trigger("click");
                                                //return false;
                                            });
                                        }
                                    })
                                     
                                        return false;
                                    },
                                
                               
                                
                                },   {
                                    name: 'looktaem',
                                    text: __('下级'),
                                    title: __('下级'),
                                    classname: 'btn btn-xs btn-info btn-addtabs',
                                    url: 'users?uid={id}',
                                  },  
                                  {
                                    name: 'looktaem',
                                    text: __('提现信息'),
                                    title: __('提现信息'),
                                    classname: 'btn btn-xs btn-info btn-addtabs',
                                    url: 'bank?uid={id}',
                                  },
                                   {
                                    name: 'looktaem',
                                    text: __('详情'),
                                    title: __('详情'),
                                    classname: 'btn btn-xs btn-info btn-addtabs',
                                    url: 'users/detail?id={id}',
                                  },
                            ],
                            
                            
                            formatter: Table.api.formatter.operate}
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
        detail: function () {
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
