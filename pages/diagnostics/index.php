<?php
use \system\classes\Core;
use \system\packages\data\Data;

$LOGS_VERSION = "v1";

$logs_db_host = Core::getSetting('logs_db_host', 'duckietown');
$logs_db_name = Core::getSetting('logs_db_name', 'duckietown');
$db_app_id = Core::getSetting('db_app_id', 'duckietown');
$db_app_secret = Core::getSetting('db_app_secret', 'duckietown');

$api_info = [];
if (strlen($logs_db_host) > 0) {
    $api_info['host'] = $logs_db_host;
}
if (strlen($db_app_id) > 0 && strlen($db_app_secret) > 0) {
    $api_info['auth'] = [
        'app_id' => $db_app_id,
        'app_secret' => $db_app_secret
    ];
}
?>

<table style="width:100%; margin-bottom:32px">
    <tr style="border-bottom:1px solid #ddd; ">
      <td style="width:100%">
        <h2>Diagnostics</h2>
      </td>
    </tr>
    <tr>
      <td style="width: 100%; padding-top: 6px">
        <div class="_logs_progress_bar progress" style="height: 12px; display: none">
          <div class="_logs_progress_bar progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
          </div>
        </div>
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
$res = Data::list($logs_db_name);
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
window._DIAGNOSTICS_LOADING_PROGRESS = 0;
window._DIAGNOSTICS_LOGS_DURATION = 0;
window._DIAGNOSTICS_LOGS_X_RESOLUTION = 1;
window._DIAGNOSTICS_LOGS_X_RANGE = [];

function get_chart_dataset(opts){
    let gradient = opts['canvas'].get(0).getContext('2d').createLinearGradient(0, 0, 0, 600);
    gradient.addColorStop(0, "rgba({0}, .6)".format(opts['color']));
    gradient.addColorStop(0.5, "rgba(255, 255, 255, 0)");
    gradient.addColorStop(1, "rgba(255, 255, 255, 0)");
    // opts['data'] = opts['data'].filter(p => (p.x <= window._DIAGNOSTICS_LOGS_DURATION));
    opts['data'] = opts['data'].map(function(p){return {
        x: Math.min(p.x, window._DIAGNOSTICS_LOGS_DURATION),
        y: p.y
    }});
    // ---
    let default_opts = {
        backgroundColor: opts['no_background']? 'rgba(0, 0, 0, 0)' : gradient,
        borderColor: "rgba({0}, .8)".format(opts['color']),
        pointRadius: 3,
        pointBackgroundColor: '#fff',
        borderWidth: 1,
        fill: true
    };
    return {...default_opts, ...opts};
}

function format_time(secs){
    let parts = [];
    if (secs > 59)
        parts.push('{0}m'.format(Math.floor(secs / 60)));
    if (secs % 60 !== 0 || secs === 0)
        parts.push('{0}s'.format(secs % 60));
    return parts.join(' ');
}

function _update_progress_bar(){
    let perc = window._DIAGNOSTICS_LOADING_PROGRESS;
    let pbar = $('._logs_progress_bar.progress');
    let pbar_progress = $('._logs_progress_bar.progress-bar');
    if (perc <= 0 && pbar.css('display') === 'none') return;
    pbar.css('display', 'block');
    perc = Math.max(0, Math.min(100, perc));
    pbar_progress.css('width', '{0}%'.format(perc));
    if (perc >= 100) {
        setTimeout(function(){
            if (window._DIAGNOSTICS_LOADING_PROGRESS >= 100) {
                window._DIAGNOSTICS_LOADING_PROGRESS = 0;
                pbar.css('display', 'none');
            }
        }, 1000);
    }
}

$(document).on('ready', function(){
    setInterval(_update_progress_bar, 500);
});
</script>
