<?php
use \system\classes\Core;
?>

<div id="ros-section-placeholder" class="text-center">
    <img src="<?php echo Core::getImageURL('loading_blue.gif') ?>" style="width:32px; height:32px; margin-top:60px">
</div>
<div id="ros-section-container" class="text-center" style="display:none">
    <nav class="navbar navbar-default" role="navigation" style="width:100%">
        <div class="container-fluid" style="padding-left:0; padding-right:0">
            <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
                <table style="width:100%; height:50px">
                    <tr>
                        <td class="text-center" width="160px" style="border-right:1px solid lightgray">
                            <h5 class="text-bold">ROS Core</h5>
                        </td>
                        <td id="ros_core_status" class="text-left" style="padding:0 10px"></td>
                    </tr>
                </table>
            </div>
        </div>
    </nav>

    <table id="ros_details" style="width:100%; display:none">
        <tr>
            <td class="col-md-6" style="padding:0; vertical-align:top">
                <nav class="navbar navbar-default" role="navigation" style="width:100%; padding:0">
                    <div class="container-fluid" style="padding-left:0; padding-right:0">
                        <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

                            <table style="width:100%; border-bottom:1px solid lightgray">
                                <tr>
                                    <td class="text-center" style="width:160px; border-right:1px solid lightgray; padding:2px 4px">
                                        <h5 class="text-bold">ROS Nodes</h5>
                                    </td>
                                    <td class="text-right" style="width:auto; padding:0 20px"></td>
                                </tr>
                            </table>

                            <div class="text-left" style="padding:8px 20px">
                                <table id="ros_nodes_table" class="table-condensed">
                                    <tbody></tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </nav>
            </td>
            <td>&nbsp;</td>
            <td class="col-md-6" style="padding:0; vertical-align:top">
                <nav class="navbar navbar-default" role="navigation" style="width:100%; padding:0">
                    <div class="container-fluid" style="padding-left:0; padding-right:0">

                        <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

                            <table style="width:100%; border-bottom:1px solid lightgray">
                                <tr>
                                    <td class="text-center" style="width:160px; border-right:1px solid lightgray; padding:2px 4px">
                                        <h5 class="text-bold">ROS Topics</h5>
                                    </td>
                                    <td class="text-right" style="width:auto; padding:0 20px"></td>
                                </tr>
                            </table>

                            <div class="text-left" style="padding:8px 20px">
                                <table id="ros_topics_table" class="table-condensed">
                                    <tbody></tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </nav>
            </td>
        </tr>
    </table>
</div>
