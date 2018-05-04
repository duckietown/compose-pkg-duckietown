<?php
use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Database;
use \system\packages\duckietown\Duckietown;

require_once $GLOBALS['__SYSTEM__DIR__'].'templates/tableviewers/TableViewer.php';
use \system\templates\tableviewers\TableViewer as TableViewer;

// define table layout
$table = array(
    'style' => 'table-striped table-hover',
    'layout' => array(
        'name' => array(
            'type' => 'text',
            'show' => true,
            'width' => 'md-6',
            'align' => 'left',
            'translation' => 'Mission name',
            'editable' => false
        )
    ),
    'actions' => array(
        '_width' => 'md-5',
        'open' => array(
            'type' => 'default',
            'glyphicon' => 'fullscreen',
            'tooltip' => 'Open mission',
            'text' => 'Open',
            'function' => array(
                'type' => 'custom',
                'custom_html' => 'onclick="_open_mission(this)"',
                'arguments' => array('name')
            )
        ),
        '_s1' => ['type' => 'separator'],
        'delete' => array(
            'type' => 'warning',
            'glyphicon' => 'trash',
            'tooltip' => 'Delete mission',
            'text' => 'Delete',
            'function' => array(
                'type' => 'custom',
                'custom_html' => 'onclick="_delete_mission(this)"',
                'arguments' => array('name')
            )
        )
    ),
    'features' => array(
        '_counter_column',
        '_actions_column'
    )
);

// open database of missions for current duckiebot
$db_name = sprintf("veh_%s_mission", $duckiebotName);
$missions_db = new Database('duckietown', $db_name);
$list_missions = $missions_db->list_keys();

// create list of mission records for the table renderer
$missions = [];
foreach( $list_missions as $mission_name ){
    $missions_record = [
        'name' => $mission_name
    ];
    array_push( $missions, $missions_record );
}

// prepare data for the table viewer
$res = array(
    'size' => sizeof( $missions ),
    'total' => sizeof( $missions ),
    'data' => $missions
);

// this is where the magic happens
TableViewer::generateTableViewer( Configuration::$PAGE, $res, [], $table );

?>

<script type="text/javascript">

    function _open_mission( btn ){
        redirectTo(
            'duckiebot',
            'mission-control',
            null, null,
            {
                veh:'<?php echo $duckiebotName ?>',
                mission: $(btn).data('name')
            }
        );
    }//_open_mission

</script>
