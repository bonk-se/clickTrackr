/*
 clickTrackr - http://bonk.se/clickTrackr/
 @version 0.6.1
 @author miken@bonk.se
*/
(function(){var c="",f=0,g,a={all:false,freq:1,refId:null,log:"/clickTrackr/log.php?",url:document.location.href,maxX:-1,maxY:-1},j=function(d){try{var b=d.pageX,e=d.pageY;if(a.refId){g=g||$("#"+a.refId);var h=g.offset();b-=h.left;e-=h.top}if(0<=b&&(0<a.maxX&&b<=a.maxX||0>a.maxX)&&0<=e&&(0<a.maxY&&e<=a.maxY||0>a.maxY))if(a.all){c+="["+b+","+e+"],";f++;10<=f&&i()}else c="["+b+","+e+"],"}catch(k){}return true},i=function(){try{if(5<=c.length){var d=new Date,b=new Image(1,1);b.onload=function(){};b.src=
a.log+"&u="+escape(a.url)+"&p=["+c.substr(0,c.length-1)+"]&t="+d.getTime();c="";f=0}}catch(e){}};window.bCT={init:function(d){$.extend(a,d);if(1==a.freq||1==Math.floor(Math.random()*a.freq+1))$(function(){$("body").click(j);window.onbeforeunload=i})}}})();
