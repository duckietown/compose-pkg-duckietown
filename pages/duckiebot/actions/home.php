<?php

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Database;
use \system\packages\duckietown\Duckietown;

$duckiebotName = $GLOBALS['_duckietown_duckiebot_veh'];
?>

<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>Duckiebot - <?php echo $duckiebotName ?></h2>
			</td>
		</tr>

	</table>

	<?php
	if(isset($_GET['lst'])){
		$qs = ( (isset($_GET['lst']))? base64_decode(urldecode($_GET['lst'])) : '' );
		?>
		<a role="button"
			href="<?php echo Configuration::$BASE ?>duckiefleet<?php echo ( (strlen($qs) > 0)? '?'.$qs : '' ) ?>"
			class="btn btn-info" data-toggle="modal"
			style="margin-bottom:30px">
				<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
				&nbsp; Go back to the list
		</a>
		<?php
	}
	?>

	<?php
	$duckiebotOwner = Duckietown::getDuckiebotOwner($duckiebotName);
	?>

	<nav class="navbar navbar-default" role="navigation" style="width:100%">
		<div class="container-fluid" style="padding-left:0; padding-right:0">
			<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
				<table style="width:100%; height:50px">
					<tr>
						<td class="text-center" style="border-right:1px solid lightgray; width:170px; vertical-align:top; padding:6px">
							<ul class="nav nav-pills nav-stacked text-left" role="tablist">
								<li role="presentation" class="active">
									<a href="#tab_info" id="sel_info" aria-controls="tab_info" role="tab" data-toggle="tab">
										<i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp;
										Info
									</a>
								</li>
								<li role="presentation">
									<a href="#tab_missions" id="sel_missions" aria-controls="tab_missions" role="tab" data-toggle="tab">
										<i class="fa fa-object-ungroup" aria-hidden="true"></i>&nbsp;
										Missions
									</a>
								</li>
								<li role="presentation">
									<a href="#tab_network" id="sel_network" aria-controls="tab_network" role="tab" data-toggle="tab">
										<i class="fa fa-sitemap" aria-hidden="true"></i>&nbsp;
										Network
									</a>
								</li>
								<li role="presentation">
									<a href="#tab_storage" id="sel_storage" aria-controls="tab_storage" role="tab" data-toggle="tab">
										<span class="glyphicon glyphicon-hdd" aria-hidden="true"></span>&nbsp;
										Storage
									</a>
								</li>
								<li role="presentation">
									<a href="#tab_ros" id="sel_ros" aria-controls="tab_ros" role="tab" data-toggle="tab">
										<i class="fa fa-th" aria-hidden="true"></i>&nbsp;
										ROS
									</a>
								</li>
								<li role="presentation">
									<a href="#tab_processes" id="sel_processes" aria-controls="tab_processes" role="tab" data-toggle="tab">
										<i class="fa fa-desktop" aria-hidden="true"></i>&nbsp;
										Processes
									</a>
								</li>
								<li role="presentation">
									<a href="#tab_debug" id="sel_debug" aria-controls="tab_debug" role="tab" data-toggle="tab">
										<i class="fa fa-bug" aria-hidden="true"></i>&nbsp;
										Debug
									</a>
								</li>
							</ul>
						</td>
						<td class="text-left" style="padding:30px 20px; width:800px; vertical-align:top">
							<div class="tab-content">

								<!-- Begin Tab: Info -->
								<div role="tabpanel" class="tab-pane active text-center" id="tab_info">
									<?php include __DIR__.'/home_tabs/info.php' ?>
								</div>
								<!-- End Tab: Info -->


								<!-- Begin Tab: Missions -->
								<div role="tabpanel" class="tab-pane" id="tab_missions">
									<?php include __DIR__.'/home_tabs/missions.php' ?>
								</div>
								<!-- End Tab: Missions -->


								<!-- Begin Tab: Network -->
								<div role="tabpanel" class="tab-pane" id="tab_network">
									<?php include __DIR__.'/home_tabs/network.php' ?>
								</div>
								<!-- End Tab: Network -->


								<!-- Begin Tab: Storage -->
								<div role="tabpanel" class="tab-pane" id="tab_storage">
									<?php include __DIR__.'/home_tabs/storage.php' ?>
								</div>
								<!-- End Tab: Storage -->


								<!-- Begin Tab: ROS -->
								<div role="tabpanel" class="tab-pane" id="tab_ros">
									<?php include __DIR__.'/home_tabs/ros.php' ?>
								</div>
								<!-- End Tab: ROS -->


								<!-- Begin Tab: Processes -->
								<div role="tabpanel" class="tab-pane" id="tab_processes">
									<?php include __DIR__.'/home_tabs/processes.php' ?>
								</div>
								<!-- End Tab: Processes -->


								<!-- Begin Tab: Debug -->
								<div role="tabpanel" class="tab-pane" id="tab_debug">
									<?php include __DIR__.'/home_tabs/debug.php' ?>
								</div>
								<!-- End Tab: Debug -->

							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</nav>

