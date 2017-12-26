var contest = {
    api_url: {
        'index': '../../api/index.php/contest/info/',
        'problem-list': '../../api/index.php/contest/problem_list/',
        'status': '../../api/index.php/contest/status/',
        'rank': '../../api/index.php/contest/rank/',
        'problem': '../../api/index.php/contest/problem/',
        'submit': '../../api/index.php/contest/submit',
        'contest_register': '../../api/index.php/contest/register',
    },
    init: function() {
        contest.index(); //比赛基本信息
        if (location.href.indexOf('#') > 0) {
            var action = location.href.split('#')[1];

            if (action.split('-')[0] == 'problem') {
                $('#contest_header a[href="#problem-list"]').parent().addClass('active');
            } else if (action == 'status') {
                $('#contest_header a[href="#status"]').parent().addClass('active');
            } else if (action == 'rank') {
                $('#contest_header a[href="#rank"]').parent().addClass('active');
            }
            contest.load(action);
        }

        $('#contest_header li').click(function(x) {
            $('#contest_header li').each(function(i) {
                $(this).removeClass('active');
            });
            $(this).addClass('active');
            var action = $(this).children().attr('href').split('#')[1];
            contest.load(action);
        });



    },
    load: function(action) {
        var id = contest.get_url_param('cid');

        var sign   = action.split('-')[1];
        action = action.split('-')[0];

        if (sign == undefined && action != 'problem' && contest.api_url[action]) {
            contest.request(action, sign, contest.api_url[action] + id);
        } else if (action == 'status' && sign == undefined) {
            var page = contest.get_url_param('page');
            if (page == undefined) {
                page = 1;
            }
            contest.request(action, sign, contest.api_url[action] + id + '/' + page + '/10');
        } else if (action == 'problem' && sign == 'list') {
            contest.request(action, sign, contest.api_url[(action + '-' + sign)] + id)
        } else if (action == 'problem' && sign != undefined) {
            contest.request(action, sign, contest.api_url[action] + id + '/' + sign);
        }
    },
    get_url_param: function(name) {
        //构造一个含有目标参数的正则表达式对象
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        //匹配目标参数
        var r = window.location.search.substr(1).match(reg);
        //返回参数值
        if (r != null) return unescape(r[2]);
        //不存在时返回null
        return null;
    },
    request: function(action, sign, api_url) {

        $.ajax({
            url: api_url,
            type: 'GET',
            contentType: 'application/json; charset=utf-8',
            success: function(response) {
                if (response.code == 0) {
                    switch (action) {
                        case 'problem':
                            if (sign == 'list') {
                                contest.show_problem_list(response.data);
                            } else if (sign != undefined) {
                                contest.show_problem(response.data);
                            } else {
                                console.log('Empty');
                            }
                            break;
                        case 'status':
                            contest.show_status(response.data);
                            var page = contest.get_url_param('page');
                            if (page == undefined) {
                                page = 1;
                            }
                            contest.pagination('&cid=3#status', response.data.num, page, 10);
                            break;
                        case 'rank':
                            contest.show_rank(response.data);
                            break;
                        default:
                            break;
                    }
                } else {
                    alert(response.msg);
                }
            },
            error: function() {
                console.log('Error');
            }
        });
    },
    index: function() {
        var id = contest.get_url_param('cid');
        $.ajax({
            url: contest.api_url.index + id,
            type: 'GET',
            contentType: 'application/json; charset=utf-8',
            success: function(response) {
                if (response.code == 0) {
                    contest.show_contest_info(response.data);
                } else {
                    alert(response.msg);
                }
            },
            error: function() {
                console.log("Error");
            }
        });
    },

    show_contest_info: function(data) {
        function pad(s) {
            return s < 10 ? '0' + s: s;
        }

        $('#title').html(data.title);
        if (data.description) {
            $('#container .description').html('<div class="alert alert-info" role="alert">'+data.description+'</div>');
        }
        $('#start_time').html('Start: ' + data.start_time);
        $('#end_time').html('End: ' + data.end_time);

        var start = contest.DateToUnix(data.start_time);
        var end   = contest.DateToUnix(data.end_time);
        var now   = Date.parse(new Date()) / 1000;
        //比赛未开始
        if (now < start) {
            $('#contest_header').html('');
            if (data.login == false) {
                $('#contest_header').html('<div class="alert alert-success" role="alert" style="text-align:center">请先登录~</div>');
            } else if (data.contest_register == false) {
                var str = '<div class="form-group" style="text-align:center; padding:1.2em 1.8em; border-radius:.6em;">';

                if (data.private == 1) {
                    str += '<input type="text" class="form-control" id="contest_password" placeholder="比赛密码"><br>';
                    str += '<button class="btn btn-info" id="btn_contest_register">注册比赛</button>';
                    str += '</div>';
                    $('#contest_header').html(str);
                } else {
                    str += '<button class="btn btn-info" id="btn_contest_register">注册比赛</button>';
                    str += '</div>';
                    $('#contest_header').html(str);
                }

                contest.contest_register();

            } else {
                $('#contest_header').html('<div class="alert alert-success" role="alert" style="text-align:center">你已注册此次比赛了哦~~</div>');
            }

        } else if (data.private == 1) {
            if (data.login == false) {
                $('#contest_header').html('<div class="alert alert-success" role="alert" style="text-align:center">请先登录~</div>');
            } else if (data.contest_register == false) {
                $('#contest_header').html('<div class="alert alert-warning" role="alert" style="text-align:center">你没有注册此次私有比赛，没法查看哦~</div>');
            }
        }

        if (now < start) {
            var countdown_clock = setInterval(function() {
                if (now == start) {
                    clearInterval(countdown_clock);
                    location.reload();
                }
                now++;
                var h = Math.floor((start - now) / 3600);
                var i = Math.floor((start - now - h * 3600) / 60);
                var s = start - now - h * 3600 - i * 60;
                $('#countdown').html('<span style="color:SteelBlue;">比赛倒计时: ' + pad(h) + ':' + pad(i) + ':' + pad(s) + '</span>');
            }, 1000);

            return ;
        } else if (now > end) {
            $('#progress').attr('class', 'progress-bar progress-bar-success');

            $('#countdown').html('已结束');

            $('#progress').html('100%');
            $('#progress').css('width', '100%');

        } else {
            $('#countdown').html('<span style="color:red;">进行中</span>');
            var diff  = Math.floor((now - start) * 100 / (end - start));
            $('#progress').html(diff + '%');
            $('#progress').css('width', diff + '%');

            var running_clock = setInterval(function() {
                if (now == end) {
                    clearInterval(running_clock);
                    location.reload();
                }
                now++;
                var diff  = Math.floor((now - start) * 100 / (end - start));
                $('#progress').html(diff + '%');
                $('#progress').css('width', diff + '%');
            }, 1000);
            return ;
        }


    },
    //比赛中的题目列表
    show_problem_list: function(data) {
        var str = '<table class="table table-hover" id="problem_list">';
        str += '<tr><th>解决</th><th>ID</th><th>标题</th><th>比例(解决数/提交数目)</th></tr>';
        $.each(data.list, function(index, value) {
            var ratio = (parseInt(value['submit_num']) == 0 ? 0 : parseInt(value['solved_num']) / parseInt(value['submit_num']));
            ratio *= 100;
            ratio = ratio.toFixed(2);
            str += '<tr>';
            if (value['status'] == 1) {
                str += '<td><span class="glyphicon glyphicon-ok" style="color:green;"></span></td>';
            } else if (value['status'] == -1) {
                str += '<td><span class="glyphicon glyphicon-remove" style="color:red;"></span></td>';
            } else {
                str += '<td></td>';
            }
            str += '<td><a href="#problem-'+value['order_id']+'">' + value['order_id'] + '</a></td>';
            str += '<td><a href="#problem-'+value['order_id']+'">' + value['title'] + '</td>';
            str += '<td>' + ratio + '%(' + value['solved_num'] + '/' + value['submit_num'] + ')</td>';
            str += '</tr>';
        });
        str += '</table>';
        $('#display').html(str);
        $('#problem_list a').click(function() {
            var action = $(this).attr('href').split('#')[1];
            contest.load(action);
        });

    },
    show_status: function(data) {
        //console.log(data);
        var data = data.list;
        var str = '<table class="table table-hover">';
        str += '<tr><th>比赛提交号</th><th>用户</th><th>比赛题号</th><th>状态</th><th>运行时间(MS)</th><th>运行内存(KB)</th><th>代码长度</th><th>语言</th><th>提交时间</th></tr>';
        $.each(data, function(index, value) {
            str += '<tr>';
            str += '<td>' + value['solution_id'] + '</td>';
            str += '<td>' + value['username'] + '</td>';
            str += '<td><a href="?cid='+ value['contest_id'] +'#problem-'+value['order_id']+'">' + value['order_id'] + '</a></td>';
            if (value['result'] == 'Accepted') {
                str += '<td style="color:green">Accepted</td>';
            } else {
                str += '<td style="color:red">' + value['result'] + '</td>';
            }
            str += '<td>' + value['runtime'] + '</td>';
            str += '<td>' + value['memory'] + '</td>';
            str += '<td>' + value['code_length'] + '</td>';
            str += '<td>' + value['language'] + '</td>';
            str += '<td>' + value['submit_time'] + '</td>';
            str += '</tr>';

        });
        str += '</table>';

        $('#display').html(str);

        $('#display table a').click(function(x) {
            var action = $(this).attr('href').split('#')[1];
            contest.load(action);
        });
    },
    contest_register: function() {
        $('#btn_contest_register').click(function() {
            var contest_id = contest.get_url_param('cid');
            var contest_password = $('#contest_password').val();

            var post_data = {
                contest_id: contest_id,
                contest_password: contest_password,
            };

            $.ajax({
                url: contest.api_url.contest_register,
                type: 'POST',
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify(post_data),
                success: function(response) {
                    if (response.code != 0) {
                        alert(response.msg);
                    } else {
                        alert('比赛注册成功');
                        location.reload();
                    }
                },
                error: function() {
                    console.log('Error');
                }
            });
        });
    },
    show_rank: function(data) {
        $('#display').html(data);

        var data = data.list;
        var str = '<table class="table table-hover">';
        str += '<tr><th>排名</th><th>队伍名</th></tr>';
        $.each(data, function(index, value) {
            str += '<tr>';
            str += '<td>' + (parseInt(index) + 1) + '</td>';
            str += '<td>' + value['username'] + '</td>';
            str += '</tr>';

        });
        str += '</table>';
        $('#display').html(str);
    },
    show_problem: function(data) {
        console.log(data);
        var str = '<ul class="list-group">';
        str += '<li class="list-group-item list-group-item-info" style="text-align:center; font-size:1.3em;"><h2>'+　data.title +'</h2>';
        str += '<span class="label label-default">时间限制:'+ data.time_limit +'MS</span>&nbsp;<span class="label label-default">内存限制:'+ data.memory_limit +'KB</span></li>';

        var description = data.description.replace('\n', '<br>').replace(' ', '&nbsp;');
        str += '<li class="list-group-item"><strong>题目描述:</strong><p>'+　description +'</p></li>';

        var input = data.input.replace('\n', '<br>').replace(' ', '&nbsp;');
        str += '<li class="list-group-item"><strong>输入:</strong><p>'+　input +'</p></li>';

        var output = data.output.replace('\n', '<br>').replace(' ', '&nbsp;');
        str += '<li class="list-group-item"><strong>输出:</strong><p>'+　output +'</p></li>';

        var sample_input = data.sample_input.replace('\n', '<br>').replace(' ', '&nbsp;');
        str += '<li class="list-group-item"><strong>输入样例:</strong><p>'+　sample_input +'</p></li>';

        var sample_output = data.sample_output.replace('\n', '<br>').replace(' ', '&nbsp;');
        str += '<li class="list-group-item"><strong>输出样例:</strong><p>'+　sample_output +'</p></li>';

        var hint = data.hint.replace('\n', '<br>').replace(' ', '&nbsp;');
        str += '<li class="list-group-item"><strong>提示:</strong><p>'+　hint +'</p></li>';

        var source = data.source.replace('\n', '<br>').replace(' ', '&nbsp;');
        str += '<li class="list-group-item"><strong>来源:</strong><p>'+ source +'</p></li>';

        str += '<li class="list-group-item"><button type="button" class="btn btn-default" data-toggle="modal" data-target="#sourceModal">提交</button></li>';

        str += '</ul>';

        $('#order_id').val(data.order_id);
        $('#display').html(str);

        contest.submit_source();
    },
    //比赛中提交代码
    submit_source: function() {
        $(document.body).on('click', '#btn_source', function() {
            var post_data = {
                order_id:    $('#order_id').val(),
                contest_id:  contest.get_url_param('cid'),
                language:    $('#language').val(),
                source_code: $('#source_code').val()
            };
            console.log('wc');
            $.ajax({
                url: contest.api_url.submit,
                type: 'POST',
                data: JSON.stringify(post_data),
                contentType: 'application/json; charset=utf-8',
                success: function(response) {
                    if (!response.code && response.data.login) {
                        console.log(response.data);
                        alert('提交成功');
                        $('#sourceModal').modal('hide');
                        location.reload();
                        location.href = './?cid=' + contest.get_url_param('cid') + '#status';
                    } else if (!response.code) {
                        alert('请先登录');
                        $('#sourceModal').modal('hide');
                        $('#login').modal('show');
                    } else {
                        alert(response.msg);
                    }
                },
                error: function() {
                    console.log("error");
                }
            });
        });
    },

    pagination: function(action, num, page=1, size=10) {
        if (num <= size) {
            return false;
        }
        //page为当前页
        var str = '<nav aria-label="Page navigation"><ul class="pagination">';
            if (page > 1) {
                var tmp = parseInt(page) - 1;
                str += '<li><a href="?page=' + tmp + action + '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
            }
            for (var i = 1; i <= Math.ceil(num / size); ++i) {
                if (i == page) {
                    str += '<li class="active"><a href="?page='+ i + action +'">'+ i +'</a></li>';
                } else {
                    str += '<li><a href="?page='+ i + action +'">' + i + '</a></li>';
                }
            }
            if (page * size < num) {
                var tmp = parseInt(page) + 1;
                str += '<li><a href="?page='+ tmp + action +'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>'
            }
            str += '</ul></nav>';
        $('#display').append(str);
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
    },

};

contest.init();
