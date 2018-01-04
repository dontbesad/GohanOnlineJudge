var web_root = '/OJ/web/';
var api_root = '/OJ/api/';
var api_list = {
    index: api_root + 'index.php/problem/list/',
    status: api_root + 'index.php/problem/status/',
    problemlist: api_root + 'index.php/problem/list/',
    contestlist: api_root + 'index.php/contest/list/',
    recentcontest: api_root + 'index.php/contest/recent_contest/',
    ranklist: api_root + 'index.php/problem/ranklist/',
    other: 'c',

    source_code: api_root + 'index.php/problem/source_code/',

    login: api_root + 'index.php/user/login',
    register: api_root + 'index.php/user/register',
    verify: api_root + 'index.php/user/verify',
    quit: api_root + 'index.php/user/quit',

    user_info: api_root + 'index.php/user/info/',
    problem_info: api_root + 'index.php/problem/info/'
}

var load_template = {
    init: function() {
        $.get(web_root + 'template/top.html', function(data) {
            $('#top').append(data);
            register.init();
            login.init();
            verify.init();

        });
    }
}



var require = {
    //页面html名对应的接口
    //进入页面html名加载的时候
    init: function () {

        var html_name = require.get_html_doc_name();
        var url_param = require.get_url_param('page');
        var size = 10; //每页的数目

        switch (html_name) {
            case 'status':
            case 'problemlist':
            case 'contestlist':
            case 'recentcontest':
            case 'ranklist':
            case 'other':
                $('#'+html_name).addClass("active");
                if (url_param == null) {
                    url_param = 1;
                }
                require.request(api_list[html_name] + url_param + '/' + size, html_name, url_param, size);
                break;
            case 'index':
                $('#index').addClass("active");
                require.request(api_list['index'], 'index');
                break;
            default:
                break;
        }
        //$('#main').css('background-image', 'url(/gohan.jpg)');
        //$(document.body).css('background', 'url(./image/gohan2.jpg) no-repeat fixed center');
        //$(document.body).css('background-size', '100%');
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
                        require.show_contestlist(data, page, size);
                        require.pagination(data.num, page, size);
                        break;
                    case 'recentcontest':
                        require.show_recentcontest(data);
                        break
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
        var str = '<table class="table table-hover table-bordered">';
        str += '<tr class="success"><th>提交号</th><th>用户</th><th>题号</th><th>状态</th><th>运行时间(MS)</th><th>运行内存(KB)</th><th>代码长度</th><th>语言</th><th>提交时间</th></tr>';
        $.each(data, function(index, value) {
            str += '<tr>';
            str += '<td>' + value['solution_id'] + '</td>';
            str += '<td>' + value['username'] + '</td>';
            str += '<td><a href="problem.html?pid='+value['problem_id']+'">' + value['problem_id'] + '</a></td>';
            if (value['result'] == 'Accepted') {
                str += '<td style="color:green">Accepted</td>';
            } else {
                str += '<td style="color:red">' + value['result'] + '</td>';
            }
            str += '<td>' + value['runtime'] + '</td>';
            str += '<td>' + value['memory'] + '</td>';
            str += '<td>' + value['code_length'] + '</td>';
            str += '<td><a href="' + value['solution_id'] + '" data-toggle="modal" data-target="#show_code">'+value["language"]+'</a></td>';
            str += '<td>' + value['submit_time'] + '</td>';
            str += '</tr>';

        });
        str += '</table>';
        $('#container').html(str);
        require.show_source_code();
    },

    show_source_code: function() {

        var escapeHTML = function(a){
             a = "" + a;
             return a.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&apos;").replace(/ /g, "&nbsp;").replace(/\n/g, "<br>");
        };

        $('a[data-target="#show_code"]').click(function() {
            console.log($(this).attr('href'));
            $.ajax({
                url: api_list.source_code + $(this).attr('href'),
                type: 'GET',
                contentType: 'application/json; charset=utf-8',
                success: function(response) {
                    if (!response.code) {
                        var str = '<code>';
                        str += escapeHTML(response.data.source_code);
                        str += '</code>';
                        $('#show_code .modal-body').html(str);
                        $('#show_code').modal('show');
                    } else {
                        alert(response.msg);
                    }
                },
                error: function() {
                    console.log('Error');
                }
            });
            return false;
        });
    },

    show_problemlist: function(data) {
        var data = data.list;
        var str = '<table class="table table-hover table-bordered">';
        str += '<tr class="success"><th>状态</th><th>编号</th><th>标题</th><th>AC/提交(通过率)</th></tr>';
        $.each(data, function(index, value) {
            str += '<tr>'
            if (value.status == 1) {
                str += '<td style="color:green;"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></td>';
            } else if (value.status == -1) {
                str += '<td style="color:red;"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></td>';
            } else {
                str += '<td></td>';
            }
            str += '<td><a href="problem.html?pid='+value['problem_id']+'">' + value['problem_id'] + '</a></td>';
            str += '<td><a href="problem.html?pid='+value['problem_id']+'">' + value['title'] + '</a></td>';
            var ratio = (parseInt(value['submit_num']) == 0 ? 0 : parseInt(value['accepted_num']) / parseInt(value['submit_num']));
            ratio *= 100;
            ratio = ratio.toFixed(2);
            str += '<td>' + value['accepted_num'] + ' / ' + value['submit_num'] + '（' + ratio + '%）' + '</td>';
            str += '</tr>';

        });
        str += '</table>';
        $('#container').html(str);
    },

    show_contestlist: function(data) {
        function pad(s) {
            return s < 10 ? '0' + s: s;
        }
        var data = data.list;
        var str = '<table class="table table-hover table-bordered">';
        str += '<tr class="success"><th>比赛编号</th><th>标题</th><th>开始时间</th><th>结束时间</th><th>比赛时长</th><th>比赛权限</th><th>比赛状态</th></tr>';
        $.each(data, function(index, value) {
            str += '<tr>'
            str += '<td><a href="contest.html?cid=' + value['contest_id'] + '">' + value['contest_id'] + '</a></td>';
            str += '<td><a href="contest.html?cid=' + value['contest_id'] + '">' + value['title'] + '</a></td>';
            str += '<td>' + value['start_time'] + '</td>';
            str += '<td>' + value['end_time'] + '</td>';

            var start = require.DateToUnix(value.start_time);
            var end   = require.DateToUnix(value.end_time);
            var now   = Date.parse(new Date()) / 1000;

            var hour = Math.floor((end - start) / 3600);
            var minu = Math.floor((end - start - hour * 3600) / 60);
            var seco = Math.floor(end - start - hour * 3600 - minu * 60);
            str += '<td>'+pad(hour)+':'+pad(minu)+':'+pad(seco)+'</td>';

            if (value['private'] == 1) {
                str += '<td><span style="color:red;">Private</span></td>';
            } else {
                str += '<td><span style="color:green;">Public</span></td>';
            }

            if (now < start) {
                str += '<td style="color:blue;">未开始</td>';
            } else if (now > end) {
                str += '<td>已结束</td>';
            } else {
                str += '<td style="color:red;">进行中</td>';
            }

            str += '</tr>';
        });
        str += '</table>';
        $('#container').html(str)
    },

    show_recentcontest: function(data) {
        var data = data.list;
        var str = '<table class="table table-hover table-bordered">';
        str += '<tr class="success"><th>OJ</th><th>Name</th><th>Start Time</th><th>Week</th><th>Access</th></tr>';
        $.each(data, function(index, value) {
            str += '<tr>';
            str += '<td>'+value.oj+'</td>';
            str += '<td><a href="'+value.link+'">'+value.name+'</a></td>';
            str += '<td>'+value.start_time+'</td>';
            str += '<td>'+value.week+'</td>';
            str += '<td>'+value.access+'</td>';
            str += '</tr>';
        });
        str += '</table>';
        str += '<span style="float:right;">本数据来源于<a href="http://contests.acmicpc.info/contests.json">acmicpc.info</a></span>';

        $('#container').html(str);
    },

    show_ranklist: function(data, page=1, size=10) {
        var data = data.list;
        var str = '<table class="table table-hover table-bordered">';
        str += '<tr class="success"><th>排名</th><th>用户名</th><th>昵称</th><th>解决量</th><th>提交量</th></tr>';
        $.each(data, function(index, value) {
            str += '<tr>';

            var rank = (page - 1) * size + index + 1;
            str += '<td>' + rank + '</td>';
            str += '<td><a href="user.html?uid='+value.user_id+'">' + value.username + '</a></td>';
            str += '<td>' + value.nickname + '</td>';
            str += '<td>' + value.solved_num + '</td>';
            str += '<td>' + value.submit_num + '</td>';

            str += '</tr>';

        });
        str += '</table>';

        $('#container').html(str);
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
                    str += '<li class="active"><a href="?page='+ i +'">'+ i +'</a></li>';
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
    },

    DateToUnix: function(string) {
        var f = string.split(' ', 2);
        var d = (f[0] ? f[0] : '').split('-', 3);
        var t = (f[1] ? f[1] : '').split(':', 3);
        return (new Date(
                parseInt(d[0], 10) || null,
                (parseInt(d[1], 10) || 1) - 1,
                parseInt(d[2], 10) || null,
                parseInt(t[0], 10) || null,
                parseInt(t[1], 10) || null,
                parseInt(t[2], 10) || null
                )).getTime() / 1000;
    }

};



