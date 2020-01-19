<?php
use \system\classes\Core;
use \system\classes\BlockRenderer;
use \system\packages\ros\ROS;


class DuckietownMsgs_Twist2DStamped extends BlockRenderer{

  static protected $ICON = [
    "class" => "glyphicon",
    "name" => "dashboard"
  ];

  static protected $ARGUMENTS = [
    "topic" => [
      "name" => "ROS Topic",
      "type" => "text",
      "mandatory" => True
    ],
    "fps" => [
      "name" => "Update frequency (Hz)",
      "type" => "numeric",
      "mandatory" => True,
      "default" => 5
    ],
    "max_value" => [
      "name" => "Maximum value",
      "type" => "numeric",
      "mandatory" => True
    ],
    "allow_negative" => [
      "name" => "Allow negative values",
      "type" => "boolean",
      "mandatory" => True,
      "default" => True
    ],
    "unit" => [
      "name" => "Unit",
      "type" => "text",
      "mandatory" => True
    ],
    "field" => [
      "name" => "Message field to show",
      "type" => "text",
      "mandatory" => True
    ]
  ];

  protected static function render( $id, &$args ){
    ?>
    <canvas class="resizable" style="width:100%; padding:6px; padding-bottom:30px"></canvas>

    <table style="width:100%; height:10px; position:relative; top:-30px">
      <tr>
        <td style="width:35%" class="text-center">
          0.0
        </td>
        <td style="width:30%" class="text-center">
          <span style="position:relative; top:-20px">
            <?php echo $args['unit'] ?>
          </span>
        </td>
        <td style="width:35%" class="text-center">
          <?php echo sprintf("%.1f", $args['max_value']) ?>
        </td>
      </tr>
    </table>

    <script type="text/javascript">

    $( document ).on("<?php echo ROS::$ROSBRIDGE_CONNECTED ?>", function(evt){
      // Subscribe to the given topic
      subscriber = new ROSLIB.Topic({
        ros : window.ros,
        name : '<?php echo $args['topic'] ?>',
        messageType : 'duckietown_msgs/Twist2DStamped',
        queue_size : 1,
        throttle_rate : <?php echo 1000/$args['fps'] ?>
      });

      chart_config = {
        type: 'pie',
        data: {
          datasets: [{
            data: [ 0.5, 0.0, 0.0, 0.5 ],
            backgroundColor: [
              window.chartColors.white,
              window.chartColors.green,
              window.chartColors.green,
              window.chartColors.white
            ]
          }]
        },
        options: {
          cutoutPercentage: 50,
          rotation: -Math.PI,
          circumference: Math.PI,
          tooltips: {
            enabled: false
          },
          maintainAspectRatio: false
        }
      };
      // create chart obj
      ctx = $("#<?php echo $id ?> .block_renderer_container canvas")[0].getContext('2d');
      chart = new Chart(ctx, chart_config);
      window.mission_control_page_blocks_data['<?php echo $id ?>'] = {
        chart: chart,
        config: chart_config,
        allow_negative: <?php echo $args['allow_negative']? 'true' : 'false' ?>
      };

      subscriber.subscribe(function(message) {

        // TODO: remove
        // console.log('got info about speed');

        var max_speed = <?php echo $args['max_value'] ?>;
        cur_speed = message["<?php echo $args['field'] ?>"];
        speed_sign = Math.sign(cur_speed);
        cur_speed = Math.abs(cur_speed);
        speed_norm = Math.min(cur_speed, max_speed) / max_speed;
        // speed_norm = Math.min( Math.max( speed_norm, 0.0 ), 1.0 );
        // get chart
        chart_desc = window.mission_control_page_blocks_data['<?php echo $id ?>'];
        chart = chart_desc.chart;
        config = chart_desc.config;
        // update values
        if( chart_desc.allow_negative ){
          if( speed_sign == -1 ){
            config.data.datasets[0].data[0] = 0.5;
            config.data.datasets[0].data[1] = 0.0;
            config.data.datasets[0].data[2] = speed_norm/2.0;
            config.data.datasets[0].data[3] = 0.5 - speed_norm/2.0;
          }else{
            config.data.datasets[0].data[0] = 0.5 - speed_norm/2.0;
            config.data.datasets[0].data[1] = speed_norm/2.0;
            config.data.datasets[0].data[2] = 0.0;
            config.data.datasets[0].data[3] = 0.5;
          }
        }else{
          config.data.datasets[0].data[0] = 0.0;
          config.data.datasets[0].data[1] = speed_norm;
          config.data.datasets[0].data[2] = 0.0;
          config.data.datasets[0].data[3] = 1.0-speed_norm;
        }
        // refresh chart
        // chart.resize( chart.render, true );
        chart.update();

        // chart.resize( chart.render, true );

      });
    });

    </script>

    <?php
  }
}
?>
