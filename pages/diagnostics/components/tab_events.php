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
let on_show = function(){
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
let on_hide = function(){
    $('#_logs_tab_events').empty();
};

$('#_logs_tab_btns a[href="#events"]').on('shown.bs.tab', on_show);
$('#_logs_tab_btns a[href="#events"]').on('hidden.bs.tab', on_hide);
</script>
