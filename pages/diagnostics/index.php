<?php
use \system\classes\Core;
use \system\packages\data\Data;

$LOGS_DATABASE = "db_log_default";
$LOGS_VERSION = "v1";
?>

<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">
    <tr>
      <td style="width:100%">
        <h2>Diagnostics</h2>
      </td>
    </tr>
</table>

<style type="text/css">
#_log_selectors_form .row ._selector:nth-child(2){
    padding-left: 15px;
}

#_log_selectors_form .row ._selector{
    padding-left: 5px;
    padding-right: 5px;
}

._logs_list{
    font-size: 13px;
}

#_logs_tab_btns li > a{
    color: #555;
}
</style>

<?php
$res = Data::list($LOGS_DATABASE);
if (!$res['success']) {
    echo sprintf('<h3 class="text-center">ERROR: %s</h3>', $res['data']);
    return;
}
?>

<?php
$tabs = [
    'logs' => [
        'name' => 'Logs',
        'icon' => 'info-circle'
    ],
    'system' => [
        'name' => 'System',
        'icon' => 'microchip'
    ],
    'resources' => [
        'name' => 'Resources',
        'icon' => 'tachometer'
    ],
    'events' => [
        'name' => 'Events',
        'icon' => 'history'
    ],
    'health' => [
        'name' => 'Health',
        'icon' => 'medkit'
    ],
    'containers' => [
        'name' => 'Containers',
        'icon' => 'cubes'
    ],
    'processes' => [
        'name' => 'Processes',
        'icon' => 'gears'
    ],
    'disk' => [
        'name' => 'Disk',
        'icon' => 'hdd-o'
    ],
    'network' => [
        'name' => 'Network',
        'icon' => 'exchange'
    ]
];
?>


<!-- Nav tabs -->
<ul class="nav nav-tabs" id="_logs_tab_btns" role="tablist">
    <?php
    foreach ($tabs as $tab_id => $tab) {
        ?>
        <li role="presentation" class="<?php echo ($tab_id == 'logs')? 'active' : '' ?>">
            <a href="#<?php echo $tab_id ?>" aria-controls="<?php echo $tab_id ?>" role="tab" data-toggle="tab">
                <i class="fa fa-<?php echo $tab['icon'] ?>" aria-hidden="true"></i> <?php echo $tab['name'] ?>
            </a>
        </li>
        <?php
    }
    ?>
</ul>

<!-- Tab panes -->
<div class="tab-content" id="_logs_tab_container" style="padding: 20px 0">
    <?php
    foreach ($tabs as $tab_id => $tab) {
        ?>
        <div role="tabpanel" class="tab-pane <?php echo ($tab_id == 'logs')? 'active' : '' ?>" id="<?php echo $tab_id ?>">
        <?php
            include sprintf('%s/components/tab_%s.php', __DIR__, $tab_id);
        ?>
        </div>
        <?php
    }
    ?>
</div>

<script type="text/javascript">
window._DIAGNOSTICS_LOGS_KEYS = [];
window._DIAGNOSTICS_LOGS_DATA = {};

function get_chart_dataset(opts){
    let gradient = opts['canvas'].get(0).getContext('2d').createLinearGradient(0, 0, 0, 600);
    gradient.addColorStop(0, "rgba({0}, .6)".format(opts['color']));
    gradient.addColorStop(0.5, "rgba(255, 255, 255, 0)");
    gradient.addColorStop(1, "rgba(255, 255, 255, 0)");
    // ---
    let default_opts = {
        backgroundColor: gradient,
        borderColor: "rgba({0}, .8)".format(opts['color']),
        pointRadius: 3,
        pointBackgroundColor: '#fff',
        borderWidth: 1,
        fill: true
    };
    return {...default_opts, ...opts};
}
</script>
