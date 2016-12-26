<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <!-- Optional theme -->
        {{--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">--}}
        <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
        <link href="{{ asset('xerobase/filemanager/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
        <style>
            body{
                background:#333;
                color:#efefef;
                font-family: Ubuntu,sans-serif;
            }
            a,a:visited{
                color:#f04;
            }
            a:hover{
                text-decoration:none;
                color: #ff1850;
            }
            span{
                font-family: Ubuntu,sans-serif;
            }
            .container{
                margin-top:90px;
                margin-left: auto;
                margin-right: auto;
                /*background: #fff;*/
                width: 960px;
                min-height:500px;
            }
            .modal-title{
                color:#666;
            }
            .fa-folder-o,.fa-folder{
                color:#fcefa1;
            }
            #directory{
                padding:20px 0 0 30px;
            }
            #directory table{
                margin-left: 15px;
                border-collapse:separate;
                border-spacing:10px;
            }
            #loadingIcon{
                font-size: 20px;
            }
            .dirTips{
                color:#777;
            }
            .fileTitle{
                cursor:pointer;
                padding-right:60px;
            }
            #uploadButton{
                display:none;
            }
            .fileMenu{
                margin-right:20px;
            }
            .fileMenu a,a:visited {
                cursor:pointer;
                color:#f04;
            }
            .fileMenu a:hover {
                color: #e3013b;
            }
            .nameForm{
                width:60%;
                position: relative;
                top:-24px;
                left:20px;
                margin-bottom:-20px;
                display: none;
            }
            .subDir::before{
                padding:0 5px;
                color: #ccc;
                content:"/\00a0";
            }
            .goHome,#dirPath a{
                color:#fff;
                text-decoration:none;
                cursor:pointer;
            }
            .goHome:hover,#dirPath a:hover{
                color:#ccc;
            }
            #errorMessage{
                color:#333;
            }
            #footer {
                position: absolute;
                right: 0;
                bottom: 0;
                left: 0;
                padding: 1rem;
                text-align: center;
            }
        </style>
        <title>XEROBASE File Manager - 1.0</title>
    </head>
    <body>
        <div class="col-lg-12">

            <!--Error Modal -->
            <div id="errorModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">&nbsp;&nbsp;File upload error!</h4>
                        </div>
                        <div class="modal-body">
                            <p id="errorMessage"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>

            <!--Download link Modal -->
            <div id="downloadModal" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">&nbsp;&nbsp;Download link</h4>
                        </div>
                        <div class="modal-body">
                            <input type="text" id="downloadLink" class="form-control" placeholder="Download Link">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>


            <!--Make Directory Modal -->
            <div id="createModal" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">&nbsp;&nbsp;New directory</h4>
                        </div>
                        <div class="modal-body">
                            <input type="text" class="form-control" id="newDirectoryName" placeholder="directory name">
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="makeDirectory" class="btn btn-success" data-dismiss="modal">Create directory</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>

            <div class="container">
                <input type="hidden" id="token" value="{{ csrf_token() }}">
                <input type="hidden" id="path" value="">
                <input type="hidden" id="fileViewURL" value="{{ url('filemanager/file/') }}">
                <h3>Welcome to XEROBASE file manager</h3>
                <div id="directory">
                    <div>
                        <button class="btn btn-default btn-lg" data-toggle="modal" data-target="#createModal"><i class="fa fa-folder-open-o fa-lg"></i>&nbsp;<i class="fa fa-plus fa-lg"></i></button>
                        <label class="btn btn-danger btn-lg"><i class="fa fa-upload fa-lg"></i><input type="file" id="uploadButton">&nbsp;<span>Upload file</span></label>
                        <span id="loadingIcon"><i class="fa fa-cog fa-spin fa-lg fa-fw "></i></span>
                    </div>
                    <br>
                    <a class="goHome"><i class="fa fa-folder-open" aria-hidden="true"></i>File Manager</a>
                    <span id="dirPath"></span>
                    <br>
                    <table>
                        <tbody id="filesList">
                            @if(!empty($files))
                                @foreach($files as $file)
                                    @if($file->type == 'dir')
                                        <tr class="file-{{ $file->id }}" data-number="{{ $file->id }}"><td class="fileTitle"><i class="fa fa-folder-o fa-lg">&nbsp;<span class="namePrint openDir" data-path_directory="{{ $file->path }}" data-real_name="{{ $file->real_name }}" id="namePrint-{{ $file->id }}">{{ $file->name }}</span></i><div class="nameForm" id="nameForm-{{ $file->id }}" data-file_id="{{ $file->id }}"><input type="text" id="renameField-{{ $file->id }}" class="form-control" value="{{ $file->name }}"></div></td><td><div class="fileMenu fileMenu-{{ $file->id }}"><a class="renameButton" data-file_id="{{ $file->id }}"><i class="fa fa-edit"  aria-hidden="true"></i></a>&nbsp;<a class="removeFile" data-fileid="{{ $file->id }}"><i class="fa fa-trash" aria-hidden="true"></i></a></div></td><td class="dirTips">dirs : {{ $file->sub_folders }}</td><td class="dirTips">files : {{ $file->sub_files }}</td></tr>
                                    @else
                                        @if($file->extension == 'pdf')
                                            <tr class="file-{{ $file->id }}" data-number="{{ $file->id }}"><td class="fileTitle"><i class="fa fa-file-pdf-o fa-lg">&nbsp;<span class="namePrint openFile" data-real_name="{{ $file->real_name }}" id="namePrint-{{ $file->id }}">{{ $file->name }}</span></i><div class="nameForm" id="nameForm-{{ $file->id }}" data-file_id="{{ $file->id }}"><input type="text" id="renameField-{{ $file->id }}" class="form-control" value="{{ $file->name }}"></div></td><td><div class="fileMenu fileMenu-{{ $file->id }}"><a class="downloadLink" data-real_name="{{ $file->real_name }}"><i class="fa fa-link"></i></a>&nbsp;<a><i class="fa fa-eye openFile" data-real_name="{{ $file->real_name }}" aria-hidden="true"></i></a><a class="renameButton" data-file_id="{{ $file->id }}"><i class="fa fa-edit"  aria-hidden="true"></i></a>&nbsp;<a class="removeFile" data-fileid="{{ $file->id }}"><i class="fa fa-trash" aria-hidden="true"></i></a></div></td><td class="dirTips">size : {{ $file->size }}KB</td></tr>
                                        @else
                                            <tr class="file-{{ $file->id }}" data-number="{{ $file->id }}"><td class="fileTitle"><i class="fa fa-file-image-o fa-lg">&nbsp;<span class="namePrint openFile" data-real_name="{{ $file->real_name }}" id="namePrint-{{ $file->id }}">{{ $file->name }}</span></i><div class="nameForm" id="nameForm-{{ $file->id }}" data-file_id="{{ $file->id }}"><input type="text" id="renameField-{{ $file->id }}" class="form-control" value="{{ $file->name }}"></div></td><td><div class="fileMenu fileMenu-{{ $file->id }}"><a class="downloadLink" data-real_name="{{ $file->real_name }}"><i class="fa fa-link"></i></a>&nbsp;<a><i class="fa fa-eye openFile" data-real_name="{{ $file->real_name }}" aria-hidden="true"></i></a><a class="renameButton" data-file_id="{{ $file->id }}"><i class="fa fa-edit"  aria-hidden="true"></i></a>&nbsp;<a class="removeFile" data-fileid="{{ $file->id }}"><i class="fa fa-trash" aria-hidden="true"></i></a></div></td><td class="dirTips">size : {{ $file->size }}KB</td></tr>
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="footer">
                <p style="text-align: center;">Design and develop by <a href="http://xerobase.pro">XEROBASE</a> @2016.</p>
            </div>
        </div>

        <script src="{{ asset('xerobase/filemanager/js/jquery-1.11.3.min.js') }}"></script>

        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

        <!-- Our customized jquery codes -->
        <script>
            /**
             *  Global area
             */

            var token = $('#token').val();
            var path = $('#path').val();
            var loading = false;

            // Global function to list directory
            var listDir = function(obj){
                var el = '';
                if(obj.type == 'dir'){
                    el  = "<tr class='file-" + obj.id + "' data-number='" + obj.id + "'><td class='fileTitle'><i class='fa fa-folder-o fa-lg'>&nbsp;<span class='namePrint openDir' data-path_directory='" + obj.path + "' data-real_name='" + obj.real_name + "' id='namePrint-" + obj.id + "'>" + obj.name + "</span></i><div class='nameForm' id='nameForm-" + obj.id + "' data-file_id='" + obj.id + "'><input type='text' id='renameField-" + obj.id + "' class='form-control' value='" + obj.name + "'></div></td><td><div class='fileMenu fileMenu-" + obj.id + "'><a class='renameButton' data-file_id='" + obj.id + "'><i class='fa fa-edit'  aria-hidden='true'></i></a>&nbsp;<a class='removeFile' data-fileid='" + obj.id + "'><i class='fa fa-trash' aria-hidden='true'></i></a></div></td><td class='dirTips'>dirs : " + obj.sub_folders + "</td><td class='dirTips'>files : " + obj.sub_files + "</td></tr>";
                }else{
                    if(obj.extension == 'pdf'){
                        el = "<tr class='file-" + obj.id + "' data-number='" + obj.id + "'><td class='fileTitle'><i class='fa fa-file-pdf-o fa-lg'>&nbsp;<span class='namePrint openFile' data-real_name='" + obj.real_name + "' id='namePrint-" + obj.id + "'>" + obj.name + "</span></i><div class='nameForm' id='nameForm-" + obj.id + "' data-file_id='" + obj.id + "'><input type='text' id='renameField-" + obj.id + "' class='form-control' value='" + obj.name + "'></div></td><td><div class='fileMenu fileMenu-" + obj.id + "'><a class='downloadLink' data-real_name='" + obj.real_name + "'><i class='fa fa-link'></i></a>&nbsp;<a><i class='fa fa-eye openFile' data-real_name='" + obj.real_name + "' aria-hidden='true'></i></a><a class='renameButton' data-file_id='" + obj.id + "'><i class='fa fa-edit'  aria-hidden='true'></i></a>&nbsp;<a class='removeFile' data-fileid='" + obj.id + "'><i class='fa fa-trash' aria-hidden='true'></i></a></div></td><td class='dirTips'>size : " + obj.size + " KB</td></tr>";
                    } else{
                        el = "<tr class='file-" + obj.id + "' data-number='" + obj.id + "'><td class='fileTitle'><i class='fa fa-file-image-o fa-lg'>&nbsp;<span class='namePrint openFile' data-real_name='" + obj.real_name + "' id='namePrint-" + obj.id + "'>" + obj.name + "</span></i><div class='nameForm' id='nameForm-" + obj.id + "' data-file_id='" + obj.id + "'><input type='text' id='renameField-" + obj.id + "' class='form-control' value='" + obj.name + "'></div></td><td><div class='fileMenu fileMenu-" + obj.id + "'><a class='downloadLink' data-real_name='" + obj.real_name + "'><i class='fa fa-link'></i></a>&nbsp;<a><i class='fa fa-eye openFile' data-real_name='" + obj.real_name + "' aria-hidden='true'></i></a><a class='renameButton' data-file_id='" + obj.id + "'><i class='fa fa-edit'  aria-hidden='true'></i></a>&nbsp;<a class='removeFile' data-fileid='" + obj.id + "'><i class='fa fa-trash' aria-hidden='true'></i></a></div></td><td class='dirTips'>size : " + obj.size + " KB</td></tr>";
                    }
                }
                return el;
            };

            $(document).ready(function(){
                $('#loadingIcon').hide();
                $('.nameForm').hide();
                $('#errorMessage').html('');
                $('#checkAll').prop('checked',false);

                /*
                 *      Remove directories or files
                 */
                $(document).on('click','.removeFile',function(){
                    $('#loadingIcon').show();
                    var fileID = $(this).data('fileid');
                    if(loading == false) {
                        loading = true;
                        $.post(
                                'removefile',
                                {
                                    _token: token,
                                    fileID: fileID
                                }, function (done) {
                                    if (done == 'success') {
                                        $('.file-' + fileID).remove();
                                    }
                                    $('#loadingIcon').hide();
                                    loading = false;
                                }
                        );
                    }
                });

                /**
                 *      Open a directory
                 */
                $(document).on('click','.openDir',function(){
                    $('#loadingIcon').show();
                    if(loading == false) { // Prevent opening many duplication of a path!
                        loading = true;
                        var real_name = $(this).data('real_name');
                        var path_directory = $(this).data('path_directory');
                        path = path == '' ? real_name : path + '/' + real_name;
                        // Handle page top directory path
                        var dir_path = "<a class='subDir' data-path_directory='" + path_directory + "' data-real_name='" + real_name + "'><i class='fa fa-folder-open'  aria-hidden='true'><span>" + $(this).text() + "</span></i></a>";
                        $.post(
                                'opendirectory',
                                {
                                    _token: token,
                                    path: path
                                }, function (done) {
                                    if (done != '' || done != 'failure' && typeof(done) == 'object') {
                                        $('#filesList').html('');
                                        $.each(done, function (i, obj) {
                                            el = listDir(obj);
                                            $('#filesList').append(el);
                                            $('.nameForm').hide();
                                        });
                                        $('#dirPath').append(dir_path);
                                    }else{
                                        $('#errorMessage').html('').html('Something went wrong please try again!');
                                        $('#errorModal').modal('show');
                                    }
                                    loading = false;
                                    $('#loadingIcon').hide();
                                }
                        );
                    }
                });

                /**
                 *      Create new directory
                 */
                $('#makeDirectory').on('click',function(){
                    $('#loadingIcon').show();
                    var name = $('#newDirectoryName').val();
                    $.post(
                            'makenewdirectory',
                            {
                                _token:token,
                                name:name,
                                path:path
                            },function(done){
                                if(typeof(done) == 'object'){
                                    var el  = "<tr class='file-" + done.id + "' data-number='" + done.id + "'><td class='fileTitle'><i class='fa fa-folder-o fa-lg'>&nbsp;<span class='namePrint openDir' data-path_directory='" + done.path + "' data-real_name='" + done.real_name + "' id='namePrint-" + done.id + "'>" + done.name + "</span></i><div class='nameForm' id='nameForm-" + done.id + "' data-file_id='" + done.id + "'><input type='text' id='renameField-" + done.id + "' class='form-control' value='" + done.name + "'></div></td><td><div class='fileMenu fileMenu-" + done.id + "'><a class='renameButton' data-file_id='" + done.id + "'><i class='fa fa-edit'  aria-hidden='true'></i></a>&nbsp;<a class='removeFile' data-fileid='" + done.id + "'><i class='fa fa-trash' aria-hidden='true'></i></a></div></td><td class='dirTips'>dirs : " + 0 + "</td><td class='dirTips'>files : " + 0 + "</td></tr>";
                                    $('#filesList').prepend(el);
                                }
                                $('#newDirectoryName').val('');
                                $('#loadingIcon').hide();
                            }
                    )
                });


                /**
                 *      Clicking on directory path on top of site
                 */
                $(document).on('click','.subDir',function(){
                    $('#loadingIcon').show();
                    $(this).nextAll('.subDir').remove();
                    var real_name = $(this).data('real_name');
                    path = $(this).data('path_directory');
                    path = path == '' ? real_name : path + '/' + real_name;
                    $.post(
                            'opendirectory',
                            {
                                _token:token,
                                path:path
                            },function(done){
                                if(done != '' || done!='failure' && typeof(done) == 'object'){
                                    $('#filesList').html('');
                                    $.each(done,function(i,obj){
                                        el = listDir(obj);
                                        $('#filesList').append(el);

                                    });
                                }
                                $('#loadingIcon').hide();
                            }
                    );
                });

                /**
                 *      Go to root directory
                 */
                $('.goHome').on('click',function(){
                    $('#loadingIcon').show();
                    path = '';
                    $('#dirPath').html('');
                    $.post(
                            'opendirectory',
                            {
                                _token:token,
                                path:path
                            },function(done){
                                if(done != '' || done!='failure' && typeof(done) == 'object'){
                                    $('#filesList').html('');
                                    $.each(done,function(i,obj){
                                        el = listDir(obj);
                                        $('#filesList').append(el);
                                    });
                                }
                                $('#loadingIcon').hide();
                            }
                    );
                });

                /**
                 *      Open parent directories from side bar
                 */
                $('.openParentDir').on('click',function(){
                    $('#loadingIcon').show();
                    var real_name = $(this).data('real_name');
                    $('#dirPath').html('');
                    path = '';
                    path = path + real_name;
                    // Handle page top directory path
                    var dir_path = "<a class='subDir' data-path_directory='" + real_name + "' data-real_name='" + real_name + "'><i class='fa fa-folder-open'  aria-hidden='true'><span> " + $(this).text() + "</span></i></a>";
                    $('#dirPath').append(dir_path);
                    $.post(
                            'opendirectory',
                            {
                                _token:token,
                                path:path
                            },function(done){
                                if(done != '' || done!='failure' && typeof(done) == 'object'){
                                    $('#filesList').html('');
                                    $.each(done,function(i,obj){
                                        el = listDir(obj);
                                        $('#filesList').append(el);

                                    });
                                }
                                $('#loadingIcon').hide();
                            }
                    );
                });

                /**
                 *      Upload a file
                 */
                $('#uploadButton').on('change', function() {
                    $('#loadingIcon').show();
                    var file_data = $('#uploadButton').prop('files')[0];
                    var form_data = new FormData();
                    form_data.append('_token',token);
                    form_data.append('file', file_data);
                    form_data.append('path',path);
                    $.ajax({
                        url: 'uploadfile', // point to server-side PHP script
                        dataType: 'text',  // what to expect back from the PHP script, if anything
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: form_data,
                        type: 'post',
                        success: function(data){
                            response = $.parseJSON(data);
                            if(response.id) {
                                if(response.extension == 'pdf'){
                                    var el = '';
                                    el = "<tr class='file-" + response.id + "' data-number='" + response.id + "'><td class='fileTitle'><i class='fa fa-file-pdf-o fa-lg'>&nbsp;<span class='namePrint openFile' data-real_name='" + response.real_name + "' id='namePrint-" + response.id + "'>" + response.name + "</span></i><div class='nameForm' id='nameForm-" + response.id + "' data-file_id='" + response.id + "'><input type='text' id='renameField-" + response.id + "' class='form-control' value='" + response.name + "'></div></td><td><div class='fileMenu fileMenu-" + response.id + "'><a class='downloadLink' data-real_name='" + response.real_name + "'><i class='fa fa-link'></i></a>&nbsp;<a><i class='fa fa-eye openFile' data-real_name='" + response.real_name + "' aria-hidden='true'></i></a><a class='renameButton' data-file_id='" + response.id + "'><i class='fa fa-edit'  aria-hidden='true'></i></a>&nbsp;<a class='removeFile' data-fileid='" + response.id + "'><i class='fa fa-trash' aria-hidden='true'></i></a></div></td><td class='dirTips'>size : " + response.size + " KB</td></tr>";
                                } else{
                                    el = "<tr class='file-" + response.id + "' data-number='" + response.id + "'><td class='fileTitle'><i class='fa fa-file-image-o fa-lg'>&nbsp;<span class='namePrint openFile' data-real_name='" + response.real_name + "' id='namePrint-" + response.id + "'>" + response.name + "</span></i><div class='nameForm' id='nameForm-" + response.id + "' data-file_id='" + response.id + "'><input type='text' id='renameField-" + response.id + "' class='form-control' value='" + response.name + "'></div></td><td><div class='fileMenu fileMenu-" + response.id + "'><a class='downloadLink' data-real_name='" + response.real_name + "'><i class='fa fa-link'></i></a>&nbsp;<a><i class='fa fa-eye openFile' data-real_name='" + response.real_name + "' aria-hidden='true'></i></a><a class='renameButton' data-file_id='" + response.id + "'><i class='fa fa-edit'  aria-hidden='true'></i></a>&nbsp;<a class='removeFile' data-fileid='" + response.id + "'><i class='fa fa-trash' aria-hidden='true'></i></a></div></td><td class='dirTips'>size : " + response.size + " KB</td></tr>";
                                }
                                $('#filesList').append(el);
                            }else{
                                if(response.error){
                                    $('#errorMessage').html('').html(response.error);
                                }else{
                                    $('#errorMessage').html('').html('There is a problem, try again later!');
                                }
                                $('#errorModal').modal('show');
                            }
                            $('#loadingIcon').hide();
                        },failure:function(response){
                            $('#loadingIcon').hide();
                        }
                    });
                    $('#uploadButton').val('');
                });


                /**
                 *      Renaming files and directories
                 */
                $(document).on('click','.renameButton',function(){
                    //First let's reset all names
                    var id = $(this).data('file_id');
                    $('#namePrint-' + id).fadeOut();
                    $('#nameForm-' + id).fadeIn();
                });

                // Focus out , reverse the operation
                $(document).on('focusout','.nameForm',function(){
                    $('#loadingIcon').show();
                    var id = $(this).data('file_id');
                    $('#namePrint-' + id).show();
                    $('#nameForm-' + id).hide();
                    var newName = $('#renameField-' + id).val();
                    $.post(
                            'renameFiles',
                            {
                                _token:token,
                                id:id,
                                newName:newName
                            },function(done){
                                if(done == 'success'){
                                    $('#namePrint-' + id).text(newName);
                                }
                                $('#loadingIcon').hide();
                            }
                    );
                });

                /**
                 *      Create download links
                 */
                $(document).on('click','.openFile',function(){
                    var real_name = $(this).data('real_name');
                    location.href = $('#fileViewURL').val() + '/' + real_name;
                });

                $(document).on('click','.downloadLink',function(){
                    var real_name = $(this).data('real_name');
                    var link = $('#fileViewURL').val() + '/' + real_name;
                    $('#downloadLink').val('').val(link);
                    $('#downloadModal').modal('show');
                });

            });
        </script>
    </body>
</html>