var verify = {
    init: function() {
        $.ajax({
            url: api_list.verify,
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
        $('a[data-target="#register"]').css('display', 'none');
        $('a[data-target="#login"]').css('display', 'none');

        var str = '<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">';
        str += data.username + '&nbsp;&nbsp;<span class="caret"></span></a>';
        str += '<ul class="dropdown-menu dropdown-menu-right">';

        str += '<li><a href="user.html?uid='+data.user_id+'">个人信息</a></li>';

        if (data.admin) {
            str += '<li><a href="admin">后台页面</a></li>';
        }

        str += '<li role="separator" class="divider"></li>';

        str += '<li><a href="javascript:0;" id="quit">退出登录</a></li>';
        str += '</ul>';
        $('#account').html(str);

        $('#quit').click(function () {
            $.ajax({
                url: api_list.quit,
                type: 'GET',
                success: function() {
                    alert('成功退出');
                    location.reload();
                },
                error: function() {
                    console.log('Error');
                }
            });
            return false;
        });
    }
}


var register = {
    request: function() {
        var post_data = {
            username: $('#register #username').val(),
            nickname: $('#register #nickname').val(),
            password: $('#register #password').val(),
            password_again: $('#register #password_again').val(),
            email: $('#register #email').val(),
            school: $('#register #school').val(),
            description: $('#register #description').val()
        };
        console.log(JSON.stringify(post_data));
        $.ajax({
            url: api_list.register,
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
            url: api_list.login,
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


load_template.init();
require.init();
