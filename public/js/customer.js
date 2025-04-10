$(document).ready(
        function () {

            var timer;
            var doAutoRefresh = false;

            // Helper function to get setting value (Cookie > Default > Fallback)
            function getSettingValue(key, ultimateFallback) {
                var cookieVal = $.cookie(key);
                if (cookieVal !== undefined && cookieVal !== null) {
                    // Checkbox cookies are '0' or '1', others are numeric strings
                    if (key.startsWith('enable')) {
                        return cookieVal == '1'; // Convert '1'/'0' to true/false
                    } else {
                        var parsed = parseInt(cookieVal);
                        return isNaN(parsed) ? ultimateFallback : parsed; // Return parsed int or fallback
                    }
                } else if (window.beanstalkConsoleDefaults && window.beanstalkConsoleDefaults[key] !== undefined) {
                    return window.beanstalkConsoleDefaults[key]; // Use default from PHP (already correct type)
                } else {
                    return ultimateFallback; // Ultimate fallback if not in cookie or defaults
                }
            }

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
                    var cookieName = this.id; // Use the input's ID as the cookie name (should match config keys now)

                    if ($(this).attr('type') == 'checkbox') {
                        // Standard logic: checked = 1 (enabled), unchecked = 0 (disabled)
                        val = $(this).is(':checked') ? 1 : 0;
                    } else {
                        // For text inputs, just use the value
                        val = $(this).val();
                    }

                    // Save the cookie using the input's ID (which matches the new config keys)
                    $.cookie(cookieName, val, {expires: 365});
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
                    $('#addsamplejobid').val($(this).data('jobid'));
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
                $('.ellipsize').on('dblclick', function () {
                    $(this).toggleClass('ellipsize');
                });
                $('.kick_jobs_no').on('change', function () {
                    if (typeof (Storage) != "undefined") {
                        localStorage.setItem($(this).attr('id'), $(this).val());
                    }
                });
                if (typeof (Storage) != "undefined") {
                    $('.kick_jobs_no').each(function () {
                        $(this).val(localStorage.getItem($(this).attr('id')) || 10);
                    });
                }

                 // Use helper function to get the effective setting (Cookie > Default)
                 var autoRefreshOnLoad = getSettingValue('enableAutoRefreshLoad', false); // Ultimate fallback is false

                 if (autoRefreshOnLoad) {
                     if ($('#autoRefresh').length) {
                         $('#autoRefresh').click();
                     }
                     if ($('#autoRefreshSummary').length) {
                         $('#autoRefreshSummary').click();
                     }
                 }

                if ($('#searchTubes').is(':visible')) { // Check if the element is visible
                    window.addEventListener("keydown",function (e) {
                        if (!e.ctrlKey && !e.altKey && !e.shiftKey && e.key.length === 1 && /^[a-zA-Z0-9]$/.test(e.key)) {
                            // Check if a single alphanumeric key was pressed
                            if (!$('#searchTubes').is(":focus") && !$('body').hasClass('modal-open')) {
                                e.preventDefault();
                                $('#searchTubes').focus();
                                // Optionally, append the pressed key to the search field
                                $('#searchTubes').val($('#searchTubes').val() + e.key);
                            }
                    }});
                }

                var $searchTubesInput = $('#searchTubes');
                var $clearSearchBtn = $searchTubesInput.siblings('.clear-search'); // Use siblings() relative to input

                // Function to toggle clear button visibility
                function toggleClearButton() {
                    var value = $searchTubesInput.val();
                    if (value && value.length > 0) {
                        $clearSearchBtn.show();
                    } else {
                        $clearSearchBtn.hide();
                    }
                }

                // Show/hide button on input/keyup
                $searchTubesInput.on('input keyup', function() {
                    toggleClearButton();
                });

                // Clear input and hide button on click
                $clearSearchBtn.on('click', function() {
                    $searchTubesInput.val('').focus(); // Clear input and refocus
                    $(this).hide(); // Hide the button itself
                    // Optionally trigger 'input' event if other scripts depend on it
                    $('#searchTubes').trigger('keyup');
                });

                // Initial check in case the input field is pre-filled on page load
                toggleClearButton();


                $('#searchTubes').on('keyup', function() {
                    var value = $(this).val().toLowerCase();
                    $('table tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                });
            } // end __init

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
                            // Use helper function to get effective timeout (Cookie > Default > Fallback 500)
                            var ms = getSettingValue('autoRefreshTimeoutMs', 500);
                            ms = Math.max(200, ms); // Enforce minimum 200ms

                            // wrapping all of this to prevent last update
                            // after you turn it off
                            var html = $(options.containerClass).html();
                            $(options.containerClass).html(data);
                            $(options.containerClassCopy).html(html);
                            updateTable(options.containerClass, options.containerClassCopy);
                            $('#searchTubes').trigger('keyup');
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
                        if (l.trim() != '0') {
                            $td1.addClass('hasValue');
                        }
                        else {
                            $td1.removeClass('hasValue');
                        }
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