</div>


<script type="text/javascript">

	window.duckiebot_online = undefined;
	window.duckiebot_page_tabs = {
		'info' : {},
		'missions' : {},
		'network' : {},
		'storage' : {},
		'ros' : {},
		'processes' : {},
		'debug' : {}
	};
	var network_usage_num_points = 20;

	var _datetime_format = 'YYYY-MM-DD HH:mm:ss.SSS';

	var _last_execution_divs = [
		<?php
		$test_num = 0;
		$wtd = $what_the_duck['duckiebot']; //TODO: add laptops
		foreach ($wtd as $test) {
			echo '{ "id" : "'.$test['upload_event_id'].'_last_execution_'.$test_num.'", "datetime" : "'.$test['upload_event_date'].'"}, ';
			$test_num += 1;
		}
		?>
	];


	$(document).ready( function(){
		// Connecting to ROS
		// -----------------
		window.ros = new ROSLIB.Ros({
			url : "ws://<?php echo str_replace('/', '', str_replace('http://', '', Configuration::$BASE_URL) ); ?>:42003"
		});
		ros.on('connection', function() {
			console.log('Connected to websocket server.');
		});
		ros.on('error', function(error) {
			console.log('Error connecting to websocket server: ', error);
		});
		ros.on('close', function() {
			console.log('Connection to websocket server closed.');
		});
	});

	function update_section( section_id, html ){
		container = $('#'+section_id+'-section-container');
		placeholder = $('#'+section_id+'-section-placeholder');
		// turn on the indicators
		container.html( html );
		// hide the placeholder and show the indicators
		placeholder.css('display', 'none');
		container.css('display', '');
	}

	function duckiebot_configuration_callback(result){
		container = $('#configuration-section-container');
		placeholder = $('#configuration-section-placeholder');
		// turn on the indicators
		$.each(result.data.configuration, function(c) {
			active = result.data.configuration[c];
			if( active ){
				$('#configuration-section-container #configuration-'+c).removeClass('color-default');
				$('#configuration-section-container #configuration-'+c).addClass('color-warning');
				$('#configuration-section-container #configuration-'+c+' .badge').html('YES');
			}
		});
		// hide the placeholder and show the indicators
		placeholder.css('display', 'none');
		container.css('display', '');
	}

	_network_interface_template =
	`<table style="width:100%">
		<tr>
			<td class="text-center" width="56px" style="border-right:1px solid lightgray">
				<h2 style="margin:44px 6px">
					<i class="fa fa-sitemap" aria-hidden="true"></i>
				</h2>
			</td>
			<td class="text-left" style="padding:0 10px">
				<table>
					<tr>
						<td><bold>Interface:<bold></td>
						<td>&nbsp;&nbsp;<span>{0}</span></td>
					</tr>
					<tr>
						<td><bold>Connected:<bold></td>
						<td>&nbsp;&nbsp;<span>{1}</span></td>
					</tr>
					<tr>
						<td><bold>MAC address:<bold></td>
						<td>&nbsp;&nbsp;<span>{2}</span></td>
					</tr>
					<tr>
						<td><bold>IP address:<bold></td>
						<td>&nbsp;&nbsp;<span>{3}</span></td>
					</tr>
					<tr>
						<td><bold>Subnet mask:<bold></td>
						<td>&nbsp;&nbsp;<span>{4}</span></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<canvas id="{5}" style="width:758px; height:220px; margin-top:8px"></canvas>
	`;

	function duckiebot_network_callback(result){
		container = $('#network-section-container');
		placeholder = $('#network-section-placeholder');
		window.duckiebot_page_tabs['network'] = {
			'devices' : {},
			'subscriber' : null
		};

		// create network interfaces descriptors
		var outer_html = "";
		last_dev = result.data.interfaces[ result.data.interfaces.length-1 ].name
		$.each(result.data.interfaces, function(i) {
			iface = result.data.interfaces[i];
			if( iface.name == 'lo' ){
				// do not show the loopback interface
				return;
			}
			chart_id = "network-usage-chart-{0}-canvas".format(iface.name);
			html = _network_interface_template.format(
				iface.name,
				( iface.connected )? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true" style="color:green; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Online"></span>' : '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color:red; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Offline"></span>',
				iface.mac,
				iface.ip,
				iface.mask,
				chart_id
			);
			if( iface.name != last_dev ){
				html += "<br/><hr/><br/>";
			}
			outer_html += html;
		});
		container.html( outer_html );

		// initialize charts
		var color = Chart.helpers.color;
		$.each(result.data.interfaces, function(i) {
			iface = result.data.interfaces[i];
			if( iface.name == 'lo' ){
				// do not show the loopback interface
				return;
			}
			// create new config
			network_usage_chart_config = {
				type: 'line',
				data: {
					labels: range(network_usage_num_points-1,0,1),
					datasets: [{
						label: 'Inbound network traffic',
						backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
						borderColor: window.chartColors.red,
						fill: true,
						data: new Array(network_usage_num_points).fill(0)
					}, {
						label: 'Outbound network traffic',
						backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
						borderColor: window.chartColors.blue,
						fill: true,
						data: new Array(network_usage_num_points).fill(0)
					}]
				},
				options: {
					scales: {
						xAxes: [{
							scaleLabel: {
								display: true,
								labelString: 'Time (sec)'
							},
							ticks: {
				                beginAtZero: true
				            }
						}],
						yAxes: [{
							scaleLabel: {
								display: true,
								labelString: 'Network Usage (MB/s)'
							},
							ticks: {
				                beginAtZero: true,
								suggestedMax: 1.0
				            }
						}]
					},
				}
			};
			// create chart obj
			chart_id = "network-usage-chart-{0}-canvas".format(iface.name);
			ctx = document.getElementById(chart_id).getContext("2d");
			chart = new Chart(ctx, network_usage_chart_config);
			// store chart obj
			window.duckiebot_page_tabs['network']['devices'][iface.name] = {
				'canvas' : "network-usage-chart-{0}-canvas".format(iface.name),
				'config' : network_usage_chart_config,
				'chart' : chart
			};
		});





		// Subscribing to a Topic
		// ----------------------
		subscriber = new ROSLIB.Topic({
			ros : window.ros,
			name : '/network_monitor/usage',
			messageType : 'duckiebot_monitor/NetworkUsageList'
		});

		subscriber.subscribe(function(message) {
			$.each(message.usage, function(i) {
				iface_name = message.usage[i].dev;
				if( !(iface_name in window.duckiebot_page_tabs['network']['devices']) ){ return; }
				network_usage_chart_desc = window.duckiebot_page_tabs['network']['devices'][iface_name];
				chart = network_usage_chart_desc.chart;
				config = network_usage_chart_desc.config;
				//
				// cut the time horizon to `network_usage_num_points` points
				config.data.datasets[0].data.shift();
				config.data.datasets[1].data.shift();
				// add new Y
				config.data.datasets[0].data.push(
					message.usage[i].ratein_mbps / 8.0
				);
				config.data.datasets[1].data.push(
					message.usage[i].rateout_mbps / 8.0
				);
				// update chart
				chart.update();
			});
		});
		window.duckiebot_page_tabs['network']['subscriber'] = subscriber;

		// hide the placeholder and show the container
		placeholder.css('display', 'none');
		container.css('display', '');
	}



	var _storage_mountpoint_template =
	`<nav class="navbar navbar-default" role="navigation" style="width:310px; margin:0 {6} 36px 0; display:inline-block">
		<div class="container-fluid" style="padding-left:0; padding-right:0">
			<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
				<table class="duckiebot_storage_info" style="width:100%">
					<tr>
						<td class="text-center" width="50px" style="border-right:1px solid lightgray">
							<h2 style="margin:44px 6px">
								<i class="fa fa-hdd-o" aria-hidden="true"></i>
							</h2>
						</td>
						<td class="text-left" style="padding:0 18px">
							<table>
								<tr>
									<td><bold>Mount point:<bold></td>
									<td>&nbsp;&nbsp;<span>{0}</span></td>
								</tr>
								<tr>
									<td><bold>Device:<bold></td>
									<td>&nbsp;&nbsp;<span>{1}</span></td>
								</tr>
								<tr>
									<td><bold>Capacity:<bold></td>
									<td>&nbsp;&nbsp;<span>{7}</span></td>
								</tr>
							</table>

							<div class="progress" style="margin:10px 0 0 0">
								<div class="progress-bar progress-bar-danger" style="width:{2}">{3}</div>
								<div class="progress-bar progress-bar-success" style="width:{4}">{5}</div>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</nav>`;

	function duckiebot_storage_callback(result){
		container = $('#storage-section-container');
		placeholder = $('#storage-section-placeholder');
		// create mountpoints descriptors
		$.each(result.data.mountpoints, function(i) {
			mount = result.data.mountpoints[i];
			var used = Math.floor( mount.used*100 );
			var free = Math.floor( mount.free*100 );
			html = _storage_mountpoint_template.format(
				mount.mountpoint,
				mount.device,
				'{0}%'.format( used ),
				'{0}%{1}'.format( used, (used > 34)? ' used' : '' ),
				'{0}%'.format( free ),
				'{0}%{1}'.format( free, (free > 34)? ' free' : '' ),
				( (i+1)%3==0 || i == result.data.mountpoints.length-1 )? '0' : '16px',
				mount.size
			);
			container.html( container.html() + html );
		});
		// hide the placeholder and show the container
		placeholder.css('display', 'none');
		container.css('display', '');
	}



	function duckiebot_ros_callback(result){
		container = $('#ros-section-container');
		container_details = $('#ros_details');
		placeholder = $('#ros-section-placeholder');
		// get ROS status
		if( result.data.core.is_running ){
			$('#ros_core_status').html( '<span style="color:green; font-weight:bold">Running</span> with PID <bold>'+result.data.core.pid+'</bold>' );
			// add nodes
			$.each(result.data.nodes, function(i) {
				node = result.data.nodes[i];
				$('#ros_nodes_table > tbody').html( $('#ros_nodes_table > tbody').html() + '<tr><td>'+node+'</td></tr>' );
			});
			// add topics
			$.each(result.data.topics, function(i) {
				topic = result.data.topics[i];
				$('#ros_topics_table > tbody').html( $('#ros_topics_table > tbody').html() + '<tr><td>'+topic+'</td></tr>' );
			});
			// show details
			container_details.css('display', '');
		}else{
			$('#ros_core_status').html( '<span style="color:red; font-weight:bold">Not running</span>' );
		}
		// hide the placeholder and show the container
		placeholder.css('display', 'none');
		container.css('display', '');
	}



	function duckiebot_status_callback(result){
		$('#duckiebot_status').html(
			( result.data.online )? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true" style="color:green; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Online"></span> Online' : '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color:red; margin-top:5px" data-toggle="tooltip" data-placement="bottom" title="Offline"></span> Offline'
		);
		//
		if( result.data.online ){
			// configuration call
			var url = '<?php echo Configuration::$BASE_URL ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/duckiebot/configuration/json?name=<?php echo $duckiebotName ?>&token=<?php echo $_SESSION["TOKEN"] ?>';
			callAPI( url, false, false, duckiebot_configuration_callback, true );
		}else{
			update_section( 'configuration', '<h4 class="text-center" style="padding-right:30px">The Duckiebot is offline.</h4>' );
			update_section( 'storage', '<h4 class="text-center">The Duckiebot is offline.</h4>' );
			update_section( 'network', '<h4 class="text-center">The Duckiebot is offline.</h4>' );
			update_section( 'ros', '<h4 class="text-center">The Duckiebot is offline.</h4>' );
		}

		window.duckiebot_online = result.data.online;
	}



	function github_user_info_callback( result ){
		$('#duckiebot_owner').html(
			(result.name == null)? result.login : result.name
		);
	}

	$(document).ready( function(){
		$.each(_last_execution_divs, function(i) {
			test_div = _last_execution_divs[i];
			document.getElementById(test_div.id).innerHTML = moment( test_div.datetime, _datetime_format ).fromNow();
		});
		// is online check
		var url = '<?php echo Configuration::$BASE_URL ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/duckiebot/status/json?name=<?php echo $duckiebotName ?>&token=<?php echo $_SESSION["TOKEN"] ?>';
		callAPI( url, false, false, duckiebot_status_callback, true );
		// owner name call
		url = 'https://api.github.com/users/<?php echo $duckiebotOwner ?>';
		callExternalAPI( url, 'GET', 'json', false, false, github_user_info_callback, true, true, null, '<?php echo $duckiebotOwner ?>' );



		function on_info_tab_show(){}

		function on_info_tab_hide(){}

		function on_missions_tab_show(){}

		function on_missions_tab_hide(){}

		function on_network_tab_show(){
			// network call
			var url = '<?php echo Configuration::$BASE_URL ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/duckiebot/network/json?name=<?php echo $duckiebotName ?>&token=<?php echo $_SESSION["TOKEN"] ?>';
			callAPI( url, false, false, duckiebot_network_callback, true );
		}

		function on_network_tab_hide(){
			if( window.duckiebot_page_tabs['network']['subscriber'] !== null ){
				window.duckiebot_page_tabs['network']['subscriber'].unsubscribe();
			}
			// TODO
			console.log('Hide: Network Tab');
		}

		function on_storage_tab_show(){
			// storage call
			var url = '<?php echo Configuration::$BASE_URL ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/duckiebot/storage/json?name=<?php echo $duckiebotName ?>&token=<?php echo $_SESSION["TOKEN"] ?>';
			callAPI( url, false, false, duckiebot_storage_callback, true );
		}

		function on_storage_tab_hide(){
			// TODO
			console.log('Hide: Storage Tab');
		}

		function on_ros_tab_show(){
			// ROS call
			var url = '<?php echo Configuration::$BASE_URL ?>web-api/<?php echo Configuration::$WEBAPI_VERSION ?>/duckiebot/ros/json?name=<?php echo $duckiebotName ?>&token=<?php echo $_SESSION["TOKEN"] ?>';
			callAPI( url, false, false, duckiebot_ros_callback, true );
		}

		function on_ros_tab_hide(){
			// TODO
			console.log('Hide: ROS Tab');
		}

		function on_processes_tab_show(){
			// TODO
			console.log('Show: Processes Tab');
		}

		function on_processes_tab_hide(){
			// TODO
			console.log('Hide: Processes Tab');
		}

		function on_debug_tab_show(){
			// TODO
			console.log('Show: Debug Tab');
		}

		function on_debug_tab_hide(){
			// TODO
			console.log('Hide: Debug Tab');
		}

		$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
			in_tab = e.target; // newly activated tab
			out_tab = e.relatedTarget; // previous active tab
			// disable active components inside `out_tab`
			if( out_tab.id == 'sel_info' )
				on_info_tab_hide();
			if( window.duckiebot_online == true ){
				if( out_tab.id == 'sel_network' )
					on_network_tab_hide();
				if( out_tab.id == 'sel_missions' )
					on_missions_tab_hide();
				if( out_tab.id == 'sel_storage' )
					on_storage_tab_hide();
				if( out_tab.id == 'sel_ros' )
					on_ros_tab_hide();
				if( out_tab.id == 'sel_processes' )
					on_processes_tab_hide();
				if( out_tab.id == 'sel_debug' )
					on_debug_tab_hide();
			}
			// enable active components inside `in_tab`
			if( in_tab.id == 'sel_info' )
				on_info_tab_show();
			if( window.duckiebot_online == true ){
				if( in_tab.id == 'sel_network' )
					on_network_tab_show();
				if( in_tab.id == 'sel_missions' )
					on_missions_tab_show();
				if( in_tab.id == 'sel_storage' )
					on_storage_tab_show();
				if( in_tab.id == 'sel_ros' )
					on_ros_tab_show();
				if( in_tab.id == 'sel_processes' )
					on_processes_tab_show();
				if( in_tab.id == 'sel_debug' )
					on_debug_tab_show();
			}
		});

	} );


</script>
