<?php
use \system\classes\Core as Core;
use \system\classes\BlockRenderer as BlockRenderer;

class DuckietownMsgs_WheelsCmdStamped extends BlockRenderer{

  static protected $ICON = [
    "class" => "fa",
    "name" => "exchange"
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
    ]
  ];

  protected static function render( $id, &$args ){
    ?>
    <canvas class="resizable" style="width:100%; height:95%; padding:6px 16px"></canvas>

    <script type="text/javascript">
    $( document ).on( "ROSBridge_connected", function(evt){
      // Subscribe to the given topic
      subscriber = new ROSLIB.Topic({
        ros : window.ros,
        name : '<?php echo $args['topic'] ?>',
        messageType : 'duckietown_msgs/WheelsCmdStamped',
        queue_size : 1,
        throttle_rate : <?php echo 1000/$args['fps'] ?>
      });

      time_horizon_secs = 20
      color = Chart.helpers.color;
      chart_config = {
        type: 'line',
        data: {
          labels: range(time_horizon_secs-1,0,1),
          datasets: [{
            label: 'Left motor',
            backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
            borderColor: window.chartColors.red,
            fill: true,
            data: new Array(time_horizon_secs).fill(0)
          }, {
            label: 'Right motor',
            backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
            borderColor: window.chartColors.blue,
            fill: true,
            data: new Array(time_horizon_secs).fill(0)
          }]
        },
        options: {
          scales: {
            xAxes: [{
              scaleLabel: {
                display: false
              }
            }],
            yAxes: [{
              scaleLabel: {
                display: true,
                labelString: 'Speed (m/s)'
              },
              ticks: {
                suggestedMin: -<?php echo $args['max_value'] ?>,
                suggestedMax: <?php echo $args['max_value'] ?>
              }
            }]
          },
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
        config: chart_config
      };

      subscriber.subscribe(function(message) {
        // get chart
        chart_desc = window.mission_control_page_blocks_data['<?php echo $id ?>'];
        chart = chart_desc.chart;
        config = chart_desc.config;
        // cut the time horizon to `time_horizon_secs` points
        config.data.datasets[0].data.shift();
        config.data.datasets[1].data.shift();
        // add new Y
        config.data.datasets[0].data.push(
          message.vel_left
        );
        config.data.datasets[1].data.push(
          message.vel_right
        );
        // refresh chart
        chart.update();
      });
    });
    </script>
    <?php
  }//render

}//DuckietownMsgs_WheelsCmdStamped
?>
