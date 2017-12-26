var admin_api_root = '../../api/index.php/admin/';
var admin = {
    config: {
        verify: admin_api_root + 'user/verify',
        quit: '../../api/index.php/user/quit',
        user_admin_list: admin_api_root + 'user/admin_list',
        user_role_list: admin_api_root + 'user/role_list',
        user_search_user: admin_api_root + 'user/search_user',
        user_admin_grant: admin_api_root + 'user/admin_grant',

        problem_add: admin_api_root + 'problem/add',
        upload_image: admin_api_root + 'problem/upload_image',
        problem_list: admin_api_root + 'problem/list',
        problem_delete: admin_api_root + 'problem/delete',

        contest_add: admin_api_root + 'contest/add',
        contest_list: admin_api_root + 'contest/list',
        contest_delete: admin_api_root + 'contest/delete',
        contest_update: admin_api_root + 'contest/update',
        contest_update_show: admin_api_root + 'contest/info'
    },
    load: function(action) {
        switch (action) {
            case 'problem-add':
                $.get('./problem_add.html', function(data) {
                    $('#container').html(data);

                    var editor = admin.upload_image();
                    admin.problem_add(editor);
                });
                break;
            case 'problem-list':
                var page = admin.get_url_param('page');
                if (page == undefined) {
                    page = 1;
                }
                admin.show_problem_list(page, 10);
                admin.problem_delete();
                break;
            case 'contest-add':
                $.get('./contest_add.html', function(data) {
                    $('#container').html(data);
                });
                admin.contest_add();
                break;
            case 'contest-list':
                var page = admin.get_url_param('page');
                if (page == undefined) {
                    page = 1;
                }
                admin.show_contest_list(page, 10);
                admin.contest_delete();
                admin.contest_update_show();
                break;
            case 'admin-list':
                admin.user_admin_list();
                break;
            case 'admin-grant':
                $.get('./admin_grant.html', function(data) {
                    $('#container').html(data);

                    admin.show_role_list();
                    admin.search_user();
                    admin.user_admin_grant();
                });
            default:
                console.log('404');
        }
    },
    init: function() {
        admin.check_admin();

        admin.quit();

        $('#panel-111 .list-group a').click(function() {
            var action = $(this).attr('href').split("#")[1];
            //history.pushState({ action: action }, NULL, "#" + action);
            admin.load(action);
        });
        var action = location.href.split("#")[1];
        admin.load(action);
    },
    check_admin: function() {
        $.ajax({
            url: admin.config.verify,
            type: 'GET',
            success: function(response) {
                if (response.code == 0 && (response.data.login == false || response.data.admin == false)) {
                    var countdown = 3;
                    var str = '你没有管理权限，<strong>' + countdown + '</strong>秒之后转到管理登录页';
                    document.write(str);
                    var clock = setInterval(function() {
                        countdown--;
                        $('strong').text(countdown);
                        if (countdown <= 0) {
                            clearInterval(clock);
                            location.href = './login.html';
                        }
                    }, 1000);
                } else if (response.code != 0) {
                    alert(response.code);
                }
                var data = response.data;
                $('#admin_name').text(data.username);
            },
            error: function() {
                console.log('Error');
            }
        })
    },

    quit: function() {
        $('#quit').click(function() {
            $.ajax({
                url: admin.config.quit,
                type: 'GET',
                success: function() {
                    alert('退出成功');
                    location.href = './login.html';
                },
                error: function() {
                    console.log('Error');
                }
            });
        });
    },
    problem_add: function(editor) {
        $(document.body).on('click', '#btn_problem_add', function(e) {
            var formdata = new FormData($('#problem_add')[0]);
            formdata.append('description', editor.txt.html());
            $.ajax({
                url: admin.config.problem_add,
                type: 'POST',
                cache: false,
                processData: false,
                contentType: false,
                //dataType:'application/json; charset:utf-8',
                data: formdata,
                success: function(response) {
                    console.log(response);
                    if (!response.code) {
                        alert('添加成功');
                    } else {
                        alert(response.msg);
                    }
                },
                error: function() {
                    console.log('Error');
                }
            });
        });
    },

    upload_image: function() {
        var E = window.wangEditor;
        var editor = new E('#description');

        editor.customConfig.uploadImgServer = admin.config.upload_image;

        editor.customConfig.uploadImgMaxSize = 360 * 1024;

        editor.customConfig.uploadImgMaxLength = 5;

        editor.customConfig.customAlert = function(info) {
            alert(info);
        }

        editor.customConfig.uploadImgHooks = {
            success: function() {
                console.log('success~');
            },
            error: function() {
                console.log('error~');
            },
            fail: function(a, b, c) {
                console.log('fail~');
                editor.customConfig.customAlert(c.msg);
            }
        }

        editor.create();

        return editor;
    },

    show_problem_list: function(page, size) {
        $.ajax({
            url: admin.config.problem_list + '/' + page + '/' + size,
            type: 'GET',
            success: function(response) {
                if (!response.code) {
                    var data = response.data;
                    var str = '<table id="problem_list" class="table table-hover table-condensed" style="background-color:rgba(240,245,250,.6);">';
                    str += '<caption>题目列表</caption>';
                    str += '<tr><th>题目编号</th><th>标题</th><th>来源</th><th>操作</th></tr>';

                    $.each(data.list, function(index, value) {

                        str += '<tr><td>' + value["problem_id"] + '</td>';
                        str += '<td>' + value['title'] + '&nbsp;<a href="../problem.html?pid=' + value["problem_id"] + '">(查看)</a></td>';
                        str += '<td>' + value['source'] + '</td>';
                        str += '<td><button class="btn btn-warning" data-link="'+value['problem_id']+'">修改</button>&nbsp;<button class="btn btn-danger" data-link="'+value['problem_id']+'">删除</button></td></tr>';

                    });
                    str += '</table>';

                    $('#container').html(str);
                    admin.pagination('#problem-list', data.num, page, size);

                } else {
                    alert(response.msg);
                }
            },
            error: function() {
                console.log('Error');
            }
        });
    },

    problem_delete: function() {
        $('#container').on('click', '#problem_list .btn-danger', function(e) {
            var problem_id = $(this).attr('data-link');
            $.ajax({
                url: admin.config.problem_delete + '/' + problem_id,
                type: 'GET',
                success: function(response) {
                    if (!response.code) {
                        alert('题目删除成功');
                        location.reload();
                    } else {
                        alert(response.msg);
                    }
                },
                error: function() {
                    console.log('Error');
                }
            });
        });
    },

    contest_add: function() {
        $(document.body).on('click', '#btn_contest_add', function(e) {
            var select = $('#contest_add input:radio:checked')
            admin.request(admin.config.contest_add, JSON.stringify({
                'title': $('#title').val(),
                'description': $('#description').val(),
                'start_time': $('#start_time').val(),
                'end_time': $('#end_time').val(),
                'private': select.val(),
                'password': $('#password').val(),
                'problem_list': $('#problem_list').val()
            }));
        });
    },

    show_contest_list: function(page, size) {
        $.ajax({
            url: admin.config.contest_list + '/' + page + '/' + size,
            type: 'GET',
            success: function(response) {
                if (!response.code) {
                    var data = response.data;
                    var str = '<table id="contest_list" class="table table-hover table-condensed" style="background-color:rgba(240,245,250,.6);">';
                    str += '<caption>比赛列表</caption>';
                    str += '<tr><th>比赛编号</th><th>标题</th><th>开始时间</th><th>结束时间</th><th>权限</th><th>操作</th></tr>';
                    console.log(response.data);

                    $.each(data.list, function(index, value) {

                        str += '<tr><td>' + value["contest_id"] + '</td>';
                        str += '<td>' + value['title'] + '&nbsp;<a href="../contest/?cid=' + value["contest_id"] + '">(查看)</a></td>';
                        str += '<td>' + value["start_time"] + '</td>';
                        str += '<td>' + value["end_time"] + '</td>';
                        if (value['private'] == 1) {
                            str += '<td><span style="color:red;">Private</span></td>';
                        } else {
                            str += '<td><span style="color:green;">Public</span></td>';
                        }
                        str += '<td><button class="btn btn-warning" data-link="'+value["contest_id"]+'">修改</button>&nbsp;<button class="btn btn-danger" data-link="'+value["contest_id"]+'">删除</button></td></tr>';

                    });
                    str += '</table>';

                    $('#container').html(str);
                    admin.pagination('#contest-list', data.num, page, size);

                } else {
                    alert(response.msg);
                }
            },
            error: function() {
                console.log('Error');
            }
        });
    },

    contest_delete: function() {
        $('#container').on('click', '#contest_list .btn-danger', function(e) {
            if(!confirm("确定要进行此操作吗？")) {
                return false;
            }
            var contest_id = $(this).attr('data-link');
            $.ajax({
                url: admin.config.contest_delete + '/' + contest_id,
                type: 'GET',
                success: function(response) {
                    if (!response.code) {
                        alert('比赛删除成功');
                        location.reload();
                    } else {
                        alert(response.msg);
                    }
                },
                error: function() {
                    console.log('Error');
                }
            });
        });
    },

    contest_update_show: function() {
        $('#container').on('click', '#contest_list .btn-warning', function(e) {
            //alert($(this).attr('data-link'));
            var contest_id = $(this).attr('data-link');
            $.get('./contest_add.html', function(data) {
                $('#container').html(data);

                $('#container #contest_add').attr('id', 'contest_update');

                var str = '<div class="form-group"><label for="contest_id">比赛编号</label><input type="text" disabled class="form-control" id="contest_id" placeholder="contest_id" name="contest_id" value="'+contest_id+'"></div>';
                $('#container #contest_update').prepend(str);

                $('#container h2').html('修改比赛<span class="label label-warning" style="float:right; font-size:.6em;">注意:刷新页面将不能保存</span>');
                $('#container h2').before('<a href="javascript:0;" onclick="location.reload();"><< 回到比赛列表</a>');
                $('#container #btn_contest_add').attr('id', 'btn_contest_update');
                $('#container #btn_contest_update').text('修改比赛信息');

                $.ajax({
                    url: admin.config.contest_update_show + '/' + contest_id,
                    type: 'GET',
                    success: function(response) {
                        if (!response.code) {
                            var data = response.data;
                            $('#title').val(data.title);
                            $('#start_time').val(data.start_time);
                            $('#end_time').val(data.end_time);
                            $('#description').val(data.description);
                            if (data.private == 1) {
                                $('#private1').attr('checked', 1);
                            } else {
                                $('#private0').attr('checked', 1);
                            }
                            $('#password').val(data.password);
                            $('#problem_list').val(data.problem_list);
                        } else {
                            alert(response.msg);
                            location.reload();
                        }
                    },
                    error: function() {
                        console.log('Error');
                    }
                });


                admin.contest_update();
            });

        });

    },

    contest_update: function() {
        $('#container').on('click', '#btn_contest_update', function(e) {
            var select = $('#contest_update input:radio:checked');
            admin.request(admin.config.contest_update, JSON.stringify({
                'contest_id': $('#contest_id').val(),
                'title': $('#title').val(),
                'description': $('#description').val(),
                'start_time': $('#start_time').val(),
                'end_time': $('#end_time').val(),
                'private': select.val(),
                'password': $('#password').val(),
                'problem_list': $('#problem_list').val()
            }),'修改');
        });
    },

    request: function(api_url, request_data, act_name='添加') {
        $.ajax({
            url: api_url,
            type: 'POST',
            data: request_data,
            success: function(response) {
                if (response.code == 0) {
                    if (response.data) {
                        alert(act_name+'成功');
                        location.reload();
                    } else {
                        alert(act_name+'失败');
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
        $('#container').append(str);
    },

    get_url_param: function(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg);  //匹配目标参数
        if (r != null) return unescape(r[2]); return null; //返回参数值
    },

    user_admin_list: function() {
        $.ajax({
            url: admin.config.user_admin_list,
            type: 'GET',
            success: function(response) {

                if (!response.code) {

                    var data = response.data;
                    var str = '<table id="contest_list" class="table table-hover table-condensed" style="background-color:rgba(240,245,250,.6);">';
                    str += '<caption>管理员列表</caption>';
                    str += '<tr><th>用户编号</th><th>用户名</th><th>角色</th></tr>';

                    $.each(data.list, function(index, value) {

                        str += '<tr><td>' + value["user_id"] + '</td>';
                        str += '<td>' + value["username"] + '</td><td>';

                        $.each(value["role"], function(subindex, subvalue) {
                            str += '<span class="label label-primary">'+subvalue+'</span>';
                        });

                        str += '</td></tr>';

                    });
                    str += '</table>';

                    $('#container').html(str);

                } else {
                    alert(response.msg);
                }
            },
            error: function() {
                console.log('Error');
            }
        });
    },

    user_admin_grant: function() {

        $('#container').on('click', 'input[type=checkbox]', function(e) {
            if ($(this).is(':checked')) {
                $(this).parent().css('background-color', '#36648B');
            } else {
                $(this).parent().css('background-color', '');
            }
        });

        $('#container #btn_admin_grant').click(function() {
            var role_list = [];
            $('#container .list-group input[type=checkbox]:checked').each(function() {
                role_list.push($(this).val());
            });

            var user_list = [];
            $('#container #search_result input[type=checkbox]:checked').each(function() {
                user_list.push($(this).val());
            });
            console.log(role_list);
            console.log(user_list);

            $.ajax({
                url: admin.config.user_admin_grant,
                type: 'POST',
                data: JSON.stringify({
                    'role_list': role_list,
                    'user_list': user_list
                }),
                success: function(response) {
                    if (!response.code) {
                        alert('赋予用户权限成功');
                        location.reload();
                    } else {
                        alert(response.msg);
                    }
                },
                error: function() {
                    console.log('Error');
                }
            });
        });


    },
    show_role_list: function() {
        $.ajax({
            url: admin.config.user_role_list,
            type: 'GET',
            success: function(response) {
                if (!response.code) {

                    var str = '';
                    $.each(response.data.list, function(index, value) {
                        str += '<li class="list-group-item">'
                        str += '<input type="checkbox" name="role" value="'+value['role_id']+'"><span style="float:right; font-size:1em;" class="label label-primary">'+value['name']+'</span>';
                        str += '</li>';
                    });

                    $('#container .list-group').append(str);

                } else {
                    alert(response.msg);
                }
            },
            error: function() {
                console.log('Error');
            }
        });
    },
    search_user: function() {
        $('#container').on('click', '#btn_search_user', function(e) {
            var username = $('#container #username').val();
            $.ajax({
                url: admin.config.user_search_user,
                type:'POST',
                data: JSON.stringify({
                    'username': username
                }),
                success: function(response) {
                    if (!response.code) {
                        $('#container .form-inline strong').text('查找成功');
                        var data = response.data;

                        var str = '<tr><th>选中赋予权限</th><th>用户id</th><th>用户名</th><th>角色</th></tr>';
                        str += '<tr><td><input type="checkbox" style="width:100%;" value="'+data.user_id+'"></td><td>'+data.user_id+'</td><td>'+data.username+'</td><td>';
                        $.each(data.role, function(index, value) {
                            str += '<span class="label label-primary">'+value+'</span>';
                        });
                        str += '</td></tr>';

                        $('#container #search_result').html(str);
                    } else {
                        //alert(response.msg);
                        $('#container .form-inline strong').text(response.msg);
                    }
                },
                error: function() {
                    console.log('Error');
                }
            });
        });
    }

}
admin.init();
