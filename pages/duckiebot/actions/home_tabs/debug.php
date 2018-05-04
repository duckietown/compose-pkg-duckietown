<?php
use \system\packages\duckietown\Duckietown;

$wtd_status_color_map = array(
    'passed' => 'success',
    'skipped' => 'warning',
    'failed' => 'danger'
);

$what_the_duck = Duckietown::getDuckiebotLatestWhatTheDuck($duckiebotName);

$wtd = $what_the_duck['duckiebot']; //TODO: add laptops

$tests_stats = array(
    'passed' => 0,
    'skipped' => 0,
    'failed' => 0
);
$num_total_tests = 0;
foreach ($wtd as $test) {
    $tests_stats[$test['status']] += 1;
    $num_total_tests += 1;
}
?>
<table style="width:100%; border:1px solid lightgray">
    <tr>
        <td class="text-center" style="width:160px; border-right:1px solid lightgray; padding:2px 4px">
            <h5 class="text-bold">What The Duck</h5>
        </td>
        <td class="text-right" style="width:auto; padding:0 20px">
            <span style="color:green">
                <span class="text-bold">
                    <?php echo $tests_stats['passed'] ?>
                </span>
                Test<?php echo ($tests_stats['passed'] == 1)? '' : 's' ?> passed
            </span>
            &nbsp;|&nbsp;
            <span style="color:#cece44">
                <span class="text-bold">
                    <?php echo $tests_stats['skipped'] ?>
                </span>
                Test<?php echo ($tests_stats['skipped'] == 1)? '' : 's' ?> skipped
            </span>
            &nbsp;|&nbsp;
            <span style="color:red">
                <span class="text-bold">
                    <?php echo $tests_stats['failed'] ?>
                </span>
                Test<?php echo ($tests_stats['failed'] == 1)? '' : 's' ?> failed
            </span>
        </td>
    </tr>
</table>
<div style="padding:20px">
    <?php
    if( $num_total_tests > 0 ){
        ?>
        <table class="table">
            <tr>
                <td class="col-md-4 text-bold">
                    Test
                </td>
                <td class="col-md-6 text-bold">
                    Result
                </td>
                <td class="col-md-2 text-bold">
                    Executed
                </td>
            </tr>
            <?php
            $test_num = 0;
            foreach ($wtd as $test) {
                ?>
                <tr>
                    <td class="col-md-4">
                        <?php echo $test['test_name'] ?>
                    </td>
                    <td class="col-md-6 <?php echo $wtd_status_color_map[$test['status']]; ?>">
                        <?php
                        switch( $test['status'] ){
                            case 'passed':
                                echo 'Passed';
                                break;
                            case 'skipped':
                            case 'failed':
                                echo $test['out_short'];
                                break;
                            default:
                                break;
                        }
                        ?>
                    </td>
                    <td class="col-md-2">
                        <span id="<?php echo $test['upload_event_id']; ?>_last_execution_<?php echo $test_num; ?>">
                            ...
                        </span>
                    </td>
                </tr>
                <?php
                $test_num += 1;
            }
            ?>
        </table>

    <?php
}else{
    ?>
    <h4 class="text-center">
        No tests available
    </h4>
    <?php
}
?>
</div>
