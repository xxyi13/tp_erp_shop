/**
 * 模拟U函数
 * @param url
 * @param params
 * @returns {string}
 * @constructor
 */
function U(url, params, rewrite) {
    var website = _ROOT_ + '/index.php';
    url = url.split('/');
    if (url[0] == '' || url[0] == '@')
        url[0] = APPNAME;
    if (!url[1])
        url[1] = 'Index';
    if (!url[2])
        url[2] = 'index';
    website = website + '?s=/' + url[0] + '/' + url[1] + '/' + url[2];
    if (params) {
        params = params.join('/');
        website = website + '/' + params;
    }
    if (!rewrite) {
        website = website + '.html';
    }
    return website;
}


$(function () {

    $(".my-popup").click(function(){
        var url     = $(this).attr('url');
        if( url == '' || url == null ){
            return false;
        }
        var title   = $(this).attr('title') || $(this).attr('rel');

        parent.layer.open({
            type: 2,
            title: title,
            shadeClose: true,
            shade: false,
            maxmin: true, //开启最大化最小化按钮
            area: ['80%', '90%'],
            content: url,
            end: function(){
                // window.location.reload();
            }
        });
    });


    //ajax form表单
    $(document).on('submit', 'form.ajax-form', function (e) {

        //取消默认动作，防止表单两次提交
        e.preventDefault();

        if ( $(this).hasClass('confirm') ) {
            if(!confirm( '确认要执行该操作吗?' )){
                return false;
            }
        }

        //禁用提交按钮，防止重复提交
        var form = $(this);
        $('[type=submit]', form).addClass('disabled');

        if( $(this).attr("data-attr") ){
            return false;
        }else{
            $(this).attr('data-attr',1);
        }

        //获取提交地址，方式
        var action = $(this).attr('action');
        var method = $(this).attr('method');

        //检测提交地址
        if (!action) {
            return false;
        }

        //默认提交方式为get
        if (!method) {
            method = 'get';
        }
        //获取表单内容
        var formContent = $(this).serialize();
        obj = 'form.ajax-form';

        //发送提交请求
        if (method == 'post' || method == 'POST') {

            $.post(action, formContent, function (data) {
                form.removeAttr('data-attr');

                myObj.ajaxOperation(data);

                $('[type=submit]', form).removeClass("disabled");

            }, 'json');
        }else if( method == 'get' || method == 'GET' ){
            $.get(action, formContent, function (data) {
                form.removeAttr('data-attr');

                myObj.ajaxOperation(data);

                $('[type=submit]', form).removeClass("disabled");



            }, 'json');
        }

        //返回
        return false;
    });


    //ajax get请求
    $('.ajax-get').click(function(){
        //获取表单内容
        var param = $(this).serialize();
        var target = $(this).attr('href');

        if ( $(this).hasClass('confirm') ) {
            if (!confirm("确认要执行该操作吗？")) {
                return false;
            }
        }

        myObj.ajaxGet(target, param);

        return false;
    });

    //ajax post请求
    $('.ajax-post').click(function(e){
        var target,query,form;
        var target_form = $(this).attr('target-form');
        var nead_confirm=false;

        if( ($(this).attr('type')=='submit') || (target = $(this).attr('href')) || (target = $(this).attr('url')) ){

            //取消默认动作，防止表单两次提交
            e.preventDefault();

            form = $('.'+target_form);

            if ($(this).attr('hide-data') === 'true'){//无数据时也可以使用的功能

                form = $('.hide-data');

                query = form.serialize();

            }else if (form.get(0)==undefined){
                return false;
            }else if ( form.get(0).nodeName=='FORM' ){

                if($(this).attr('url') !== undefined ){
                    target = $(this).attr('url');
                }else{
                    target = form.get(0).action;
                }
                query = form.serialize();

            }else if( form.get(0).nodeName=='INPUT' || form.get(0).nodeName=='SELECT' || form.get(0).nodeName=='TEXTAREA') {
                form.each(function(k,v){
                    if(v.type=='checkbox' && v.checked==true){
                        nead_confirm = true;
                    }
                });

                query = form.serialize();

            }else{

                query = form.find('input,select,textarea').serialize();
            }

            if ( $(this).hasClass('confirm') ) {
                if (!confirm('确认要执行该操作吗?')) {
                    return false;
                }
            }

            myObj.ajaxPost(target, query);
        }
        return false;
    });

});



