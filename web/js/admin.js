var admin = {
    config: {
        problem_add: '../../api/index.php/admin/problem/add',
        contest_add: '../../api/index.php/admin/contest/add'
    },
    load: function(action) {
        switch (action) {
            case 'problem-add':
                $.get('./problem_add.html', function(data) {
                    $('#container').html(data);
                    admin.problem_add();
                });
                break;
            case 'contest-add':
                $.get('./contest_add.html', function(data) {
                    $('#container').html(data);
                    admin.contest_add();
                });
                break;
            case 'user':
                break;
            default:
                console.log('404');
        }
    },
    init: function() {
        $('#panel-111 .list-group a').click(function() {
            var action = $(this).attr('href').split("#")[1];
            //history.pushState({ action: action }, NULL, "#" + action);
            admin.load(action);
        });
        var action = location.href.split("#")[1];
        admin.load(action);
    },
    problem_add: function() {
        $('#btn_problem_add').click(function() {
            $.ajax({
                url: admin.config.problem_add,
                type: 'POST',
                cache: false,
                processData: false,
                contentType: false,
                //dataType:'application/json; charset:utf-8',
                data: new FormData($('#problem_add')[0]),
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
    contest_add: function() {
        $('#btn_contest_add').click(function() {
            var select = $('#contest_add input:radio:checked')
            admin.request(admin.config.contest_add, JSON.stringify({
                'title': $('#title').val(),
                'description': $('#description').val(),
                'start_time': $('#start_time').val(),
                'end_time': $('#end_time').val(),
                'private': select.val()
            }));
        });
    },

    request: function(api_url, request_data) {
        $.ajax({
            url: api_url,
            type: 'POST',
            data: request_data,
            success: function(response) {
                if (response.code == 0) {
                    if (response.data) {
                        alert('添加成功');
                    } else {
                        alert('添加失败');
                    }
                } else {
                    alert(response.msg);
                }
            },
            error: function() {
                console.log('Error');
            }
        });
    }

}
admin.init();
