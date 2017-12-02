var admin = {
    config: {
        'problem': {
            'add': ''
        }
    },
    init: function() {
        var action = location.href.split("#")[1];
        switch (action) {
            case 'problem':
                $.get('./problem_add.html', function(data) {
                    console.log(data);
                    $('#container').html(data);
                });
            case 'contest':
                break;
            case 'user':
                break;
            default:
                console.log('404');
        }
    },

}
admin.init();
