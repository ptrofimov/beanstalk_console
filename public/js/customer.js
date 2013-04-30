$(document).ready(
		function() {

			var timer;
            var doAutoRefresh=false;

			__init();

			function __init() {
				$('#addServer').live('click', function() {
					$('#subnavServer').fadeToggle("fast");
					return false;
				});
				$('#saveServer').live('click', function() {
					addServer();
					$('#subnavServer').fadeToggle("fast");
					$("#port").val('')
					$("#server").val('')

					return false;
				});

				$('#addJob').live('click', function() {
					$('#modalAddJob').modal('toggle');
					return false;
				});

				$('#tubeSave').live('click', function() {
					var result = addNewJob();

					if (result == 'empty') {
						$('#tubeSaveAlert').fadeIn('fast');
					} else {
						$('#modalAddJob').modal('toggle');
					}

					return false;
				});

				$('#autoRefresh').live('click', function() {
					if (!$('#autoRefresh').hasClass('btn-success')) {
						reloader();
						$('#autoRefresh').toggleClass('btn-success');
						$('#autoRefresh i').toggleClass('icon-white');
					} else {
						clearTimeout(timer);
                        doAutoRefresh=false;
						$('#autoRefresh').toggleClass('btn-success');
						$('#autoRefresh i').toggleClass('icon-white');
					}

					return false;
				});

				if (contentType == 'json') {
					var cn = $('pre code').html();
					var jn = formatJson(cn);
					$('pre code').html(jn);
				}

				// Выстраиваем листинг серверов из куки
				readServersFromCookies();
			}

			function addServer() {
				server = $("#server").val();
				port = $("#port").val();

				if (port && server) {
					myCoockie = $.cookie("beansServers");
					if (myCoockie == null) {
						server = server + ":" + port;
					} else {
						server = myCoockie + ";" + server + ":" + port;
					}
					$.cookie("beansServers", server, {
						expires : 365
					});

					readServersFromCookies();
				} else {
					alert("Some fields empty..");
				}
			}

			function readServersFromCookies() {
				$('.rawServer').empty();
				if ($.cookie("beansServers")) {
					myCoockie = $.cookie("beansServers").split(';');
					if (myCoockie.length > 0) {
						$('<li class="divider"></li>').appendTo('#listServers');

						for ( var i = 0; i < myCoockie.length; i++) {
							$(
									'<li class="rawServer"><a href="./?server=' + myCoockie[i] + '">' + myCoockie[i]
											+ '</a></li>').appendTo('#listServers');
						}
					}
				}
			}

			function addNewJob() {

				if (!$('#tubeName').val() || !$('#tubeData').val()) {
					return 'empty';
				}

				var params = {
					'tubeName' : $('#tubeName').val(),
					'tubeData' : $('#tubeData').val(),
					'tubePriority' : $('#tubePriority').val(),
					'tubeDelay' : $('#tubeDelay').val(),
					'tubeTtr' : $('#tubeTtr').val()
				}

				$.ajax({
					'url' : url + '&action=addjob',
					'data' : params,
					'success' : function(data) {
						var result = data.result;
						cleanFormNewJob();
						location.reload();
					},
					'type' : 'POST',
					'dataType' : 'json',
					'error' : function() {
						console.log('error ajax...');
					}
				});
			}

			function cleanFormNewJob() {
				// $('#tubeName').val('');
				$('#tubeData').val('');
				$('#tubePriority').val('');
				$('#tubeDelay').val('');
				$('#tubeTtr').val('');
			}

			function formatJson(val) {

				var retval = '';
				var str = val;
				var pos = 0;
				var strLen = str.length;
				var indentStr = '    ';
				var newLine = "\n";
				var char = '';

				for ( var i = 0; i < strLen; i++) {
					char = str.substring(i, i + 1);
					nextChar = str.substring(i + 1, i + 2);
					if ((char == '}' || char == ']') && nextChar != newLine) {

						retval = retval + newLine;
						pos = pos - 1;
						for ( var j = 0; j < pos; j++) {
							retval = retval + indentStr;
						}
					}
					retval = retval + char;

					if ((char == '{' || char == '[' || char == ',') && nextChar != newLine) {
						retval = retval + newLine;
						if (char == '{' || char == '[') {
							pos = pos + 1;
						}

						for ( var k = 0; k < pos; k++) {
							retval = retval + indentStr;
						}
					}
				}

				return retval;

			}

			function reloader() {
				var params = {
					'action' : 'reloader',
					'secondary' : new Date().getTime()
				}
				var res;

                doAutoRefresh=true;
				$.ajax({
					'url' : url,
					'data' : params,
					'success' : function(data) {
                        if (doAutoRefresh)
                        {
                            // wrapping all of this to prevent last update
                            // after you turn it off
                            var html = $('#idAllTubes').html();
                            $('#idAllTubes').html(data);
                            $('#idAllTubesCopy').html(html);
                            updateTable();
                            timer = setTimeout(reloader, 500);
                        }
					},
					'type' : 'GET',
					'dataType' : 'html',
					'error' : function() {
						console.log('error ajax...');
					}
				});
			}

			function updateTable() {
				var td1 = $('#idAllTubes table').find('td'), td2 = $('#idAllTubesCopy table').find('td');
				for (i = 0, il = td1.length; i < il; i++) {
					if (td1[i].innerText != td2[i].innerText) {
						var $td1 = $(td1[i]), color = $td1.css('background-color');
						$td1.css({
							'background-color' : '#afa'
						}).animate({
							'background-color' : color
						}, 500);
					}
				}
			}

		});