var myObj = new Object();

/**
 * ajax get方式访问
 * @param target
 * @param param
 * @param operation_type
 * @param operation_id
 */
myObj.ajaxGet =function (target, param){

    if ( target != '' && target != null && target != 'javascript:void(0)' ) {
        $.get(target, param, function (data) {

            myObj.ajaxOperation(data);

        }, 'json');
    } else {
        swal("该操作出现错误，请稍后重试！", "", "error");
    }
};


myObj.ajaxPost = function(target, param) {
    if ( target != '' && target != null && target != 'javascript:void(0)' ) {
        $.post(target, param, function (data) {

            myObj.ajaxOperation(data);

        }, 'json');
    } else {
        swal("该操作出现错误，请稍后重试！", "", "error");
    }
};



myObj.ajaxOperation = function ( data) {

    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引

    //  配置默认的提示框
    toastr.options = {
        "closeButton": true,
        "debug": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": 3000,
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    if( data.status == 1 ) {
        toastr.success( data.info);

        setTimeout( function () {

            if( index ) {
                parent.layer.close(index); //再执行关闭
            }

            if( data.url !== '' ) {
                window.location.href = data.url;
            }

        }, 3000);
    } else {
        toastr.error( data.info );
    }

};


/* js cookie操作 */
function SetCookie(name,value)  //兩個參數，一個是cookie的名字，一個是值
{
    var Days = 7;               //此 cookie 將被保存7天
    var exp  = new Date();      //new Date("December 31, 9998");
    exp.setTime(exp.getTime() + Days*24*60*60*1000);
    document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}
function getCookie(name)        //取 cookies函數
{
    var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
    if(arr != null) return unescape(arr[2]); return null;
}

function delCookie(name)       //刪除 cookie
{
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval=getCookie(name);
    if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}


//加法函数
function accAdd(arg1, arg2) {
    var r1, r2, m;
    try {
        r1 = arg1.toString().split(".")[1].length;
    }
    catch (e) {
        r1 = 0;
    }
    try {
        r2 = arg2.toString().split(".")[1].length;
    }
    catch (e) {
        r2 = 0;
    }
    m = Math.pow(10, Math.max(r1, r2));
    return (arg1 * m + arg2 * m) / m;
}



//减法函数
function Subtr(arg1, arg2) {
    var r1, r2, m, n;
    try {
        r1 = arg1.toString().split(".")[1].length;
    }
    catch (e) {
        r1 = 0;
    }
    try {
        r2 = arg2.toString().split(".")[1].length;
    }
    catch (e) {
        r2 = 0;
    }
    m = Math.pow(10, Math.max(r1, r2));
    //last modify by deeka
    //动态控制精度长度
    n = (r1 >= r2) ? r1 : r2;
    return ((arg1 * m - arg2 * m) / m).toFixed(n);
}


//乘法函数
function accMul(arg1, arg2) {
    var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
    try {
        m += s1.split(".")[1].length;
    }
    catch (e) {
    }
    try {
        m += s2.split(".")[1].length;
    }
    catch (e) {
    }
    return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
}


//除法函数
function accDiv(arg1, arg2) {
    var t1 = 0, t2 = 0, r1, r2;
    try {
        t1 = arg1.toString().split(".")[1].length;
    }
    catch (e) {
    }
    try {
        t2 = arg2.toString().split(".")[1].length;
    }
    catch (e) {
    }
    with (Math) {
        r1 = Number(arg1.toString().replace(".", ""));
        r2 = Number(arg2.toString().replace(".", ""));
        return (r1 / r2) * pow(10, t2 - t1);
    }
}

