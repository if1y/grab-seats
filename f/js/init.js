
var apiPath = "../backend/";
var frontEnd = "";
$.alert = function(message) {
    alert(message);
};
//字符串添加replaceAll方法
String.prototype.replaceAll = function(s1,s2){
　　return this.replace(new RegExp(s1,"gm"),s2);
}
var Template = {
    fill: function(str, params) {
        
        var newStr = str;
        for (var i in params) {
            
           newStr = newStr.replaceAll("{{"+i+"}}", params[i]);
        }
        return newStr;
    }
}

var Url = {
    getQueryParam: function (variable)
    {
           var query = window.location.search.substring(1);
           var vars = query.split("&");
           for (var i=0;i<vars.length;i++) {
                   var pair = vars[i].split("=");
                   if(pair[0] == variable){return pair[1];}
           }
           return(false);
    }
}

var Navi = {
    getUser: function(){
        $.get(apiPath+"getUser.php",{}, function(data){
                if (data.code == 0) {
                    var html = "<li><a>欢迎您："+data.data.account+"</a></li>";
                    html += "<li><a href='"+apiPath+"logout.php'>退出</a></li>";
                    $("#user-info").html(html);
                }
            } , 'json');
    }
}

