<?php
_logs_print_table_structure();
?>

<hr>

<div id="_logs_tab_events">
</div>


<style type="text/css">
//
</style>


<script type="text/javascript">
// this gets executed when the tab gains focus
let _tab_events_on_show = function(){
    // get logs list
    let tab_data = table_to_object('#_main_table');
    // render logs info
    tab_data.forEach(function(tab_row){
        let key = tab_row['_key'];
        let log_data = window._DIAGNOSTICS_LOGS_DATA[key];
        //
    });
};

// this gets executed when the tab loses focus
let _tab_events_on_hide = function(){
    $('#_logs_tab_events').empty();
};

$('#_logs_tab_btns a[href="#events"]').on('shown.bs.tab', _tab_events_on_show);
$('#_logs_tab_btns a[href="#events"]').on('hidden.bs.tab', _tab_events_on_hide);
</script>
