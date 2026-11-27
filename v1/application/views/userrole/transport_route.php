<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#assigned" data-toggle="tab"><i class="fas fa-bus"></i> <?=translate('assigned')?></a>
            </li>
            <li>
                <a href="#list" data-toggle="tab"><i class="fas fa-route"></i> <?=translate('route_list')?></a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="assigned" class="tab-pane active">
               <?php if (!empty($route)) { ?>
                <table class="table table-bordered table-condensed mb-none">
                    <tbody>
                        <tr><th><?=translate('route_name')?></th><td><?=html_escape($route['route_name'])?></td></tr>
                        <tr><th><?=translate('start_place')?></th><td><?=html_escape($route['start_place'])?></td></tr>
                        <tr><th><?=translate('stop_place')?></th><td><?=html_escape($route['stop_place'])?></td></tr>
                        <tr><th><?=translate('stoppage')?></th><td><?=html_escape($route['stop_position'])?></td></tr>
                        <tr><th><?=translate('pickup_point')?></th><td><?=date("g:i A", strtotime($route['stop_time']))?></td></tr>
                        <tr><th><?=translate('route_fare')?></th><td><?=currencyFormat($route['route_fare'])?></td></tr>
                        <tr><th><?=translate('vehicle_no')?></th><td><?=html_escape($route['vehicle_no'])?></td></tr>
                        <tr><th><?=translate('driver_name')?></th><td><?=html_escape($route['driver_name'])?></td></tr>
                        <tr><th><?=translate('driver_phone')?></th><td><?=html_escape($route['driver_phone'])?></td></tr>
                    </tbody>
                </table>
                <?php } else { ?>
                    <p class="text-center text-muted mb-none"><?=translate('no_information_available')?></p>
                <?php } ?>
            </div>
            <div class="tab-pane" id="list">
                <table class="table table-bordered table-hover table-condensed table-export">
                    <thead>
                        <tr>
                            <th><?=translate('sl')?></th>
                            <th><?=translate('route_name')?></th>
                            <th><?=translate('start_place')?></th>
                            <th><?=translate('stoppage')?></th>
                            <th><?=translate('stop_place')?></th>
                            <th><?=translate('vehicle_no')?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $count = 1;
                            $branchID = get_loggedin_branch_id();
                            $assigns = $this->userrole_model->getAssignList($branchID);
                            if (count($assigns)) {
                            foreach ($assigns as $assign):
                        ?>
                        <tr>
                            <td><?php echo $count++;?></td>
                            <td><?php echo html_escape($assign['name']);?></td>
                            <td><?php echo html_escape($assign['start_place']);?></td>
                            <td><?php
                                $stoppages = $this->transport_model->stoppage_pointByRoute($assign['route_id']);
                                foreach ($stoppages as $sval) {
                                    echo html_escape($sval->stop_position) . "<small class='text-muted block'> - " . translate('route_fare') . ": " . currencyFormat($sval->route_fare) . " / " . translate('stop_time') . ": " . date("g:i A", strtotime($sval->stop_time)) . "</small>";
                                } ?></td>
                            <td><?php echo html_escape($assign['stop_place']);?></td>
                            <td><?php echo $this->userrole_model->getVehicleList($assign['route_id']);?></td>
                        </tr>
                        <?php endforeach; } else { ?>
                        <tr>
                            <td colspan="6">
                                <p class="text-center text-muted mb-none"><?=translate('no_information_available')?></p>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
