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

			pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }
			.string { color: green; }
			.number { color: darkorange; }
			.boolean { color: blue; }
			.null { color: magenta; }
			.key { color: red; }			

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

			function output(inp) {
			    document.getElementById("code").appendChild(document.createElement('pre')).innerHTML = inp;
			}

			function syntaxHighlight(json) {
			    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
			    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
			        var cls = 'number';
			        if (/^"/.test(match)) {
			            if (/:$/.test(match)) {
			                cls = 'key';
			            } else {
			                cls = 'string';
			            }
			        } else if (/true|false/.test(match)) {
			            cls = 'boolean';
			        } else if (/null/.test(match)) {
			            cls = 'null';
			        }
			        return '<span class="' + cls + '">' + match + '</span>';
			    });
			}

            function doSubmit(form) {
            	try{
	            	var sService = $('#sService').val().trim('/');

	            	if(!sService){
	            		alert('request uri is required');
	            		$('#sService').focus();
	            		return ;
	            	}

	                var sData = $('#sData').val().trim();
	                var sToken = $('#sToken').val().trim();
	                var aData = {};
	                if (sData) {
	                    aData = $.evalJSON(sData);
	                }

					if(sToken)
					{
						aData['token'] = sToken;
					}

	                $('#send_data').html($.toJSON(aData));
	                $('#response_object').html('');

	                $.post('api.php/' + sService, aData, function(text) {
	                    $('#response_text').html(text);
	                    var json = $.evalJSON(text);
	                    var json_text = $.toJSON(json);
	                    $('#response_object').html(json_text);

	                    $('#code').html('');
						var str = JSON.stringify(json, undefined, 4);
						// output(str);
						output(syntaxHighlight(str));	                    
	                }, "text");

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
					<textarea id="send_data"></textarea>
				</div>
				<div>
					Response Data
				</div><textarea id="response_object"></textarea></td>
			</tr>
		</table></div>

		<div id="code"></div>

	</body>
</html>
