<html>
    <head>
        <title>Mobile API Debug Console</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="pragma" content="no-cache">
        <style type="text/css">
            input.text, textarea.textarea {
                width: 100%;
                padding: 3px 5px;
            }
            textarea,.textarea {
                height: 100px;
                width: 700px;
            }
            td {
                padding: 10px;
            }
            td.right {
                vertical-align: top;
                border-left: 1px solid #ccc;
            }
        </style>
        <script type="text/javascript" src="static/jscript/jquery-1.9.0.min.js"></script>
		<script type="text/javascript" src="static/jscript/jquery.json-2.4.js"></script>
		<script type="text/javascript">
            $(document).ready(function() {
                $('#gform').bind('submit', function(evt) {
                    evt.preventDefault();
                    doSubmit();
                });
            });
            
            function progressHandlingFunction(e){
                if(e.lengthComputable){
                    $('progress').attr({value:e.loaded,max:e.total});
                }
            }
            function completeHandler(data) {
                $('#response_object').html(data);
            }
            
            function doSubmit(form) {
            	try{
	                var aData = {};
                    var aDataForm = $('#gform').serializeArray();
                    if (aDataForm) {
                        aData = aDataForm;
                    }

                    var sToken = $('#sToken').val().trim();
	                $('#send_data').html($.toJSON(aDataForm));
	                $('#response_object').html('');
                    
                    var formData = new FormData($('#gform')[0]);
                    
                    $.ajax({
                        url: 'api.php/event/photoprofile',  //server script to process data
                        headers: {
                            "token": sToken
                        },
                        type: 'POST',
                        xhr: function() {  // custom xhr
                            var myXhr = $.ajaxSettings.xhr();
                            if (myXhr.upload) { // check if upload property exists
                                myXhr.upload.addEventListener('progress', progressHandlingFunction, false); // for handling the progress of the upload
                            }
                            return myXhr;
                        },
                        success: completeHandler,
                        //Ajax events
                        // Form data
                        data: formData,
                        //Options to tell JQuery not to process data or worry about content-type
                        cache: false,
                        contentType: false,
                        processData: false
                    });
            	}catch(e){
                    console.log(e);
            		//alert(e.getMessage());
            	}
               	return false;
            }
            
		</script>
    </head>
    <body>
        <div style="width: 1100px;text-overflow: scroll;"><table>
                <tr>
                    <td width="500">
                        <form method="post" action="http://localhost/projects/debug/upload/module/mfox/api.php" id="gform" enctype="multipart/form-data">
                            <div>
                                <div>
                                    Token:
                                </div>
                                <div>
                                    <input class="text" maxlength="200" type="text" name="token" value="" id="sToken"/>
                                </div>
                            </div>
                            
                            <div>
                                <div>
                                    iEventId:
                                </div>
                                <div>
                                    <input class="text" maxlength="200" type="text" name="iEventId" value=""/>
                                </div>
                            </div>
                            
                            <div>
                                <div>
                                    File upload:
                                </div>
                                <div>
                                    <input type="file" size="30" name="image">
                                </div>
                            </div>
                            
                            <div>
                                <button type="submit" name="_submit">
                                    Submit
                                </button>
                                <button type="reset" name="_reset">
                                    Reset
                                </button>
                            </div>
                        </form></td>
                    <td class="right">
                        <div>
                            Send Data:
                        </div>
                        <div>
                            <textarea id="send_data"></textarea>
                        </div>
                        <div>
                            Response Data
                        </div><textarea id="response_object"></textarea></td>
                </tr>
            </table></div>

    </body>
</html>
