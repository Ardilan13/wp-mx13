"use strict";(self["webpackChunkoptinmonster_wordpress_plugin_vue_app"]=self["webpackChunkoptinmonster_wordpress_plugin_vue_app"]||[]).push([[295],{61365:function(t,e,s){s.r(e),s.d(e,{default:function(){return d}});var n=function(){var t=this,e=t._self._c;return e("core-page",{staticClass:"omapi-monsterleads-wrapper"},[t.connected||t.isLoading?e("div",[e("common-alerts",{attrs:{alerts:t.alerts}}),e("common-tabnav",{attrs:{active:t.currentTab,tabs:t.allTabs},on:{go:t.navTo}}),e("monsterleads-".concat(t.currentTab),{tag:"component"})],1):e("monsterleads-not-connected"),e("monsterleads-modal-export")],1)},a=[],o=s(86080),r=s(20629),c=s(58850),i={mixins:[c.e],data:function(){return{pageSlug:"monsterleads"}},computed:(0,o.Z)((0,o.Z)((0,o.Z)({},(0,r.rn)(["alerts"])),(0,r.Se)(["connected","shouldFetchUser"])),{},{isLoading:function(){return this.shouldFetchUser||this.$store.getters.isLoading("monsterleads")}})},u=i,l=s(1001),g=(0,l.Z)(u,n,a,!1,null,null,null),d=g.exports},58850:function(t,e,s){s.d(e,{e:function(){return c}});var n=s(86080),a=s(27361),o=s.n(a),r=s(20629),c={computed:(0,n.Z)((0,n.Z)({},(0,r.Se)("tabs",["settingsTab","settingsTabs"])),{},{allTabs:function(){return this.$store.getters["tabs/".concat(this.pageSlug,"Tabs")]},currentTab:function(){return this.$store.getters["tabs/".concat(this.pageSlug,"Tab")]},selectedTab:function(){return this.$get("$route.params.selectedTab")}}),mounted:function(){this.goToSelected()},watch:{$route:function(t){this.goTo(o()(t,"params.selectedTab"))}},methods:(0,n.Z)((0,n.Z)({},(0,r.nv)("tabs",["goTab"])),{},{navTo:function(t){this.goTab({page:this.pageSlug,tab:t,baseUrl:""})},goTo:function(t){this.goTab({page:this.pageSlug,tab:t})},goToSelected:function(){this.selectedTab&&this.goTo(this.selectedTab)}})}}}]);
//# sourceMappingURL=monsterleads-legacy.5db9ab36.js.map