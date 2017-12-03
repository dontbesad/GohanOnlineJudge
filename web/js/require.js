var load_template = {
    init: function() {
        $.get('./template/top.html', function(data) {
            $('#top').html(data);

            register.init();
            login.init();
            verify.init();
        });
    }
}
load_template.init();

var require = {
    //页面html名对应的接口
    config: {
        'index':'../api/index.php/problem/list/',
        'status':'../api/index.php/problem/status/',
        'problemlist':'../api/index.php/problem/list/',
        'contestlist':'../api/index.php/contest/list/',
        'ranklist':'../api/index.php',
        'other':'c'
    },
    //进入页面html名加载的时候
    init: function () {
        var html_name = require.get_html_doc_name();
        var url_param = require.get_url_param('page');
        var size = 10; //每页的数目

        switch (html_name) {
            case 'status':
            case 'problemlist':
            case 'contestlist':
            case 'ranklist':
            case 'other':
                $('#'+html_name).addClass("active");
                if (url_param == null) {
                    url_param = 1;
                }
                require.request(require.config[html_name] + url_param + '/' + size, html_name, url_param, size);
                break;
            case 'index':
            case '':
                $('#index').addClass("active");
                require.request(require.config['index'], 'index');
                break;
            default:
                break;
        }
        //$('#main').css('background-image', 'url(/gohan.jpg)');
        //$('#main').css('background-size', '100%');
    },


    get_html_doc_name: function() {
        var str = window.location.href;
        if (str.lastIndexOf(".") === false) {
            return '';
        }
        str = str.substring(str.lastIndexOf("/") + 1);
        str = str.substring(0, str.lastIndexOf("."));
        return str;
    },

    get_url_param: function(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg);  //匹配目标参数
        if (r != null) return unescape(r[2]); return null; //返回参数值
    },

    request: function(api_url, html_name, page, size) {
        $.ajax({
            url: api_url,
            type: 'GET',
            contentType: 'application/json; charset=utf-8',
            success: function(response) {
                var data = response.data;
                switch (html_name) {
                    case 'index':
                        require.show_index(data);
                        require.pagination(data.num, page, size, size);
                        break;
                    case 'status':
                        require.show_status(data);
                        require.pagination(data.num, page, size);
                        break;
                    case 'problemlist':
                        require.show_problemlist(data);
                        require.pagination(data.num, page, size);
                        break;
                    case 'contestlist':
                        require.show_contestlist(data);
                        require.pagination(data.num, page, size);
                        break;
                    case 'ranklist':
                        require.show_ranklist(data);
                        require.pagination(data.num, page, size);
                        break;
                    case 'other':
                        require.show_other(data);
                        require.pagination(data.num, page, size);
                        break;
                    case '':
                        require.show_index(data);
                        require.pagination(data.num, page, size);
                        break;
                    default:
                        break;
                }
                console.log(response);
            },
            error: function() {
                console.log("error");
            }
        });
    },

    show_index: function(data) {
        console.log('index page');
    },

    show_status: function(data) {

        var data = data.list;
        var str = '<table class="table table-hover">';
        str += '<tr><th>提交号</th><th>用户</th><th>题号</th><th>状态</th><th>运行时间(MS)</th><th>运行内存(KB)</th><th>代码长度</th><th>语言</th><th>提交时间</th></tr>';
        $.each(data, function(index, value) {
            str += '<tr>';
            str += '<td>' + value['solution_id'] + '</td>';
            str += '<td>' + value['username'] + '</td>';
            str += '<td><a href="problem.html?id='+value['problem_id']+'">' + value['problem_id'] + '</a></td>';
            str += '<td>' + value['result'] + '</td>';
            str += '<td>' + value['runtime'] + '</td>';
            str += '<td>' + value['memory'] + '</td>';
            str += '<td>' + value['code_length'] + '</td>';
            str += '<td>' + value['language'] + '</td>';
            str += '<td>' + value['submit_time'] + '</td>';
            str += '</tr>';

        });
        str += '</table>';
        $('#container').html(str);
    },

    show_problemlist: function(data) {
        var data = data.list;
        var str = '<table class="table table-hover">';
        str += '<tr><th>编号</th><th>标题</th><th>提交数</th><th>AC数</th></tr>';
        $.each(data, function(index, value) {
            str += '<tr>'
            str += '<td><a href="problem.html?id='+value['problem_id']+'">' + value['problem_id'] + '</a></td>';
            str += '<td><a href="problem.html?id='+value['problem_id']+'">' + value['title'] + '</a></td>';
            str += '<td>' + value['submit_num'] + '</td>';
            str += '<td>' + value['accepted_num'] + '</td>';
            str += '</tr>';

        });
        str += '</table>';
        $('#container').html(str);
    },

    show_contestlist: function(data) {
        var data = data.list;
        var str = '<table class="table table-hover">';
        str += '<tr><th>比赛编号</th><th>标题</th><th>开始时间</th><th>结束时间</th><th>权限</th></tr>';
        $.each(data, function(index, value) {
            str += '<tr>'
            str += '<td>' + value['contest_id'] + '</td>';
            str += '<td>' + value['title'] + '</td>';
            str += '<td>' + value['start_time'] + '</td>';
            str += '<td>' + value['end_time'] + '</td>';
            str += '<td>' + (value['private']?'Public':'Private') + '</td>';
            str += '</tr>';

        });
        str += '</table>';
        $('#container').html(str)
    },

    show_ranklist: function(data) {
        $('#container').html('xxxx');
    },

    pagination: function(num, page=1, size=10) {
        if (num <= size) {
            return false;
        }
        //page为当前页
        var str = '<nav aria-label="Page navigation"><ul class="pagination">';
            if (page > 1) {
                var tmp = parseInt(page) - 1;
                str += '<li><a href="?page=' + tmp + '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
            }
            for (var i = 1; i <= Math.ceil(num / size); ++i) {
                if (i == page) {
                    str += '<li class="active"><a href=?page="'+ i +'">'+ i +'</a></li>';
                } else {
                    str += '<li><a href="?page='+ i +'">' + i + '</a></li>';
                }
            }
            if (page * size < num) {
                var tmp = parseInt(page) + 1;
                str += '<li><a href="?page='+ tmp +'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>'
            }
            str += '</ul></nav>';
        $('#container').after(str);
    }

};



