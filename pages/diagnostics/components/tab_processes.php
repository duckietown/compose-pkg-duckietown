<?php
use \system\classes\Core;

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

$_condense_plots = True;
$_leaves_only = True;
?>

<hr>

<script
    src="<?php echo Core::getJSscriptURL('bootstrap-slider.min.js', 'duckietown') ?>"
    type="text/javascript">
</script>
<link
    href="<?php echo Core::getCSSstylesheetURL('bootstrap-slider.min.css', 'duckietown') ?>"
    rel="stylesheet"
>

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


<div class="panel panel-default">
    <div class="panel-heading">Filter</div>
    <div class="panel-body">
        <form id="_logs_tab_processes_filter_form">
        <table style="width:100%">
            <tr style="height: 70px; vertical-align: top">
                <td>
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>CPU Usage (%)</dt>
                            <dd>
                                <input id="_logs_tab_processes_filter_pcpu" type="text"/><br/>
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>RAM Usage (%)</dt>
                            <dd>
                                <input id="_logs_tab_processes_filter_pmem" type="text"/><br/>
                            </dd>
                        </dl>
                    </div>
                </td>
            </tr>
            <tr style="height: 70px; vertical-align: top">
                <td>
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt># Threads</dt>
                            <dd>
                                <input id="_logs_tab_processes_filter_nthreads" type="text"/><br/>
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>Condense plots</dt>
                            <dd>
                                <input type="checkbox"
                                       data-toggle="toggle"
                                       data-onstyle="primary"
                                       data-class="fast"
                                       data-size="small"
                                       id="_logs_tab_processes_filter_condense_plots"
                                    <?php echo ($_condense_plots) ? 'checked' : '' ?>
                                >
                            </dd>
                        </dl>
                    </div>
                </td>
            </tr>
            <tr style="height: 70px; vertical-align: top">
                <td>
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt style="padding-top: 6px">Command</dt>
                            <dd>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"> > </span>
                                    <input type="text" id="_logs_tab_processes_filter_command"
                                           class="form-control" placeholder="Command contains...">
                                </div>
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>Hide parents</dt>
                            <dd>
                                <input type="checkbox"
                                       data-toggle="toggle"
                                       data-onstyle="primary"
                                       data-class="fast"
                                       data-size="small"
                                       id="_logs_tab_processes_filter_leaves_only"
                                    <?php echo ($_leaves_only) ? 'checked' : '' ?>
                                >
                            </dd>
                        </dl>
                    </div>
                </td>
            </tr>
            <tr style="vertical-align: top">
                <td>
                    <div class="col-md-12 text-right">
                        <a href="#" role="button" class="btn btn-primary" id="_logs_tab_processes_filter_apply">
                            <span class="glyphicon glyphicon-filter" aria-hidden="true"></span>
                            Apply
                        </a>
                    </div>
                </td>
            </tr>
        </table>
        </form>
    </div>
</div>

<hr>

<p>Found <strong id="_logs_tab_processes_num_processes">--</strong> processes.</p>

<hr>

<div id="_logs_tab_processes">
</div>


<script type="text/javascript">
let _LOGS_PROCESS_BLOCK_TEMPLATE = `
<div class="panel panel-default">
  <div class="panel-heading" style="background-color: {panel_color}"><strong>Log: </strong>{log} <span style="float: right"><strong>Container: </strong>{container_name}</span></div>
  <div class="panel-body">
    <table style="width: 100%;">
        <tr>
            <td>
                <div class="col-md-6">
                    <dl class="dl-horizontal">
                      <dt><u>Info</u>:</dt><dd></dd>
                      <dt>Container</dt>
                      <dd>{container_name}</dd>
                      <dt>Process</dt>
                      <dd>{process_name_str}</dd>
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
            <td id="_log{log_i}_pid{pid}_command_container"></td>
        </tr>
    </table>

  </div>
</div>`;

let _LOG_PROCESS_COMMAND_TEMPLATE = `
<div class="input-group" style="margin-top: 12px">
  <span class="input-group-addon" style="background-color: {command_color}"><strong>Command</strong></span>
    <div class="_proc_command">{command}</div>
</div>
`;

let _LOG_PROCESS_FILTER_DEFAULT_VALUES = {
  pcpu: [0, 100],
  pmem: [0, 100],
  nthreads: [0, 50],
  command: "",
  leaves_only: true,
  condense_plots: true,
  coverage: [0.1, 1.0]
};

