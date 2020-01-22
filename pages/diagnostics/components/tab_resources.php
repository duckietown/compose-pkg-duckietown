<?php
_logs_print_table_structure();

/*
Keys used from Log:

    general:
        time
        duration

    resources_stats:
        time
        memory.pmem
        cpu.pcpu

*/
?>

<hr>

<div id="_logs_tab_resources">
    <br/>
    <h4>CPU Usage</h4>
    <div id="_logs_tab_resources_cpu">
    </div>
    <br/><br/>
    <h4>Ram Usage</h4>
    <div id="_logs_tab_resources_ram">
    </div>
</div>


<script type="text/javascript">

function _tab_resources_render_single_log(key, seek){
    let color = get_log_info(key, '_color');
    let log_data = window._DIAGNOSTICS_LOGS_DATA[key][seek];
    let start_time = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].time;
    let duration = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].duration;
    // create datasets
    let pcpu = log_data.map(function(e){return {x: parseInt(e.time - start_time), y: e.cpu.pcpu}});
    let pmem = log_data.map(function(e){return {x: parseInt(e.time - start_time), y: e.memory.pmem}});
    // ---
    // add CPU canvas to tab
    let cpu_canvas = $('<canvas/>').width('100%').height('200px');
    $('#_logs_tab_resources #_logs_tab_resources_cpu').append(cpu_canvas);
    // render CPU usage
    new Chart(cpu_canvas, {
        type: 'line',
        data: {
            labels: range(0, duration),
            datasets: [
                get_chart_dataset({
                    canvas: cpu_canvas,
                    label: 'CPU usage (%)',
                    data: pcpu,
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
    // add RAM canvas to tab
    let ram_canvas = $('<canvas/>').width('100%').height('200px');
    $('#_logs_tab_resources #_logs_tab_resources_ram').append(ram_canvas);
    // render RAM usage
    new Chart(ram_canvas, {
        type: 'line',
        data: {
            labels: range(0, duration),
            datasets: [
                get_chart_dataset({
                    canvas: ram_canvas,
                    label: 'RAM usage (%)',
                    data: pmem,
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

// this gets executed when the tab gains focus
let _tab_resources_on_show = function(){
    let seek = '/resources_stats';
    fetch_log_data(seek, _tab_resources_render_single_log);
};

// this gets executed when the tab loses focus
let _tab_resources_on_hide = function(){
    $('#_logs_tab_resources #_logs_tab_resources_cpu').empty();
    $('#_logs_tab_resources #_logs_tab_resources_ram').empty();
};

$('#_logs_tab_btns a[href="#resources"]').on('shown.bs.tab', _tab_resources_on_show);
$('#_logs_tab_btns a[href="#resources"]').on('hidden.bs.tab', _tab_resources_on_hide);
</script>
