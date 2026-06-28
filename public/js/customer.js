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

                // Review batch preparation guard: keep state/count/safety controls aligned.
                function updateReviewBatchStartState() {
                    var $option = $('#reviewState option:selected');
                    var count = parseInt($option.data('count'), 10) || 0;
                    var forced = $('input[name="forceUnsafe"]').is(':checked') && parseInt($option.data('force-allowed'), 10) === 1;
                    var allowed = parseInt($option.data('allowed'), 10) === 1 || forced;
                    var message = $option.data('message') || '';
                    var $limit = $('#reviewLimit');
                    var limit = parseInt($limit.val(), 10) || 0;
                    $limit.attr('max', count);
                    if (limit < 1 || limit > count) {
                        $limit.val(count);
                    }
                    $('#reviewSafetyMessage')
                        .toggleClass('alert-info', allowed)
                        .toggleClass('alert-warning', !allowed)
                        .text(message);
                    $('#reviewBatchStartSubmit').prop('disabled', count === 0 || !allowed);
                    $('#reviewBatchPauseProceedSubmit').toggle(count > 0 && !allowed);
                }
                function validateReviewBatchStart(allowUnsafePause) {
                    var $option = $('#reviewState option:selected');
                    var count = parseInt($option.data('count'), 10) || 0;
                    var forced = $('input[name="forceUnsafe"]').is(':checked') && parseInt($option.data('force-allowed'), 10) === 1;
                    var allowed = parseInt($option.data('allowed'), 10) === 1 || forced;
                    var limit = parseInt($('#reviewLimit').val(), 10) || 0;
                    if (count === 0) {
                        alert('The selected state has no jobs to review.');
                        return false;
                    }
                    if (limit < 1 || limit > count) {
                        alert('Jobs to review must be between 1 and the selected state count.');
                        return false;
                    }
                    if (!allowed && !allowUnsafePause) {
                        alert($option.data('message') || 'The selected state cannot be reviewed safely.');
                        return false;
                    }
                    return true;
                }
                $('#reviewState').on('change', updateReviewBatchStartState);
                $('input[name="forceUnsafe"]').on('change', updateReviewBatchStartState);
                $('#reviewBatchStartSubmit').on('click', function () {
                    return validateReviewBatchStart(false);
                });
                $('#reviewBatchPauseProceedSubmit').on('click', function () {
                    return validateReviewBatchStart(true);
                });
                updateReviewBatchStartState();
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

                // Review table controls: selection, bulk action guards, body expansion, and progress runners.
                $('#reviewSelectAll').on('change', function () {
                    $('.reviewJobCheckbox').prop('checked', $(this).is(':checked'));
                });
                $('#reviewSelectAllButton').on('click', function () {
                    $('.reviewJobCheckbox').prop('checked', true);
                    $('#reviewSelectAll').prop('checked', true);
                    return false;
                });
                $('#reviewSelectNoneButton').on('click', function () {
                    $('.reviewJobCheckbox').prop('checked', false);
                    $('#reviewSelectAll').prop('checked', false);
                    return false;
                });
                var lastReviewCheckbox = null;
                $('.reviewJobCheckbox').on('click', function (e) {
                    if (e.shiftKey && lastReviewCheckbox) {
                        clearTextSelection();
                        selectReviewCheckboxRange(lastReviewCheckbox, this, $(this).is(':checked'));
                    }
                    lastReviewCheckbox = this;
                    updateReviewSelectAllState();
                    e.stopPropagation();
                });
                $('.reviewJobCheckbox').on('click', function (e) {
                    var checkbox = this;
                    if (e.shiftKey && lastReviewCheckbox) {
                        selectReviewCheckboxRange(lastReviewCheckbox, checkbox, checkbox.checked);
                    }
                    lastReviewCheckbox = checkbox;
                    updateReviewSelectAllState();
                });
                $('.reviewJobView').on('click', function () {
                    loadReviewJobBody($(this).data('review-id'), this);
                    return false;
                });
                $('#reviewJobsTable tbody').on('click', 'tr.clickable-row', function (e) {
                    if ($(e.target).is('input, button, a') || $(e.target).parents('input, button, a').length) {
                        return;
                    }
                    var $btn = $(this).find('.reviewJobView');
                    if ($btn.length) {
                        loadReviewJobBody($btn.data('review-id'), $btn[0]);
                    }
                });
                $('#reviewToggleBodies').on('click', function () {
                    var $button = $(this);
                    if ($button.data('expanded')) {
                        showVisibleReviewPreviews();
                        $button.data('expanded', 0).text('Show full bodies on this page');
                    } else {
                        showVisibleReviewBodies();
                        $button.data('expanded', 1).text('Show previews');
                    }
                    return false;
                });
                $('button[data-confirm]').on('click', function () {
                    if ($(this).data('requires-selection') && $('.reviewJobCheckbox:checked').length === 0) {
                        alert('No review jobs are selected.');
                        return false;
                    }
                    if ($(this).data('requires-moved') && (parseInt($('#reviewRemainingMovedCount').val(), 10) || 0) === 0) {
                        alert('There are no undecided review jobs to process.');
                        return false;
                    }
                    if ($(this).data('requires-target') && $.trim($('#reviewTargetTube').val()) === '') {
                        alert('Destination tube is required for this action.');
                        return false;
                    }
                    return confirm($(this).data('confirm'));
                });
                if ($('#reviewBatchProgress').length) {
                    processReviewBatch();
                }
                if ($('#reviewBatchOperationProgress').length) {
                    processReviewBatchOperation();
                }
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
                        if ($(e.target).is('input, textarea, select') || $(e.target).is('[contenteditable=true]')) {
                            return;
                        }
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

            // Continue preparing a review batch one server-side chunk at a time.
            function processReviewBatch() {
                var $container = $('#reviewBatchProgress');
                var batchId = $container.data('batch-id');
                if (!batchId) {
                    return;
                }

                $.ajax({
                    'url': url + '&action=reviewBatchProcess',
                    'data': {'batchId': batchId},
                    'success': function (data) {
                        if (!data || !data.batch) {
                            $('#reviewBatchMessage').text('Unexpected response while processing the review batch.');
                            return;
                        }

                        var batch = data.batch;
                        var target = parseInt(batch.target_count, 10) || 0;
                        var processed = parseInt(batch.processed, 10) || 0;
                        var pct = target > 0 ? Math.min(100, Math.floor((processed / target) * 100)) : 100;

                        $('#reviewBatchStatus').text(batch.status);
                        $('#reviewBatchProcessed').text(processed);
                        $('#reviewBatchTarget').text(target);
                        $('#reviewBatchProgressBar').css('width', pct + '%');

                        if (batch.status === 'processing') {
                            setTimeout(processReviewBatch, 200);
                        } else if (batch.status === 'complete' || batch.status === 'error') {
                            window.location.href = $container.data('show-url');
                        } else {
                            $('#reviewBatchMessage').text(batch.error_message || batch.safety_message || 'Review batch stopped.');
                        }
                    },
                    'type': 'POST',
                    'dataType': 'json',
                    'error': function () {
                        $('#reviewBatchMessage').text('Error while processing the review batch.');
                    }
                });
            }

            // Continue a long-running all-return/all-delete review operation in chunks.
            function processReviewBatchOperation() {
                var $container = $('#reviewBatchOperationProgress');
                var batchId = $container.data('batch-id');
                if (!batchId) {
                    return;
                }

                $.ajax({
                    'url': url + '&action=reviewBatchOperationProcess',
                    'data': {'batchId': batchId},
                    'success': function (data) {
                        if (!data || !data.operation) {
                            $('#reviewBatchOperationMessage').text('Unexpected response while processing the review operation.');
                            return;
                        }

                        var operation = data.operation;
                        var target = parseInt(operation.target_count, 10) || 0;
                        var processed = parseInt(operation.processed, 10) || 0;
                        var pct = target > 0 ? Math.min(100, Math.floor((processed / target) * 100)) : 100;

                        $('#reviewBatchOperationStatus').text(operation.status);
                        $('#reviewBatchOperationProcessed').text(processed);
                        $('#reviewBatchOperationTarget').text(target);
                        $('#reviewBatchOperationErrors').text(operation.errors || 0);
                        $('#reviewBatchOperationProgressBar').css('width', pct + '%');

                        if (operation.status === 'processing') {
                            setTimeout(processReviewBatchOperation, 200);
                        } else if (operation.status === 'complete') {
                            window.location.href = $container.data('show-url');
                        } else {
                            $('#reviewBatchOperationMessage').text(operation.error_message || 'Review operation stopped.');
                        }
                    },
                    'type': 'POST',
                    'dataType': 'json',
                    'error': function () {
                        $('#reviewBatchOperationMessage').text('Error while processing the review operation.');
                    }
                });
            }

            // Show one already-loaded review-copy body in the inspection modal.
            function loadReviewJobBody(reviewId, button) {
                var $button = button ? $(button) : $();
                var $body = $('.reviewJobBodyFull[data-review-id="' + reviewId + '"]');

                $('#reviewJobBodyContent').text('');
                $('#reviewJobBodyModal').modal('show');

                if (!$body.length) {
                    $('#reviewJobBodyStats').html('<tr><td>Error</td><td>Job body is not available on this page.</td></tr>');
                    return;
                }

                var stats = [
                    { label: 'Original ID', value: $button.data('original-id') },
                    { label: 'TTR', value: $button.data('ttr') },
                    { label: 'Age', value: formatDuration($button.data('age')) },
                    { label: 'Reserves', value: $button.data('reserves') },
                    { label: 'Priority', value: $button.data('pri') },
                    { label: 'Buries', value: $button.data('buries') }
                ];

                var rows = '';
                for (var i = 0; i < stats.length; i += 2) {
                    rows += '<tr>';
                    rows += '<th style="background: #f8fafc; width: 20%; font-weight: 600;">' + escapeHtml(stats[i].label) + '</th>';
                    rows += '<td style="width: 50%;">' + escapeHtml(String(stats[i].value !== undefined && stats[i].value !== null ? stats[i].value : '')) + '</td>';
                    if (i + 1 < stats.length) {
                        rows += '<th style="background: #f8fafc; width: 15%; font-weight: 600;">' + escapeHtml(stats[i+1].label) + '</th>';
                        rows += '<td style="width: 15%;">' + escapeHtml(String(stats[i+1].value !== undefined && stats[i+1].value !== null ? stats[i+1].value : '')) + '</td>';
                    } else {
                        rows += '<th style="background: #f8fafc; width: 15%;"></th><td style="width: 15%;"></td>';
                    }
                    rows += '</tr>';
                }
                $('#reviewJobBodyStats').html(rows);
                $('#reviewJobBodyContent').text($body.text());
                if (typeof hljs !== 'undefined') {
                    $('#reviewJobBodyContent').removeClass().addClass('json');
                    hljs.highlightBlock($('#reviewJobBodyContent')[0]);
                }

                // Set up modal action form
                var status = $button.data('status') || '';
                var ownedByAnotherSession = $('#reviewModalJobForm').data('owned') === 1;

                $('#reviewModalJobId').val(reviewId);
                $('#reviewModalTargetTube').val($('#reviewTargetTube').val());
                $('#reviewModalDelay').val($('#reviewReturnDelay').val());

                var isSelectable = (status === 'moved' || status === 'duplicated');
                var isCleanup = (status === 'duplicated' || status === 'error');
                var canDelete = isSelectable || isCleanup;

                $('#reviewModalMoveBtn').prop('disabled', !isSelectable || ownedByAnotherSession);
                $('#reviewModalDuplicateBtn').prop('disabled', !isSelectable || ownedByAnotherSession);
                $('#reviewModalDeleteBtn').prop('disabled', !canDelete || ownedByAnotherSession);
            }

            // Expand all visible preview cells using the full bodies already loaded with the page.
            function showVisibleReviewBodies() {
                $('.reviewJobBodyPreview').each(function () {
                    var $preview = $(this);
                    var reviewId = $preview.data('review-id');
                    var $body = $('.reviewJobBodyFull[data-review-id="' + reviewId + '"]');

                    if ($body.length) {
                        $preview.text($body.text());
                    } else {
                        $preview.text('Job body is not available on this page.');
                    }
                });
            }

            function showVisibleReviewPreviews() {
                $('.reviewJobBodyPreview').each(function () {
                    $(this).text($(this).data('preview') || '');
                });
            }

            // Render one metadata row in the review body modal.
            function reviewJobStatRow(label, value) {
                if (value === undefined || value === null || value === '') {
                    value = '';
                }
                return '<tr><th>' + escapeHtml(label) + '</th><td>' + escapeHtml(String(value)) + '</td></tr>';
            }

            // Apply shift-click range selection across review job checkboxes.
            function selectReviewCheckboxRange(first, second, checked) {
                var boxes = $('.reviewJobCheckbox').toArray();
                var firstIndex = boxes.indexOf(first);
                var secondIndex = boxes.indexOf(second);
                var start;
                var end;

                if (firstIndex < 0 || secondIndex < 0) {
                    return;
                }

                start = Math.min(firstIndex, secondIndex);
                end = Math.max(firstIndex, secondIndex);

                for (var i = start; i <= end; i++) {
                    boxes[i].checked = checked;
                }
            }

            // Keep the header checkbox in sync with the visible row checkboxes.
            function updateReviewSelectAllState() {
                var $boxes = $('.reviewJobCheckbox');
                var checkedCount = $boxes.filter(':checked').length;
                $('#reviewSelectAll').prop('checked', $boxes.length > 0 && checkedCount === $boxes.length);
            }

            // Prevent accidental text highlighting while using shift-click range selection.
            function clearTextSelection() {
                if (window.getSelection) {
                    window.getSelection().removeAllRanges();
                } else if (document.selection) {
                    document.selection.empty();
                }
            }

            function formatDuration(value) {
                value = parseInt(value, 10);
                if (isNaN(value)) {
                    return '';
                }
                var days = Math.floor(value / 86400);
                var hours = Math.floor(value / 3600) % 24;
                var minutes = Math.floor(value / 60) % 60;
                var seconds = Math.floor(value % 60);
                var parts = [];

                if (days > 0) {
                    parts.push('days: ' + days);
                }
                if (hours > 0) {
                    parts.push('hours: ' + hours);
                }
                if (minutes > 0) {
                    parts.push('minutes: ' + minutes);
                }
                if (seconds > 0 || parts.length === 0) {
                    parts.push('seconds: ' + seconds);
                }

                return parts.join(', ');
            }

            function escapeHtml(value) {
                return $('<div>').text(value).html();
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
                            $('#sampleSaveAlert').removeClass('hide').fadeIn('fast');
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
