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
			textarea.textarea {
				height: 200px;
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
            function doSubmit(form) {
                try {
                    var sService = $('#sService').val().trim('/');

                    if (!sService) {
                        alert('request uri is required');
                        $('#sService').focus();
                        return;
                    }

                    var sData = $('#sData').val().trim();
                    var sToken = $('#sToken').val().trim();
                    var aData = {};
                    var headers = {};
                    if (sData) {
                        aData = $.evalJSON(sData);
                    }

                    if (sToken) {
                        headers.token = sToken;
                    }

                    $('#send_data').html($.toJSON(aData));
                    $('#response_text').html('loading ...');
                    $('#response_object').html('');

                    $.ajax('api.php/' + sService, {
                        async : true,
                        data : aData,
                        dataType: 'json',
                        headers : headers,
                        type : $('#sMethod').val(),
                        success : function(json) {
                            var json_text = $.toJSON(json);
                            $('#response_object').html(json_text);
                        }
                    });

                } catch(e) {
                    console.log(e);
                    //alert(e.getMessage());
                }
                return false;
            }
		</script>
	</head>
	<body>
		<table>
			<tr>
				<td width="500">
				<form method="post" onsubmit="return 0;" id="gform">
					<div>
						<div>
							Request URI:
						</div>
						<div>
							<input class="text" maxlength="200" type="text" name="sService" value="" id="sService"/>
						</div>
					</div>
					<div>
						<div>
							METHOD:
						</div>
						<div>
							<select id="sMethod">
								<option>GET</option>
								<option>POST</option>
								<option>PUT</option>
								<option>DELETE</option>
							</select>
						</div>
					</div>
					<div>
						<div>
							Token:
						</div>
						<div>
							<input class="text" maxlength="200" type="text" name="sToken" value="" id="sToken"/>
						</div>
					</div>
					<div>
						<div>
							JSON notation
						</div>
						<div>
							<textarea class="textarea" name="sData" id="sData"></textarea>
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
					<pre id="send_data"></pre>
				</div>
				<div>
					<pre id="response_text"></pre>
				</div>
				<div>
					Response Data
				</div><div id="response_object"></div></td>
			</tr>
		</table>

	</body>
</html>
