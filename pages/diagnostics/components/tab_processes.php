<?php
_logs_print_table_structure();

/*
Keys used from Log:

    process_stats:
        time
        container
        ppid
        pid
        pcpu
        nthreads
        pmem
        command

    NOT USED:
        mem
        cputime

*/
?>

<hr>

<div id="_logs_tab_processes">
</div>


<style type="text/css">
#_logs_tab_processes > .panel > .panel-heading {
    background-image: none;
}

#_logs_tab_processes .panel-body div._proc_command{
    border: 1px solid lightgrey;
    padding: 5px 0 5px 10px;
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
    font-family: monospace;
    font-size: 0.9em;
}
</style>


<script type="text/javascript">
var _LOGS_PROCESS_BLOCK_TEMPLATE = `
<div class="panel panel-default">
  <div class="panel-heading" style="background-color: {color}"><strong>Log: </strong>{log} <span style="float: right"><strong>Container: </strong>{container_name}</span></div>
  <div class="panel-body">
    <table style="width: 100%;">
        <tr>
            <td>
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
            </td>
        </tr>
        <tr>
            <td>
                <div id="_log{log_i}_cpu_pid{pid}_canvas_container"></div>
                <div id="_log{log_i}_ram_pid{pid}_canvas_container"></div>
                <div id="_log{log_i}_nthreads_pid{pid}_canvas_container"></div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="input-group">
                  <span class="input-group-addon"><strong>Command</strong></span>
                    <div class="_proc_command">{command}</div>
                </div>
            </td>
        </tr>
    </table>

  </div>
</div>`;


function _format_command(cmd){
    let indent = 0;
    let space = '&emsp; ';
    let out = [];
    cmd.split(' ').forEach(function(e, i){
        let _cur_space = space.repeat(indent);
        out.push(_cur_space + e);
        if (e.startsWith('/') || i === 0) indent += 1;
    });
    return out.join('<br/>');
}

function _tab_processes_render_single_log(key, seek, log_i){
    if (seek !== '/process_stats') return;
    let color = get_log_info(key, '_color');
    let log_data = window._DIAGNOSTICS_LOGS_DATA[key][seek];
    let log_containers = window._DIAGNOSTICS_LOGS_DATA[key]['/containers'];
    let start_time = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].time;
    // aggregate data
    let data = {};
    log_data.forEach(function(proc){
        let PID = proc['pid'];
        if (!data.hasOwnProperty(PID)) {
            data[PID] = {
                log: key,
                log_i: log_i,
                color: 'rgba({0}, 0.4)'.format(color),
                container_name: log_containers[proc['container']],
                container: proc['container'],
                ppid: proc['ppid'],
                pid: PID,
                command: _format_command(proc['command']),
                pcpu: [],
                cputime: [],
                pmem: [],
                mem: [],
                nthreads: []
            };
        }
        let rel_time = parseInt(proc['time'] - start_time);
        // add temporal data
        data[PID].pcpu.push({x: rel_time, y: parseFloat(proc['pcpu'])});
        data[PID].cputime.push({x: rel_time, y: proc['cputime']});
        data[PID].pmem.push({x: rel_time, y: parseFloat(proc['pmem'])});
        data[PID].mem.push({x: rel_time, y: proc['mem']});
        data[PID].nthreads.push({x: rel_time, y: parseInt(proc['nthreads'])});
    });
    // draw each process
    for (const [pid, proc_data] of Object.entries(data)) {
        $('#_logs_tab_processes').append(
            _LOGS_PROCESS_BLOCK_TEMPLATE.format(proc_data)
        );
        // add CPU canvas to process tab
        let cpu_canvas = $('<canvas/>').width('100%').height('200px');
        $('#_logs_tab_processes #_log{0}_cpu_pid{1}_canvas_container'.format(log_i, pid)).append(cpu_canvas);
        // render CPU usage
        new Chart(cpu_canvas, {
            type: 'line',
            data: {
                labels: window._DIAGNOSTICS_LOGS_X_RANGE,
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
                    ],
                    xAxes: [
                        {
                            ticks: {
                                callback: format_time
                            }
                        }
                    ]
                }
            }
        });
        // add RAM canvas to process tab
        let ram_canvas = $('<canvas/>').width('100%').height('200px');
        $('#_logs_tab_processes #_log{0}_ram_pid{1}_canvas_container'.format(log_i, pid)).append(ram_canvas);
        // render RAM usage
        new Chart(ram_canvas, {
            type: 'line',
            data: {
                labels: window._DIAGNOSTICS_LOGS_X_RANGE,
                datasets: [
                    get_chart_dataset({
                        canvas: ram_canvas,
                        label: 'RAM usage (%)',
                        data: proc_data.pmem,
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
                    ],
                    xAxes: [
                        {
                            ticks: {
                                callback: format_time
                            }
                        }
                    ]
                }
            }
        });
        // add NTHREADS canvas to process tab
        let nthreads_canvas = $('<canvas/>').width('100%').height('200px');
        $('#_logs_tab_processes #_log{0}_nthreads_pid{1}_canvas_container'.format(log_i, pid)).append(nthreads_canvas);
        // render NTHREADS usage
        new Chart(nthreads_canvas, {
            type: 'line',
            data: {
                labels: window._DIAGNOSTICS_LOGS_X_RANGE,
                datasets: [
                    get_chart_dataset({
                        canvas: nthreads_canvas,
                        label: '# Threads',
                        data: proc_data.nthreads,
                        color: color,
                        no_background: true
                    })
                ]
            },
            options: {
                scales: {
                    yAxes: [
                        {
                            ticks: {
                                callback: function(label) {
                                    return label;
                                },
                                min: 1
                            },
                            gridLines: {
                                display: false
                            }
                        }
                    ],
                    xAxes: [
                        {
                            ticks: {
                                callback: format_time
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
