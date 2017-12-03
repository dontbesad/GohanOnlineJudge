var admin = {
    config: {
        'problem': {
            'add': ''
        }
    },
    load: function(action) {
        switch (action) {
            case 'problem_add':
                $.get('./problem_add.html', function(data) {
                    $('#container').html(data);
                    admin.problem_add();
                });
            case 'contest':
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
                url: '../../api/index.php/admin/problem/add',
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
    }

}
admin.init();
