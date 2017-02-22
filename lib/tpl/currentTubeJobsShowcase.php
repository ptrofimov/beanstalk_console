<section class="jobsShowcase">
    <?php foreach ((array) $peek as $state => $job): ?>
        <hr>
        <div class="pull-left">
            <h3>Next job in "<?php echo $state ?>" state</h3>
        </div>
        <div class="clearfix"></div>
        <?php if ($job): ?>

            <div class="row show-grid">
                <div class="col-sm-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Stats:</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($job['stats'] as $key => $value): ?>
                                <tr>
                                    <td><?php echo $key ?></td>
                                    <td>
                                        <?php
                                        if($key === 'age') {
                                          $days = floor($value / 86400);
                                          $hours = floor($value / 3600 % 24);
                                          $minutes = floor($value / 60 % 60);
                                          $seconds = floor($value % 60);
                                          echo $days > 0 ? 'days: ' . $days . '<br>' : '';
                                          echo $hours > 0 ? 'hours: ' . $hours . '<br>' : '';
                                          echo $minutes > 0 ? 'minutes: ' . $minutes . '<br>' : '';
                                          echo $seconds > 0 ? 'seconds: ' . $seconds : '';
                                        } else {
                                          echo $value;
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-sm-9">
                    <div class="clearfix">
                        <div class="pull-left">
                            <b>Job data:</b>
                        </div>
                        <?php if ($job): ?>
                            <div class="pull-right">
                                <div style="margin-bottom: 3px;">
                                    <a class="btn btn-sm btn-info addSample" data-jobid="<?php echo $job['id']; ?>"
                                       href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=addSample"><i class="glyphicon glyphicon-plus glyphicon-white"></i> Add to
                                        samples</a>

                                    <div class="btn-group">
                                        <button class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                            <i class="glyphicon glyphicon-arrow-right glyphicon-white"></i> Move all <?php echo $state ?> to
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><input class="moveJobsNewTubeName" type="text" class="input-medium"
                                                       data-href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=moveJobsTo&state=<?php echo $state; ?>&destTube="
                                                       placeholder="New tube name"/></li>
                                                <?php
                                                if (isset($tubes) && is_array($tubes) && count($tubes)) {
                                                    foreach ($tubes as $key => $name) {
                                                        ?>
                                                    <li>
                                                        <a href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=moveJobsTo&destTube=<?php echo $name; ?>&state=<?php echo $state; ?>"><?php echo htmlspecialchars($name); ?></a>
                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                                <?php
                                            }
                                            ?>
                                            <?php
                                            if ($state == 'ready') {
                                                ?>
                                                <li class="divider"></li>
                                                <li>
                                                    <a href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&action=moveJobsTo&destState=buried&state=<?php echo $state; ?>">Buried</a>
                                                </li>
                                                <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                    <a class="btn btn-sm btn-danger"
                                       href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&state=<?php echo $state ?>&action=deleteAll&count=1"
                                       onclick="return confirm('This process might hang a while on tubes with lots of jobs. Are you sure you want to continue?');"><i
                                            class="glyphicon glyphicon-trash glyphicon-white"></i> Delete all <?php echo $state ?> jobs</a>
                                    <a class="btn btn-sm btn-danger"
                                       href="./?server=<?php echo $server ?>&tube=<?php echo urlencode($tube) ?>&state=<?php echo $state ?>&action=deleteJob&jobid=<?php echo $job['id']; ?>"><i
                                            class="glyphicon glyphicon-remove glyphicon-white"></i> Delete</a>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                    <pre><code><?php echo htmlspecialchars(trim(var_export($job['data'], true), "'"), ENT_COMPAT) ?></code></pre>
                </div>
            </div>
        <?php else: ?>
            <i>empty</i>
        <?php endif ?>
    <?php endforeach ?>
</section>