let _LOG_PROCESS_FILTER_VALUES = {};

let _LOG_PROGRESS_PROC_GROUPS = {};


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
    // ---
    // let number_of_processes = 0;
    let number_of_processes = parseInt($('#_logs_tab_processes_num_processes').html()) || 0;
    // filters
    let [min_pcpu, max_pcpu] = _LOG_PROCESS_FILTER_VALUES['pcpu'] || _LOG_PROCESS_FILTER_DEFAULT_VALUES['pcpu'];
    let [min_pmem, max_pmem] = _LOG_PROCESS_FILTER_VALUES['pmem'] || _LOG_PROCESS_FILTER_DEFAULT_VALUES['pmem'];
    let [min_nthreads, max_nthreads] = _LOG_PROCESS_FILTER_VALUES['nthreads'] || _LOG_PROCESS_FILTER_DEFAULT_VALUES['nthreads'];
    let command_filter = _LOG_PROCESS_FILTER_DEFAULT_VALUES['command'];
    if (_LOG_PROCESS_FILTER_VALUES.hasOwnProperty('command') && _LOG_PROCESS_FILTER_VALUES['command'].length > 0)
        command_filter = _LOG_PROCESS_FILTER_VALUES['command'];
    let condense_plots = _LOG_PROCESS_FILTER_DEFAULT_VALUES['condense_plots'];
    if (_LOG_PROCESS_FILTER_VALUES['condense_plots'] !== undefined)
        condense_plots = _LOG_PROCESS_FILTER_VALUES['condense_plots'];
    let [min_coverage, max_coverage] = _LOG_PROCESS_FILTER_VALUES['coverage'] || _LOG_PROCESS_FILTER_DEFAULT_VALUES['coverage'];
    let leaves_only = _LOG_PROCESS_FILTER_DEFAULT_VALUES['leaves_only'];
    if (_LOG_PROCESS_FILTER_VALUES['leaves_only'] !== undefined)
        leaves_only = _LOG_PROCESS_FILTER_VALUES['leaves_only'];
    // aggregate data
    let data = {};
    let max_n_points = 0;
    let parents_PID = new Set();
    log_data.forEach(function(proc){
        let PID = proc['pid'];
        let process_name = _find_process_name(proc['command']);
        let charts = {
            'pcpu': null,
            'pmem': null,
            'nthreads': null,
            'command': null
        };
        let append = false;
        if (condense_plots && _LOG_PROGRESS_PROC_GROUPS.hasOwnProperty(process_name)) {
            charts = _LOG_PROGRESS_PROC_GROUPS[process_name];
            append = true;
        }
        if (!data.hasOwnProperty(PID)) {
            data[PID] = {
                log: key,
                log_i: log_i,
                panel_color: condense_plots? '' : 'rgba({0}, 0.4)'.format(color),
                command_color: condense_plots ? 'rgba({0}, 0.5)'.format(color) : '#eee',
                container_name: log_containers[proc['container']],
                process_name: process_name,
                process_name_str: process_name? '<strong>'+process_name+'</strong>' : '(check command below)',
                container: proc['container'],
                ppid: proc['ppid'],
                pid: PID,
                command: _format_command(proc['command']),
                pcpu: [],
                cputime: [],
                pmem: [],
                mem: [],
                nthreads: [],
                is_leaf: true,
                append: append,
                charts: charts
            };
        }
        parents_PID.add(proc['ppid']);
        let rel_time = parseInt(proc['time'] - start_time);
        // add temporal data
        data[PID].pcpu.push({x: rel_time, y: parseFloat(proc['pcpu'])});
        // data[PID].cputime.push({x: rel_time, y: proc['cputime']});
        data[PID].pmem.push({x: rel_time, y: parseFloat(proc['pmem'])});
        // data[PID].mem.push({x: rel_time, y: proc['mem']});
        data[PID].nthreads.push({x: rel_time, y: parseInt(proc['nthreads'])});
        // update stats
        max_n_points = Math.max(max_n_points, data[PID].pcpu.length);
    });
    // update fields
    for (const [_, proc_data] of Object.entries(data)) {
        proc_data.is_leaf = !parents_PID.has(proc_data.pid);
    }
    // filter processes
    let _filtered_data = {};
    for (const [pid, proc_data] of Object.entries(data)) {
        // 1. cpu usage
        if (proc_data.pcpu.filter(v => v.y >= min_pcpu).length <= 0)
            continue;
        if (proc_data.pcpu.filter(v => v.y >= max_pcpu).length > 0)
            continue;
        // 2. mem usage
        if (proc_data.pmem.filter(v => v.y >= min_pmem).length <= 0)
            continue;
        if (proc_data.pmem.filter(v => v.y >= max_pmem).length > 0)
            continue;
        // 3. threads usage
        if (proc_data.nthreads.filter(v => v.y >= min_nthreads).length <= 0)
            continue;
        if (proc_data.nthreads.filter(v => v.y >= max_nthreads).length > 0)
            continue;
        // 4. command
        if (!proc_data.command.includes(command_filter))
            continue;
        // 5. coverage
        if ((proc_data.pcpu.length / max_n_points) < min_coverage)
            continue;
        if ((proc_data.pcpu.length / max_n_points) > max_coverage)
            continue;
        // 6. leaves only
        if (leaves_only && !proc_data.is_leaf)
            continue;
        // add to filtered
        _filtered_data[pid] = proc_data
    }
    data = _filtered_data;
    // draw each process
    for (const [pid, proc_data] of Object.entries(data)) {
        if (!proc_data.append) {
            $('#_logs_tab_processes').append(
                _LOGS_PROCESS_BLOCK_TEMPLATE.format(proc_data)
            );
            let command_container = $('#_logs_tab_processes #_log{0}_pid{1}_command_container'.format(log_i, pid));
            command_container.append(
                _LOG_PROCESS_COMMAND_TEMPLATE.format(proc_data)
            );
            if (proc_data.process_name){
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name] = {};
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['command'] = command_container;
            }
        }else{
            _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['command'].append(
                _LOG_PROCESS_COMMAND_TEMPLATE.format(proc_data)
            );
        }
        // add CPU canvas to process tab
        let pcpu_dataset = get_chart_dataset({
            label: 'CPU usage (%)',
            data: proc_data.pcpu,
            color: color
        });
        if (!proc_data.append) {
            let cpu_canvas = $('<canvas/>').width('100%').height('200px');
            $('#_logs_tab_processes #_log{0}_cpu_pid{1}_canvas_container'.format(log_i, pid)).append(cpu_canvas);
            // render CPU usage
            let chart = new Chart(cpu_canvas, {
                type: 'line',
                data: {
                    labels: window._DIAGNOSTICS_LOGS_X_RANGE,
                    datasets: [
                        pcpu_dataset
                    ]
                },
                options: {
                    animation: false,
                    scales: {
                        yAxes: [
                            {
                                ticks: {
                                    callback: function (label) {
                                        return label.toFixed(0) + ' %';
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
            if (proc_data.process_name){
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['pcpu'] = chart;
            }
        }else{
            proc_data.charts['pcpu'].data.datasets.push(pcpu_dataset);
            proc_data.charts['pcpu'].update();
        }
        // add RAM canvas to process tab
        let pmem_dataset = get_chart_dataset({
            label: 'RAM usage (%)',
            data: proc_data.pmem,
            color: color
        });
        if (!proc_data.append) {
            let ram_canvas = $('<canvas/>').width('100%').height('200px');
            $('#_logs_tab_processes #_log{0}_ram_pid{1}_canvas_container'.format(log_i, pid)).append(ram_canvas);
            // render RAM usage
            let chart = new Chart(ram_canvas, {
                type: 'line',
                data: {
                    labels: window._DIAGNOSTICS_LOGS_X_RANGE,
                    datasets: [
                        pmem_dataset
                    ]
                },
                options: {
                    animation: false,
                    scales: {
                        yAxes: [
                            {
                                ticks: {
                                    callback: function (label) {
                                        return label.toFixed(0) + ' %';
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
            if (proc_data.process_name) {
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['pmem'] = chart;
            }
        }else{
            proc_data.charts['pmem'].data.datasets.push(pmem_dataset);
            proc_data.charts['pmem'].update();
        }
        // add NTHREADS canvas to process tab
        let nthreads_dataset = get_chart_dataset({
            label: '# Threads',
            data: proc_data.nthreads,
            color: color,
            no_background: true
        });
        if (!proc_data.append) {
            let nthreads_canvas = $('<canvas/>').width('100%').height('200px');
            $('#_logs_tab_processes #_log{0}_nthreads_pid{1}_canvas_container'.format(log_i, pid)).append(nthreads_canvas);
            // render NTHREADS usage
            let chart = new Chart(nthreads_canvas, {
                type: 'line',
                data: {
                    labels: window._DIAGNOSTICS_LOGS_X_RANGE,
                    datasets: [
                        nthreads_dataset
                    ]
                },
                options: {
                    animation: false,
                    scales: {
                        yAxes: [
                            {
                                ticks: {
                                    callback: function (label) {
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
            if (proc_data.process_name) {
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['nthreads'] = chart;
            }
        }else{
            proc_data.charts['nthreads'].data.datasets.push(nthreads_dataset);
            proc_data.charts['nthreads'].update();
        }
        number_of_processes += 1;
    }
    // update processes counter
    $('#_logs_tab_processes_num_processes').html(number_of_processes);
}

function _find_process_name(command) {
    let parts = command.split(' ');
    // ROS node
    for (let i = 0; i < parts.length; i++){
        if (parts[i].startsWith('__name:='))
            return parts[i].slice(8);
    }
    // python script
    for (let i = 0; i < parts.length; i++){
        if (parts[i].startsWith('/usr/bin/python') && i < parts.length - 1)
            return parts[i+1].split('/').slice(-1)[0];
    }
    // ---
    return null;
}

// this gets executed when the tab gains focus
let _tab_processes_on_show = function(){
    let seek = ['/process_stats', '/containers'];
    fetch_log_data(seek, _tab_processes_render_single_log, hidePleaseWait);
};

// this gets executed when the tab loses focus
let _tab_processes_on_hide = function(){
    $('#_logs_tab_processes').empty();
};

$('#_logs_tab_btns a[href="#processes"]').on('shown.bs.tab', _tab_processes_on_show);
$('#_logs_tab_btns a[href="#processes"]').on('hidden.bs.tab', _tab_processes_on_hide);

// configure filter sliders
$("#_logs_tab_processes_filter_pcpu").slider({
    id: "_logs_tab_processes_filter_pcpu",
    min: 0,
    max: 100,
    step: 1,
    range: true,
    value: [0, 100],
    tooltip: 'show',
    ticks: [0, 100],
    ticks_positions: [0, 100],
    ticks_labels: ['0%', '100%'],
    formatter: function(value) {
        // let [min, max] = value.split(',');
		return value[0] + '%  -  ' + value[1] + '%';
	}
});

$("#_logs_tab_processes_filter_pmem").slider({
    id: "_logs_tab_processes_filter_pmem",
    min: 0,
    max: 100,
    step: 1,
    range: true,
    value: [0, 100],
    tooltip: 'show',
    ticks: [0, 100],
    ticks_positions: [0, 100],
    ticks_labels: ['0%', '100%'],
    formatter: function(value) {
		return value[0] + '%  -  ' + value[1] + '%';
	}
});

$("#_logs_tab_processes_filter_nthreads").slider({
    id: "_logs_tab_processes_filter_nthreads",
    min: 1,
    max: 50,
    step: 1,
    range: true,
    value: [1, 50],
    tooltip: 'show',
    ticks: [1, 50],
    ticks_positions: [1, 100],
    ticks_labels: ['1', '50'],
    formatter: function(value) {
		return value[0] + '  -  ' + value[1];
	}
});


$('#_logs_tab_processes_filter_apply').on('click', function(){
    showPleaseWait();
    setTimeout(function(){
        let filters = {
          pcpu: $('input#_logs_tab_processes_filter_pcpu').val().split(','),
          pmem: $('input#_logs_tab_processes_filter_pmem').val().split(','),
          nthreads: $('input#_logs_tab_processes_filter_nthreads').val().split(','),
          command: $('#_logs_tab_processes_filter_command').val(),
          leaves_only: $('#_logs_tab_processes_filter_leaves_only').get(0).checked,
          condense_plots: $('#_logs_tab_processes_filter_condense_plots').get(0).checked,
        };
        // store new filter values
        _LOG_PROCESS_FILTER_VALUES = filters;
        // store in browser
        localStorage.setItem('_LOG_PROCESS_FILTER_VALUES', filters);
        // clear number of processes
        $('#_logs_tab_processes_num_processes').html('--');
        // refresh tab
        refresh_current_tab();
    }, 500);
});

</script>
