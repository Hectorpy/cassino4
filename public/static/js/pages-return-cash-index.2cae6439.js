(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-return-cash-index"],{"0b2f":function(t,e,a){"use strict";a.d(e,"b",(function(){return r})),a.d(e,"c",(function(){return i})),a.d(e,"a",(function(){return n}));var n={uNavbar:a("ed7b").default,"u-Image":a("c033").default,uParse:a("290c").default,uButton:a("395b").default},r=function(){var t=this.$createElement,e=this._self._c||t;return e("v-uni-view",{staticClass:"return_cash"},[e("u-navbar",{attrs:{title:"充值现金返还",autoBack:!0,bgColor:"#1d1f2b",leftIconColor:"#fff",leftIconSize:"40rpx",titleStyle:{color:"#fff",fontSize:"32rpx"},height:"120rpx"}}),e("v-uni-view",{staticClass:"f_s_head d_border12 d_flex"},[e("v-uni-view",{staticClass:"s_h_fl"},[e("u--image",{attrs:{src:"../../static/images/jogos/ad_1.png",mode:"widthFix",width:"280rpx",height:"315rpx"}})],1),e("v-uni-view",{staticClass:"s_h_fr d_flex"},[e("v-uni-text",[this._v("首次存款")]),e("v-uni-text",[this._v("+ 20%奖金")])],1)],1),e("v-uni-view",{staticClass:"tips_text"},[e("v-uni-view",[this._v("A partir de agora, a recarga pode obter recompensas extras em dinheiro.")]),e("v-uni-view",[this._v("Quanto mais você recarregar, maior será a taxa de recompensa, até 10%. Após a recarga, o dinheiro extra também será transferido diretamente para a sua conta.")])],1),e("v-uni-view",{staticClass:"f_s_con d_bgColor"},[e("v-uni-view",{staticStyle:{color:"#ffa548","margin-bottom":"30rpx"}},[this._v("Nota especial:")]),e("v-uni-view",{staticClass:"content"},[e("u-parse",{attrs:{content:this.content}})],1),e("v-uni-view",{staticClass:"f_s_btn"},[e("u-button",{attrs:{type:"primary",text:"Recarregue agora",shape:"circle",customStyle:{height:"80rpx"},color:"linear-gradient(to bottom, rgb(242, 165, 96), rgb(235, 68, 204))"}})],1)],1)],1)},i=[]},"19a3":function(t,e,a){"use strict";a.r(e);var n=a("0b2f"),r=a("c816");for(var i in r)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return r[t]}))}(i);a("9166");var s=a("f0c5"),o=Object(s["a"])(r["default"],n["b"],n["c"],!1,null,"195529d0",null,!1,n["a"],void 0);e["default"]=o.exports},2698:function(t,e,a){"use strict";a("7a82");var n=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.banner=function(){return(0,r.default)({url:"index/getban",method:"POST"})},e.enterGame=function(t){return(0,r.default)({url:"index/in_game",method:"POST",data:t})},e.eventDetails=function(t){return(0,r.default)({url:"index/get_contents",method:"POST",data:{type:t}})},e.gameList=function(t){return(0,r.default)({url:"index/game_index",method:"POST",data:t})},e.notice=function(){return(0,r.default)({url:"index/get_msg",method:"POST"})};var r=n(a("a558"))},"4b0e":function(t,e,a){var n=a("b4b7");n.__esModule&&(n=n.default),"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var r=a("4f06").default;r("1b1a1648",n,!0,{sourceMap:!1,shadowMode:!1})},9166:function(t,e,a){"use strict";var n=a("4b0e"),r=a.n(n);r.a},b4b7:function(t,e,a){var n=a("24fb");e=n(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * uni-app内置的常用样式变量\n */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */\n/* uni.scss */.return_cash[data-v-195529d0]{padding:%?130?% %?20?% 0 %?20?%}.return_cash .f_s_head[data-v-195529d0]{width:100%;height:%?300?%;background:linear-gradient(1turn,#ea3ed2,#f1a064)}.return_cash .f_s_head .s_h_fl[data-v-195529d0]  .u-image__image{position:relative;top:%?-15?%}.return_cash .f_s_head .s_h_fr[data-v-195529d0]{padding-left:%?20?%;flex-direction:column;font-size:%?35?%;color:#fff;font-style:italic}.return_cash .f_s_head .s_h_fr > uni-text[data-v-195529d0]:nth-child(1){margin:%?50?% 0 %?30?% 0;font-size:%?28?%}.return_cash .tips_text[data-v-195529d0]{margin:%?20?% 0;color:hsla(0,0%,100%,.8);font-size:%?26?%;line-height:1.5}.return_cash .f_s_con[data-v-195529d0]{padding:%?20?%;margin:%?20?% 0}.return_cash .f_s_con .content[data-v-195529d0]{padding:%?20?%;margin-bottom:%?20?%;min-height:%?500?%;color:#fff;border:1px dashed rgba(0,0,0,.5)}',""]),t.exports=e},c816:function(t,e,a){"use strict";a.r(e);var n=a("c860"),r=a.n(n);for(var i in n)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(i);e["default"]=r.a},c860:function(t,e,a){"use strict";a("7a82");var n=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r=n(a("c7eb")),i=n(a("1da1")),s=a("2698"),o={data:function(){return{content:"",type:3}},onLoad:function(){this.getEventDetails()},methods:{getEventDetails:function(){var t=this;return(0,i.default)((0,r.default)().mark((function e(){var a,n;return(0,r.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return e.next=2,(0,s.eventDetails)(t.type);case 2:a=e.sent,a.code,n=a.data,t.content=n.content;case 6:case"end":return e.stop()}}),e)})))()}}};e.default=o}}]);