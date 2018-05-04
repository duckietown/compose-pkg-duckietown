<?php
use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Database;
use \system\packages\duckietown\Duckietown;
?>

<table>
    <tr>
        <td class="text-left" style="width:200px">
            <h4>Information</h4>
            <hr>
            <table id="duckiebot_general_info">
                <tr>
                    <td style="width:110px">
                         <bold>Duckiebot:<bold>
                    </td>
                    <td>
                         <?php echo $duckiebotName ?>
                    </td>
                </tr>
                <tr>
                    <td>
                         <bold>Owner ID:<bold>
                    </td>
                    <td>
                         <?php echo $duckiebotOwner ?>
                    </td>
                </tr>
                <tr>
                    <td>
                         <bold>Owner Name:<bold>
                    </td>
                    <td id="duckiebot_owner">
                        <img src="<?php echo Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:18px; height:18px;">
                    </td>
                </tr>
                <tr>
                    <td>
                         <bold>Status:<bold>
                    </td>
                    <td id="duckiebot_status">
                        <img src="<?php echo Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:18px; height:18px;">
                    </td>
                </tr>
            </table>
        </td>

        <td class="text-center" style="width:358px">
            <img src="<?php echo Core::getImageURL('duckiebot.gif'); ?>" style="width:160px" />
        </td>

        <td class="text-right" style="width:200px">
            <h4>Configuration</h4>
            <hr>
            <div id="configuration-section-placeholder" style="height:108px">
                <img src="<?php echo Configuration::$BASE_URL ?>images/loading_blue.gif" style="width:32px; height:32px; margin-top:32px; margin-right:90px">
            </div>
            <div id="configuration-section-container" style="display:none">
                <ul class="nav nav-pills" role="tablist">
                    <?php
                    $configurations = [
                        'w' => ['wifi', '5GHz WiFi'],
                        'j' => ['gamepad', 'Joystick'],
                        'd' => ['hdd-o', '32GB USB']
                    ];
                    foreach ($configurations as $conf => $desc) {
                        ?>
                        <li role="presentation" class="configuration-indicator" style="width:100%; float:right">
                            <a id="configuration-w" class="color-default" style="height:34px; margin-bottom:2px; padding:8px 12px">
                                <table>
                                    <tr>
                                        <td>
                                            <h4 style="margin:0; padding:0">
                                                <i class="fa fa-<?php echo $desc[0] ?>" aria-hidden="true"></i>
                                            </h4>
                                        </td>
                                        <td>
                                            <h5 style="margin:0; padding:0">
                                                &nbsp;
                                                DB-17<?php echo $conf ?>
                                                <span style="font-weight:normal">
                                                    &nbsp;
                                                    (<?php echo $desc[1] ?>)
                                                </span>
                                            </h5>
                                        </td>
                                    </tr>
                                </table>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </td>
    </tr>
</table>
