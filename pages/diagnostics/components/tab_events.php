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

<div id="_logs_tab_events">

    <h4>Events:</h4>
    <table id="_events_table" class="table table-striped table-condensed text-center">
        <tr>
          <th class="col-md-2 text-center">Relative Time</th>
          <th class="col-md-4 text-center">Absolute Time</th>
          <th class="col-md-6 text-center">Event type</th>
        </tr>
    </table>

</div>


<script type="text/javascript">

function _tab_events_render_all(seek){
    let events = [];
    let keys = get_listed_logs('_key');
    keys.forEach(function(key){
        let color = get_log_info(key, '_color');
        color = 'rgba({0}, 0.8)'.format(color);
        let log_evts = window._DIAGNOSTICS_LOGS_DATA[key][seek];
        log_evts.forEach(function(evt){
            events.push({
                rel_time: evt.time - window._DIAGNOSTICS_LOGS_DATA[key]['/general'].time
            });
        });
    });
    events.sort((a, b) => (a.time > b.time) ? 1 : -1);
    // ---
    events.forEach(function(evt){
        // render events
        $('#_logs_tab_events #_events_table').append(`
            <tr>
              <th class="col-md-2 text-center">{rel_time}</th>
              <th class="col-md-4 text-center">{abs_time}</th>
              <th class="col-md-6 text-center">{event_type}</th>
            </tr>
        `.format(evt));
    })
}

// this gets executed when the tab gains focus
let _tab_events_on_show = function(){
    let seek = '/events';
    fetch_log_data(seek, null, _tab_events_render_all);
};

// this gets executed when the tab loses focus
let _tab_events_on_hide = function(){
    $('#_logs_tab_events').empty();
};

$('#_logs_tab_btns a[href="#events"]').on('shown.bs.tab', _tab_events_on_show);
$('#_logs_tab_btns a[href="#events"]').on('hidden.bs.tab', _tab_events_on_hide);
</script>