var verify = {
    init: function() {
        $.ajax({
            url: '../api/index.php/user/verify',
            type: 'GET',
            contentType: 'application/json; charset=utf-8',
            success: function(response) {
                if (response.data.login) {
                    verify.show_account(response.data);
                } else {
                    console.log('not login');
                }
            },
            error: function() {
                console.log('error');
            }
        })
    },
    show_account: function(data) {
        $('#account').html('<button class="btn btn-primary" type="button">'+data.username+'&nbsp;<span class="badge">平民</span></button>');
    }
}


var register = {
    request: function() {
        var post_data = {
            username: $('#register #username').val(),
            password: $('#register #password').val(),
            password_again: $('#register #password_again').val(),
            email: $('#register #email').val(),
            school: $('#register #school').val(),
            description: $('#register #description').val()
        };
        console.log(JSON.stringify(post_data));
        $.ajax({
            url: '../api/index.php/user/register',
            type: 'POST',
            data: JSON.stringify(post_data),
            contentType: 'application/json; charset=utf-8',
            success: function(response) {
                console.log(response);
                if (response.data) {
                    alert("注册成功");
                    $("#register").modal('hide');
                } else {
                    alert(response.msg);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log('Error');
            }
        })
    },
    init: function() {
        $('#btn_register').click(function() {
            register.request();
        });
    }
}

var login = {
    request: function() {
        var post_data = {
            username: $('#login #username').val(),
            password: $('#login #password').val(),
        };
        $.ajax({
            url: '../api/index.php/user/login',
            type: 'POST',
            data: JSON.stringify(post_data),
            contentType: 'application/json; charset=utf-8',
            success: function(response) {
                console.log(response);
                if (response.data) {
                    alert("登录成功");
                    $("#login").modal('hide');
                    verify.init();
                } else {
                    alert(response.msg);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log('Error');
            }
        })
    },
    init: function() {
        $('#btn_login').click(function() {
            login.request();
        });
    }
}


require.init();
