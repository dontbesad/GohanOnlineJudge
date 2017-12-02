var require = {
    //页面html名对应的接口
    config: {
        'index':'../api/index.php/problem/list/1',
        'status':'../api/index.php/problem/status/1',
        'problemlist':'../api/index.php/problem/list/1',
        'contestlist':'../api/index.php/contest/list/1',
        'ranklist':'../api/index.php',
        'other':'c'
    },
    //进入页面html名加载的时候
    init: function () {
        var html_name = require.get_html_doc_name();
        switch (html_name) {
            case 'index':
            case 'status':
            case 'problemlist':
            case 'contestlist':
            case 'ranklist':
            case 'other':
                $('#'+html_name).addClass("active");
                require.request(require.config[html_name], html_name);
                break;
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

    request: function(api_url, html_name) {
        $.ajax({
            url: api_url,
            type: 'GET',
            contentType: 'application/json; charset=utf-8',
            success: function(response) {
                switch (html_name) {
                    case 'index':
                        require.show_index(response.data);
                        break;
                    case 'status':
                        require.show_status(response.data);
                        break;
                    case 'problemlist':
                        require.show_problemlist(response.data);
                        break;
                    case 'contestlist':
                        require.show_contestlist(response.data);
                        break;
                    case 'ranklist':
                        require.show_ranklist(response.data);
                        break;
                    case 'other':
                        require.show_other(response.data);
                        break;
                    case '':
                        require.show_index(response.data);
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
verify.init();

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
        console.log(JSON.stringify(post_data));
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
register.init();
login.init();
