/*! AdminLTE app.js
 * ================
 * Main JS application file for AdminLTE v2. This file
 * should be included in all pages. It controls some layout
 * options and implements exclusive AdminLTE plugins.
 *
 * @Author  Almsaeed Studio
 * @Support <http://www.almsaeedstudio.com>
 * @Email   <support@almsaeedstudio.com>
 * @version 2.0.5
 * @license MIT <http://opensource.org/licenses/MIT>
 */
"use strict";function _init(){$.AdminLTE.layout={activate:function(){var a=this;a.fix(),a.fixSidebar(),$(window,".wrapper").resize(function(){a.fix(),a.fixSidebar()})},fix:function(){var a=$(".main-header").outerHeight()+$(".main-footer").outerHeight(),b=$(window).height(),c=$(".sidebar").height();$("body").hasClass("fixed")?$(".content-wrapper, .right-side").css("min-height",b-$(".main-footer").outerHeight()):b>=c?$(".content-wrapper, .right-side").css("min-height",b-a):$(".content-wrapper, .right-side").css("min-height",c)},fixSidebar:function(){return $("body").hasClass("fixed")?("undefined"==typeof $.fn.slimScroll&&console&&console.error("Error: the fixed layout requires the slimscroll plugin!"),void($.AdminLTE.options.sidebarSlimScroll&&"undefined"!=typeof $.fn.slimScroll&&($(".sidebar").slimScroll({destroy:!0}).height("auto"),$(".sidebar").slimscroll({height:$(window).height()-$(".main-header").height()+"px",color:"rgba(0,0,0,0.2)",size:"3px"})))):void("undefined"!=typeof $.fn.slimScroll&&$(".sidebar").slimScroll({destroy:!0}).height("auto"))}},$.AdminLTE.pushMenu=function(a){var b=this.options.screenSizes;$(a).click(function(a){a.preventDefault(),$(window).width()>b.sm-1?$("body").toggleClass("sidebar-collapse"):$("body").hasClass("sidebar-open")?($("body").removeClass("sidebar-open"),$("body").removeClass("sidebar-collapse")):$("body").addClass("sidebar-open")}),$(".content-wrapper").click(function(){$(window).width()<=b.sm-1&&$("body").hasClass("sidebar-open")&&$("body").removeClass("sidebar-open")})},
	$.AdminLTE.tree=function(a){
		var b=this;
		if($.cookie("menu-state")=="open"){
			var c=$("li.treeview").eq($.cookie("menu-index")),d=c.children("a").next();
				var e=c.parents("ul").first(),f=e.find("ul:visible").slideUp("normal");
				f.removeClass("menu-open");
				var g=c.parent("li");
				d.slideDown("normal",function(){
					d.addClass("menu-open"),
					e.find("li.active").removeClass("active"),
					g.addClass("active"),
					b.layout.fix()
				});
		}
		$(".sidebar-menu li.treeview").click(function(){
			var index = $(".sidebar-menu li.treeview").index(this);
			$.cookie("menu-index", index, {path:"/"});
		})

		$("li a",$(a)).click(function(a){
			var c=$(this),d=c.next();
			if(d.is(".treeview-menu")&&d.is(":visible")){
				d.slideUp("normal",function(){
					d.removeClass("menu-open")
				}),
				d.parent("li").removeClass("active");
				$.removeCookie("menu-state");
			}
			else if(d.is(".treeview-menu")&&!d.is(":visible")){
				var e=c.parents("ul").first(),f=e.find("ul:visible").slideUp("normal");
				f.removeClass("menu-open");
				var g=c.parent("li");
				d.slideDown("normal",function(){
					d.addClass("menu-open"),
					e.find("li.active").removeClass("active"),
					g.addClass("active"),
					b.layout.fix()
				});
				$.removeCookie("menu-index");
				$.cookie("menu-state", "open", {path:"/"});
			}
			d.is(".treeview-menu")&&a.preventDefault()
		})
	},
$.AdminLTE.boxWidget={activate:function(){var a=$.AdminLTE.options,b=this;$(a.boxWidgetOptions.boxWidgetSelectors.collapse).click(function(a){a.preventDefault(),b.collapse($(this))}),$(a.boxWidgetOptions.boxWidgetSelectors.remove).click(function(a){a.preventDefault(),b.remove($(this))})},collapse:function(a){var b=a.parents(".box").first(),c=b.find(".box-body, .box-footer");b.hasClass("collapsed-box")?(a.children(".fa-plus").removeClass("fa-plus").addClass("fa-minus"),c.slideDown(300,function(){b.removeClass("collapsed-box")})):(a.children(".fa-minus").removeClass("fa-minus").addClass("fa-plus"),c.slideUp(300,function(){b.addClass("collapsed-box")}))},remove:function(a){var b=a.parents(".box").first();b.slideUp()},options:$.AdminLTE.options.boxWidgetOptions}}if("undefined"==typeof jQuery)throw new Error("AdminLTE requires jQuery");$.AdminLTE={},$.AdminLTE.options={navbarMenuSlimscroll:!0,navbarMenuSlimscrollWidth:"3px",navbarMenuHeight:"200px",sidebarToggleSelector:"[data-toggle='offcanvas']",sidebarPushMenu:!0,sidebarSlimScroll:!0,enableBoxRefresh:!0,enableBSToppltip:!0,BSTooltipSelector:"[data-toggle='tooltip']",enableFastclick:!0,enableBoxWidget:!0,boxWidgetOptions:{boxWidgetIcons:{collapse:"fa fa-minus",open:"fa fa-plus",remove:"fa fa-times"},boxWidgetSelectors:{remove:'[data-widget="remove"]',collapse:'[data-widget="collapse"]'}},directChat:{enable:!0,contactToggleSelector:'[data-widget="chat-pane-toggle"]'},colors:{lightBlue:"#3c8dbc",red:"#f56954",green:"#00a65a",aqua:"#00c0ef",yellow:"#f39c12",blue:"#0073b7",navy:"#001F3F",teal:"#39CCCC",olive:"#3D9970",lime:"#01FF70",orange:"#FF851B",fuchsia:"#F012BE",purple:"#8E24AA",maroon:"#D81B60",black:"#222222",gray:"#d2d6de"},screenSizes:{xs:480,sm:768,md:992,lg:1200}},$(function(){var a=$.AdminLTE.options;_init(),$.AdminLTE.layout.activate(),$.AdminLTE.tree(".sidebar"),a.navbarMenuSlimscroll&&"undefined"!=typeof $.fn.slimscroll&&$(".navbar .menu").slimscroll({height:"200px",alwaysVisible:!1,size:"3px"}).css("width","100%"),a.sidebarPushMenu&&$.AdminLTE.pushMenu(a.sidebarToggleSelector),a.enableBSToppltip&&$(a.BSTooltipSelector).tooltip(),a.enableBoxWidget&&$.AdminLTE.boxWidget.activate(),a.enableFastclick&&"undefined"!=typeof FastClick&&FastClick.attach(document.body),a.directChat.enable&&$(a.directChat.contactToggleSelector).click(function(){var a=$(this).parents(".direct-chat").first();a.toggleClass("direct-chat-contacts-open")}),$('.btn-group[data-toggle="btn-toggle"]').each(function(){var a=$(this);$(this).find(".btn").click(function(b){a.find(".btn.active").removeClass("active"),$(this).addClass("active"),b.preventDefault()})})}),function(a){a.fn.boxRefresh=function(b){function c(a){a.append(f),e.onLoadStart.call(a)}function d(a){a.find(f).remove(),e.onLoadDone.call(a)}var e=a.extend({trigger:".refresh-btn",source:"",onLoadStart:function(){},onLoadDone:function(){}},b),f=a('<div class="overlay"><div class="fa fa-refresh fa-spin"></div></div>');return this.each(function(){if(""===e.source)return void(console&&console.log("Please specify a source first - boxRefresh()"));var b=a(this),f=b.find(e.trigger).first();f.click(function(a){a.preventDefault(),c(b),b.find(".box-body").load(e.source,function(){d(b)})})})}}(jQuery),function(a){a.fn.todolist=function(b){var c=a.extend({onCheck:function(){},onUncheck:function(){}},b);return this.each(function(){"undefined"!=typeof a.fn.iCheck?(a("input",this).on("ifChecked",function(){var b=a(this).parents("li").first();b.toggleClass("done"),c.onCheck.call(b)}),a("input",this).on("ifUnchecked",function(){var b=a(this).parents("li").first();b.toggleClass("done"),c.onUncheck.call(b)})):a("input",this).on("change",function(){var b=a(this).parents("li").first();b.toggleClass("done"),c.onCheck.call(b)})})}}(jQuery);