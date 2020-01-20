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

<link
    href="<?php echo Core::getCSSstylesheetURL('bootstrap-select.min.css') ?>"
    rel="stylesheet"
>

<script
    src="<?php echo Core::getJSscriptURL('bootstrap-select.min.js') ?>"
    type="text/javascript">
</script>

<script type="text/javascript">
$.fn.selectpicker.Constructor.BootstrapVersion = '3';
</script>

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

