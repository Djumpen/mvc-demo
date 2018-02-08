$(document).ready(function(){

    var uploader = new plupload.Uploader({
        runtimes : 'html5,flash,silverlight,html4',

        file_data_name: 'image',
        multi_selection: false,
        browse_button : 'pickfiles',
        container: document.getElementById('upload-container'),

        url : "/api/tasks/task/image",

        filters : {
            max_file_size : '10mb',
            mime_types: [
                {title : "Image", extensions : "jpg,gif,png"}
            ]
        },

        init: {
            PostInit: function() {
                document.getElementById('filelist').innerHTML = '';
                $('#cancelfile').hide();
            },

            FilesAdded: function(up, files) {
                document.getElementById('filelist').innerHTML = '';
                plupload.each(files, function(file) {
                    document.getElementById('filelist').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
                });
                uploader.start();
                $('#pickfiles,.moxie-shim').hide();
            },

            UploadProgress: function(up, file) {
                document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
            },

            FileUploaded: function(uploader, file, result) {
                $('#cancelfile').show();
                $('#pickfiles,.moxie-shim').hide();

                var response = JSON.parse(result.response);
                $('#image-path').val(response.image_path);
                $('#filelist').html('');
                $('#image-preview').attr('src', response.image_url).show();
            },

            Error: function(up, err) {
                document.getElementById('console').innerHTML += "\nError #" + err.code + ": " + err.message;
            }
        }
    });

    uploader.init();

    $('#cancelfile').click(function(){
        $('#filelist').html('');
        $('#pickfiles,.moxie-shim').show();
        $('#image-preview').hide();
        $('#image-path').val('');
        $(this).hide();
    });

    $('#task-form').submit(function(e){
        e.preventDefault();
        var _this = this;

        var data = {
            Task: objectifyForm($(this).serializeArray())
        };

        $.ajax({
            url: '/api/tasks/task',
            type: 'post',
            data: JSON.stringify(data),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (res) {
                $(_this).fadeOut('fast', function(){
                    $('#success-message').fadeIn();
                });
            },
            error: function (res, code) {
                $('#error-response').html(res.responseJSON.message);
            }
        });

        return false;
    });

    $('#preview').click(function(e){
        e.preventDefault();
        var data = {
            Task: objectifyForm($('#task-form').serializeArray())
        };

        $.ajax({
            url: '/api/tasks/task/preview',
            type: 'post',
            data: JSON.stringify(data),
            success: function (res) {
                $('#myModal .modal-body').html(res);
                $('#myModal').modal();
                $('#error-response').html('');
            },
            error: function (res, code) {
                $('#error-response').html(res.responseJSON.message);
            }
        });
    });

});

