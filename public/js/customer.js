$(document).ready(
    function () {

        var timer;
        var doAutoRefresh = false;

        __init();

        function __init() {
            $('#servers-add .btn-info').click(function () {
                addServer($('#host').val(), $('#port').val());
                $("#host,#port").val('');
                $('#servers-add').modal('hide');
                window.location.href = window.location.href;
                return false;
            });
            $('#addJob').on('click', function () {
                $('#modalAddJob').modal('toggle');
                return false;
            });
            $('#filter input[type=checkbox]').click(function () {
                $('table')
                    .find('[name=' + $(this).attr('name') + ']')
                    .toggle($(this).is(':checked'));
                var names = [];
                $('#filter input:checked').each(function () {
                    names.push($(this).attr('name'));
                });
                $.cookie($('#filter').data('cookie'), names, {expires:365});
                $('.row-full').attr('colspan', names.length);
            });

            $('#tubeSave').on('click', function () {
                var result = addNewJob();

                if (result == 'empty') {
                    $('#tubeSaveAlert').fadeIn('fast');
                } else {
                    $('#modalAddJob').modal('toggle');
                }

                return false;
            });

            $('#autoRefresh').on('click', function () {
                if (!$('#autoRefresh').hasClass('btn-success')) {
                    reloader();
                    $('#autoRefresh').toggleClass('btn-success');
                    $('#autoRefresh i').toggleClass('icon-white');
                } else {
                    clearTimeout(timer);
                    doAutoRefresh = false;
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
        }

        function addServer(host, port) {
            if (host) {
                myCoockie = $.cookie("beansServers");
                if (myCoockie == null) {
                    server = host + ":" + port;
                } else {
                    server = myCoockie + ";" + host + ":" + port;
                }
                $.cookie("beansServers", server, {
                    expires:365
                });
            } else {
                alert("Some fields empty..");
            }
        }

        function addNewJob() {

            if (!$('#tubeName').val() || !$('#tubeData').val()) {
                return 'empty';
            }

            var params = {
                'tubeName':$('#tubeName').val(),
                'tubeData':$('#tubeData').val(),
                'tubePriority':$('#tubePriority').val(),
                'tubeDelay':$('#tubeDelay').val(),
                'tubeTtr':$('#tubeTtr').val()
            }

            $.ajax({
                'url':url + '&action=addjob',
                'data':params,
                'success':function (data) {
                    var result = data.result;
                    cleanFormNewJob();
                    location.reload();
                },
                'type':'POST',
                'dataType':'json',
                'error':function () {
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

            for (var i = 0; i < strLen; i++) {
                char = str.substring(i, i + 1);
                nextChar = str.substring(i + 1, i + 2);
                if ((char == '}' || char == ']') && nextChar != newLine) {

                    retval = retval + newLine;
                    pos = pos - 1;
                    for (var j = 0; j < pos; j++) {
                        retval = retval + indentStr;
                    }
                }
                retval = retval + char;

                if ((char == '{' || char == '[' || char == ',') && nextChar != newLine) {
                    retval = retval + newLine;
                    if (char == '{' || char == '[') {
                        pos = pos + 1;
                    }

                    for (var k = 0; k < pos; k++) {
                        retval = retval + indentStr;
                    }
                }
            }

            return retval;

        }

        function reloader() {
            var params = {
                'action':'reloader',
                'secondary':new Date().getTime()
            }
            var res;

            doAutoRefresh = true;
            $.ajax({
                'url':url,
                'data':params,
                'success':function (data) {
                    if (doAutoRefresh) {
                        // wrapping all of this to prevent last update
                        // after you turn it off
                        var html = $('#idAllTubes').html();
                        $('#idAllTubes').html(data);
                        $('#idAllTubesCopy').html(html);
                        updateTable();
                        timer = setTimeout(reloader, 500);
                    }
                },
                'type':'GET',
                'dataType':'html',
                'error':function () {
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
                        'background-color':'#afa'
                    }).animate({
                            'background-color':color
                        }, 500);
                }
            }
        }
    }
);
