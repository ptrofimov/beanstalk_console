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
                $('#filterServer input[type=checkbox]').click(function () {
                    $('table')
                            .find('[name=' + $(this).attr('name') + ']')
                            .toggle($(this).is(':checked'));
                    var names = [];
                    $('#filterServer input:checked').each(function () {
                        names.push($(this).attr('name'));
                    });
                    names = names.filter(function (itm, i, a) {
                        return i == a.indexOf(itm);
                    });
                    $.cookie($('#filterServer').data('cookie'), names, {expires: 365});
                    $('.row-full').attr('colspan', names.length);
                });
                $('#filter input[type=checkbox]').click(function () {
                    $('table')
                            .find('[name=' + $(this).attr('name') + ']')
                            .toggle($(this).is(':checked'));
                    var names = [];
                    $('#filter input:checked').each(function () {
                        names.push($(this).attr('name'));
                    });
                    names = names.filter(function (itm, i, a) {
                        return i == a.indexOf(itm);
                    });
                    $.cookie($('#filter').data('cookie'), names, {expires: 365});
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
                        reloader({
                            'action': 'reloader',
                            'tplMain': 'ajax',
                            'tplBlock': 'allTubes',
                            'secondary': new Date().getTime()
                        }, {
                            'containerClass': '#idAllTubes',
                            'containerClassCopy': '#idAllTubesCopy',
                        });
                        $('#autoRefresh').toggleClass('btn-success');
                    } else {
                        clearTimeout(timer);
                        doAutoRefresh = false;
                        $('#autoRefresh').toggleClass('btn-success');
                    }
                    return false;
                });
                $('#autoRefreshSummary').on('click', function () {
                    if (!$('#autoRefreshSummary').hasClass('btn-success')) {
                        reloader({
                            'action': 'reloader',
                            'tplMain': 'ajax',
                            'tplBlock': 'serversList',
                            'secondary': new Date().getTime()
                        }, {
                            'containerClass': '#idServers',
                            'containerClassCopy': '#idServersCopy',
                        });
                        $('#autoRefreshSummary').toggleClass('btn-success');
                    } else {
                        clearTimeout(timer);
                        doAutoRefresh = false;
                        $('#autoRefreshSummary').toggleClass('btn-success');
                    }
                    return false;
                });

                if (contentType == 'json') {
                    $('pre code').each(function () {
                        var cn = $(this).html();
                        var jn = formatJson(cn);
                        $(this).html(jn);
                    });
                }

                $('#clearTubesSelect').on('click', function () {
                    $('#clear-tubes input[type=checkbox]:regex(name,' + $("#tubeSelector").val() + ')').prop('checked', true);
                    $.cookie("tubeSelector", $("#tubeSelector").val(), {
                        expires: 365
                    });
                    $('#clearTubes').text('Clear ' + $('#clear-tubes input[type=checkbox]:checked').length + ' selected tubes');
                });

                $('#clearTubes').on('click', function () {
                    clearTubes();
                });

                $('#settings input').on('change', function () {
                    var val;
                    if ($(this).attr('type') == 'checkbox') {
                        if ($(this).is(':checked')) {
                            val = $(this).val();
                        } else {
                            val = null;
                        }
                    } else {
                        val = $(this).val();
                    }
                    if (jQuery.inArray(this.id, ['isDisabledUnserialization', 'isDisabledJsonDecode', 'isDisabledJobDataHighlight']) >= 0)
                        val = $(this).is(':checked') ? null : 1;
                    $.cookie(this.id, val, {expires: 365});
                });

                $('.addSample').on('click', function () {
                    var selectedText = "";
                    if (typeof window.getSelection != "undefined") {
                        var sel = window.getSelection();
                        if (sel.rangeCount) {
                            var container = document.createElement("div");
                            for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                                container.appendChild(sel.getRangeAt(i).cloneContents());
                            }
                            selectedText = container.textContent || container.innerText;
                        }
                    } else
                    if (typeof document.selection != "undefined") {
                        if (document.selection.type == "Text") {
                            selectedText = document.selection.createRange().htmlText;
                        }
                    }
                    $('#addsamplename').val(selectedText);
                    $('#addsamplestate').val($(this).data('state'));
                    $('#modalAddSample').modal('toggle');
                    return false;
                });

                $('#sampleSave').on('click', function () {
                    addSampleJob();
                    return false;
                });

                $('.moveJobsNewTubeName').click(function (e) {
                    e.stopPropagation();
                });

                $('.moveJobsNewTubeName').keypress(function (e) {
                    if (e.which == 13) {
                        if ($(this).val().length > 0) {
                            console.log($(this).data('href') + encodeURIComponent($(this).val()));
                        }
                        document.location.replace($(this).data('href') + ($(this).val()));
                    }
                });
                $(document).on('click', '#addServer', function () {
                    $('#servers-add').modal('toggle');
                    return false;
                });
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
                        expires: 365
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
                    'tubeName': $('#tubeName').val(),
                    'tubeData': $('#tubeData').val(),
                    'tubePriority': $('#tubePriority').val(),
                    'tubeDelay': $('#tubeDelay').val(),
                    'tubeTtr': $('#tubeTtr').val()
                }

                $.ajax({
                    'url': url + '&action=addjob',
                    'data': params,
                    'success': function (data) {
                        var result = data.result;
                        cleanFormNewJob();
                        location.reload();
                    },
                    'type': 'POST',
                    'dataType': 'json',
                    'error': function () {
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

            function reloader(params, options) {
                doAutoRefresh = true;
                $.ajax({
                    'url': url,
                    'data': params,
                    'success': function (data) {
                        if (doAutoRefresh) {
                            var ms = 500;
                            if ($.cookie('autoRefreshTimeoutMs')) {
                                ms = parseInt($.cookie('autoRefreshTimeoutMs'));
                            }
                            if (ms < 200) {
                                ms = 200;
                            }
                            // wrapping all of this to prevent last update
                            // after you turn it off
                            var html = $(options.containerClass).html();
                            $(options.containerClass).html(data);
                            $(options.containerClassCopy).html(html);
                            updateTable(options.containerClass, options.containerClassCopy);
                            timer = setTimeout(reloader, ms, params, options);
                        }
                    },
                    'type': 'GET',
                    'dataType': 'html',
                    'error': function () {
                        console.log('error ajax...');
                    }
                });
            }

            function updateTable(containerClass, containerClassCopy) {
                var td1 = $(containerClass + ' table').find('td'), td2 = $(containerClassCopy + ' table').find('td');
                for (i = 0, il = td1.length; i < il; i++) {
                    if (typeof td2[i] === 'undefined' || typeof td1[i] === 'undefined') {                    // tube is missing
                        continue;
                    }
                    var l = td1[i].innerText || td1[i].innerHTML || td1[i].textContent;
                    var r = td2[i].innerText || td2[i].innerHTML || td2[i].textContent;
                    if (l.trim() != r.trim()) {
                        var $td1 = $(td1[i]), color = $td1.css('background-color');
                        $td1.css({
                            'background-color': '#afa'
                        }).animate({
                            'background-color': color
                        }, 500);
                    }
                }
            }

            function clearTubes() {
                if ($('#clear-tubes input[type=checkbox]:checked').length === 0) {
                    return;
                }

                $.ajax({
                    'url': url + '&action=clearTubes',
                    'data': $('#clear-tubes input[type=checkbox]:checked').serialize(),
                    'success': function (data) {
                        var result = data.result;
                        location.reload();
                    },
                    'type': 'POST',
                    'error': function () {
                        alert('error from ajax (clear might take a while, be patient)...');
                    }
                });
            }

            function addSampleJob() {
                if (!$('#addsamplename').val() || $('input[name^=tube]:checked').length < 1) {
                    $('#sampleSaveAlert span').text('Required fields are marked *');
                    $('#sampleSaveAlert').fadeIn('fast');
                    return;
                }

                $.ajax({
                    'url': url + '&action=addSample',
                    'data': $('#modalAddSample input').serialize(),
                    'success': function (data) {
                        console.log(data);
                        if (data.result) {
                            $('#modalAddSample').modal('toggle');
                        } else {
                            $('#sampleSaveAlert span').text(data.error);
                            $('#sampleSaveAlert').fadeIn('fast');
                        }
                    },
                    'type': 'POST',
                    'dataType': 'json',
                    'error': function () {
                        alert('error ajax...');
                    }
                });
            }
        }
);
