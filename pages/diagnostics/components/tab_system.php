<?php
_logs_print_table_structure();
?>

<div id="_logs_tab_system">
</div>


<script type="text/javascript">

var _LOGS_SYS_BLOCK_TEMPLATE = `
<div class="panel panel-default">
  <div class="panel-heading"></div>
  <div class="panel-body">
    Panel content
  </div>
</div>`;

// this gets executed when the tab gains focus
let on_show = function(){
    // render log info
    get_listed_logs();


};

// this gets executed when the tab loses focus
let on_hide = function(){
    console.log('HERE');
};

$('#_logs_tab_btns a[href="#system"]').on('shown.bs.tab', on_show);
$('#_logs_tab_btns a[href="#system"]').on('hidden.bs.tab', on_hide);
</script>
