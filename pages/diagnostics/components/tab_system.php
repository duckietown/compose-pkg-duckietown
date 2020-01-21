<?php
_logs_print_table_structure();

/*
Keys used from Log:

    OSType
    OperatingSystem
    KernelVersion
    Architecture
    Name
    SystemTime
    mem} G
    NCPU
    ServerVersion
    Images
    Containers
    ContainersRunning
    ContainersPaused
    ContainersStopped

*/
?>

<hr>

<div id="_logs_tab_system">
</div>


<style type="text/css">
#_logs_tab_system > .panel > .panel-heading {
    background-image: none;
}
</style>


<script type="text/javascript">
var _LOGS_SYS_BLOCK_TEMPLATE = `
<div class="panel panel-default">
  <div class="panel-heading" style="background-color: {color}">{title}</div>
  <div class="panel-body">
    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt>OS Type</dt>
          <dd>{OSType}</dd>
          <dt>OS Distro</dt>
          <dd>{OperatingSystem}</dd>
          <dt>Kernel Version</dt>
          <dd>{KernelVersion}</dd>
          <dt>Architecture</dt>
          <dd>{Architecture}</dd>
        </dl>
    </div>
    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt>Hostname</dt>
          <dd>{Name}</dd>
          <dt>System Time (Log)</dt>
          <dd>{SystemTime}</dd>
          <dt>Memory</dt>
          <dd>{mem} GB</dd>
          <dt>Processors</dt>
          <dd>{NCPU}</dd>
        </dl>
    </div>
    <br/>
    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt><u>Docker</u>:</dt><dd></dd>
          <dt>Version</dt>
          <dd>{ServerVersion}</dd>
          <dt>Images</dt>
          <dd>{Images}</dd>
          <dt>Containers</dt>
          <dd>{Containers}</dd>
        </dl>
    </div>
    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt><u>Containers</u>:</dt><dd></dd>
          <dt>Running</dt>
          <dd>{ContainersRunning}</dd>
          <dt>Paused</dt>
          <dd>{ContainersPaused}</dd>
          <dt>Stopped</dt>
          <dd>{ContainersStopped}</dd>
        </dl>
    </div>
  </div>
</div>`;

// this gets executed when the tab gains focus
let on_show = function(){
    // get logs list
    let tab_data = table_to_object('#_main_table');
    // render logs info
    tab_data.forEach(function(tab_row){
        let key = tab_row['_key'];
        let color = 'rgba({0}, 0.8)'.format(tab_row['_color'].slice(4, -1));
        let log_data = window._DIAGNOSTICS_LOGS_DATA[key];
        let memGB = log_data['endpoint']['MemTotal'] / Math.pow(1000, 3);
        let extra_info = {
            'title': '<strong>Log: </strong>{0}'.format(key),
            'color': color,
            'mem': Math.round(memGB, 2)
        };
        $('#_logs_tab_system').append(
            _LOGS_SYS_BLOCK_TEMPLATE.format({...log_data['endpoint'], ...extra_info})
        );
    });
};

// this gets executed when the tab loses focus
let on_hide = function(){
    $('#_logs_tab_system').empty();
};

$('#_logs_tab_btns a[href="#system"]').on('shown.bs.tab', on_show);
$('#_logs_tab_btns a[href="#system"]').on('hidden.bs.tab', on_hide);
</script>
