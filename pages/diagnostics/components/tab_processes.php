<?php
_logs_print_table_structure();

/*
Keys used from Log:

    process_stats:

        {
            "container": "2077a99f825b7e03bc9c296c7fe02397a25c6cea357a57eda8a35778d8e86bef",
            "time": 1579651459.8183,
            "ppid": "31042",
            "pid": "31063",
            "pcpu": "3.0",
            "nthreads": "1",
            "cputime": "00:00:00",
            "pmem": "0.0",
            "mem": 0.404,
            "command": "\/bin\/bash \/launch\/dt-system-monitor\/launch.sh"
        },

*/
?>

<hr>

<div id="_logs_tab_processes">
</div>


<style type="text/css">
#_logs_tab_processes > .panel > .panel-heading {
    background-image: none;
}
</style>


<script type="text/javascript">
var _LOGS_PROCESS_BLOCK_TEMPLATE = `
<div class="panel panel-default">
  <div class="panel-heading" style="background-color: {color}"><strong>Log: </strong>{log} <span style="float: right"><strong>Container: </strong>{container_name}</span></div>
  <div class="panel-body">

    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt><u>Container</u>:</dt><dd></dd>
          <dt>Name</dt>
          <dd>{container_name}</dd>
        </dl>
    </div>

    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt><u>Process</u>:</dt><dd></dd>
          <dt>PID</dt>
          <dd>{pid}</dd>
          <dt>Parent PID</dt>
          <dd>{ppid}</dd>
        </dl>
    </div>

    <div class="col-md-12">&nbsp;<br/></div>

    <div class="col-md-12">&nbsp;<br/></div>

    <div class="col-md-12">
        <dl class="dl-horizontal">
          <dt>Command</dt>
          <dd>{command}</dd>
        </dl>
    </div>

    <div id="_cpu_pid{pid}_canvas_container">

    </div>

  </div>
</div>`;


function _tab_processes_render_single_log(key, seek){
    if (seek !== '/process_stats') return;
    let color = get_log_info(key, '_color');
    let log_data = window._DIAGNOSTICS_LOGS_DATA[key][seek];
    let log_containers = window._DIAGNOSTICS_LOGS_DATA[key]['/containers'];
    let start_time = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].time;
    let duration = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].duration;
    // aggregate data
    let data = {};
    log_data.forEach(function(proc){
        let PID = proc['pid'];
        if (!data.hasOwnProperty(PID)) {
            data[PID] = {
                log: key,
                container_name: log_containers[proc['container']],
                container: proc['container'],
                ppid: proc['ppid'],
                pid: PID,
                command: proc['command'],
                time: [],
                pcpu: [],
                cputime: [],
                pmem: [],
                mem: [],
                nthreads: []
            };
        }
        // add temporal data
        data[PID].time.push(proc['time']);
        data[PID].pcpu.push(parseFloat(proc['pcpu']));
        data[PID].cputime.push(proc['cputime']);
        data[PID].pmem.push(parseFloat(proc['pmem']));
        data[PID].mem.push(proc['mem']);
        data[PID].nthreads.push(parseInt(proc['nthreads']));
    });
    // draw each process
    for (const [pid, proc_data] of Object.entries(data)) {
        $('#_logs_tab_processes').append(
            _LOGS_PROCESS_BLOCK_TEMPLATE.format(proc_data)
        );
        // add CPU canvas to process tab
        let cpu_canvas = $('<canvas/>').width('100%').height('200px');
        $('#_logs_tab_processes #_cpu_pid{0}_canvas_container'.format(pid)).append(cpu_canvas);
        // render CPU usage
        new Chart(cpu_canvas, {
            type: 'line',
            data: {
                labels: range(0, duration, 5),
                datasets: [
                    get_chart_dataset({
                        canvas: cpu_canvas,
                        label: 'CPU usage (%)',
                        data: proc_data.pcpu,
                        color: color
                    })
                ]
            },
            options: {
                scales: {
                    yAxes: [
                        {
                            ticks: {
                                callback: function(label) {
                                    return label.toFixed(0)+' %';
                                },
                                min: 0,
                                max: 100
                            },
                            gridLines: {
                                display: false
                            }
                        }
                    ]
                }
            }
        });

    }
}

// this gets executed when the tab gains focus
let _tab_processes_on_show = function(){
    let seek = ['/process_stats', '/containers'];
    fetch_log_data(seek, _tab_processes_render_single_log);
};

// this gets executed when the tab loses focus
let _tab_processes_on_hide = function(){
    $('#_logs_tab_processes').empty();
};

$('#_logs_tab_btns a[href="#processes"]').on('shown.bs.tab', _tab_processes_on_show);
$('#_logs_tab_btns a[href="#processes"]').on('hidden.bs.tab', _tab_processes_on_hide);
</script>
