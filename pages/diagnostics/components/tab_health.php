<?php
_logs_print_table_structure();

/*
Keys used from Log:

    health:
        status
        temp
        volts
            sdram_i
            core
        time

*/
?>

<hr>

<div id="_logs_tab_health">
    <br/>
    <h4>Overall status</h4>
    <div id="_logs_tab_health_status">
    </div>
    <br/><br/>
    <h4>CPU Temperature</h4>
    <div id="_logs_tab_health_cpu_temp">
    </div>
    <br/><br/>
    <h4>Voltage</h4>
    <div id="_logs_tab_health_voltage">
    </div>
</div>


<script type="text/javascript">

function _tab_health_render_single_log(key, seek){
    let color = get_log_info(key, '_color');
    let log_data = window._DIAGNOSTICS_LOGS_DATA[key][seek];
    let start_time = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].time;
    let duration = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].duration;
    // create datasets
    let status_to_val = {'ok': 0, 'warning': 1, 'error': 2};
    let val_to_status = ['ok', 'warning', 'error'];
    let status = log_data.map(function(e){return {
        x: parseInt(e.time - start_time),
        y: status_to_val[e.status]
    }});
    let temp = log_data.map(function(e){return {
        x: parseInt(e.time - start_time),
        y: parseFloat(e.temp.slice(0,-2))
    }});
    let cpu_volt = log_data.map(function(e){return {
        x: parseInt(e.time - start_time),
        y: parseFloat(e.volts.core.slice(0,-1))
    }});
    let ram_volt = log_data.map(function(e){return {
        x: parseInt(e.time - start_time),
        y: parseFloat(e.volts.sdram_i.slice(0,-1))
    }});
    // ---
    // add device status canvas to tab
    let status_canvas = $('<canvas/>').width('100%').height('200px');
    $('#_logs_tab_health #_logs_tab_health_status').append(status_canvas);
    // render CPU usage
    new Chart(status_canvas, {
        type: 'line',
        data: {
            labels: range(0, duration),
            datasets: [
                get_chart_dataset({
                    canvas: status_canvas,
                    label: "Status",
                    data: status,
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
                                return val_to_status[label];
                            },
                            min: -1,
                            max: 3
                        },
                        gridLines: {
                            display: false
                        }
                    }
                ]
            }
        }
    });
    // add CPU temp canvas to tab
    let temp_canvas = $('<canvas/>').width('100%').height('200px');
    $('#_logs_tab_health #_logs_tab_health_cpu_temp').append(temp_canvas);
    // render CPU usage
    new Chart(temp_canvas, {
        type: 'line',
        data: {
            labels: range(0, Math.max(duration, ...temp.map(e => e.x))),
            datasets: [
                get_chart_dataset({
                    canvas: temp_canvas,
                    label: "CPU Temp \'C)",
                    data: temp,
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
                                return label.toFixed(0)+' \'C';
                            },
                            min: 30,
                            max: 70
                        },
                        gridLines: {
                            display: false
                        }
                    }
                ]
            }
        }
    });
    // add volts canvas to tab
    let cpu_volt_canvas = $('<canvas/>').width('100%').height('200px');
    $('#_logs_tab_health #_logs_tab_health_voltage').append(cpu_volt_canvas);
    // render RAM usage
    new Chart(cpu_volt_canvas, {
        type: 'line',
        data: {
            labels: range(0, duration),
            datasets: [
                get_chart_dataset({
                    canvas: cpu_volt_canvas,
                    label: 'CPU Volt (V)',
                    data: cpu_volt,
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
                                return label.toFixed(1)+' V';
                            },
                            min: 0.6,
                            max: 1.4
                        },
                        gridLines: {
                            display: false
                        }
                    }
                ]
            }
        }
    });
    // add volts canvas to tab
    let ram_volt_canvas = $('<canvas/>').width('100%').height('200px');
    $('#_logs_tab_health #_logs_tab_health_voltage').append(ram_volt_canvas);
    // render RAM usage
    new Chart(ram_volt_canvas, {
        type: 'line',
        data: {
            labels: range(0, duration),
            datasets: [
                get_chart_dataset({
                    canvas: ram_volt_canvas,
                    label: 'RAM Volt (V)',
                    data: ram_volt,
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
                                return label.toFixed(1)+' V';
                            },
                            min: 0.6,
                            max: 1.4
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

// this gets executed when the tab gains focus
let _tab_health_on_show = function(){
    let seek = '/health';
    fetch_log_data(seek, _tab_health_render_single_log);
};

// this gets executed when the tab loses focus
let _tab_health_on_hide = function(){
    $('#_logs_tab_health #_logs_tab_health_status').empty();
    $('#_logs_tab_health #_logs_tab_health_cpu_temp').empty();
    $('#_logs_tab_health #_logs_tab_health_voltage').empty();
};

$('#_logs_tab_btns a[href="#health"]').on('shown.bs.tab', _tab_health_on_show);
$('#_logs_tab_btns a[href="#health"]').on('hidden.bs.tab', _tab_health_on_hide);
</script>
