$(document).ready(function(){
    $('.task-done').change(function () {
        var id = $(this).data('id');
        var isChecked = $(this).prop('checked') ? 1 : 0;
        var data = {
            'Task': {
                'is_done': isChecked
            }
        };
        $.ajax({
            url: '/api/tasks/task/' + id + '/complete',
            type: 'put',
            data: JSON.stringify(data),
            contentType: 'application/json; charset=utf-8',
            success: function (res) {},
            error: function (res, code) {
                $(this).prop('checked', !isChecked);
            }
        });
    });

    $('.task-edit').click(function(){
        var id = $(this).data('id');
        var content = $('.task-content[data-id="' + id + '"]').text();

        $('#save-task').data('id', id);
        $('#edited-text').val(content);
        $('#myModal').modal();
        return;
    });

    $('#save-task').click(function(){
        var id = $(this).data('id');
        var content = $('#edited-text').val();

        var data = {
            'Task': {
                'content': content
            }
        }

        $.ajax({
            url: '/api/tasks/task/' + id,
            type: 'put',
            data: JSON.stringify(data),
            contentType: 'application/json; charset=utf-8',
            success: function (res) {
                $('.task-content[data-id="' + id + '"]').text(content);
                $('#myModal').modal('hide');
            },
            error: function (res, code) {
                // TODO: error check
            }
        });
    });
});