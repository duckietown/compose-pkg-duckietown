<?php

# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Tuesday, January 9th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 17th 2018



require_once $GLOBALS['__PACKAGES__DIR__'].'duckietown/Duckietown.php';
use \system\packages\duckietown\Duckietown as Duckietown;


$tiles = [
	"3way_tile_plain.svg",
	"4way_tile_plain.svg",
	"curve_tile_plain.svg",
	"grass_tile_plain.svg",
	"parking_lot_tile_plain.svg",
	"straight_2stop_tile_plain.svg",
	"straight_stop_tile_plain.svg",
	"straight_tile_plain.svg"
];

?>

<link href="<?php echo sprintf("%scss.php?package=%s&stylesheet=%s",
	\system\classes\Configuration::$BASE_URL, 'duckietown', 'duckietown_page.css'); ?>" rel="stylesheet">
<script src="<?php echo sprintf("%sjs.php?package=%s&script=%s",
	\system\classes\Configuration::$BASE_URL, 'duckietown', 'duckietown_page.js'); ?>"></script>


<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:50%">
				<h2>Duckietown</h2>
			</td>
		</tr>

	</table>

	<div id="tiles_toolbox">
			<?php foreach ($tiles as $tile): ?>
				<img class="tile" id="<?php echo $tile ?>" src="<?php echo sprintf("%simage.php?package=%s&image=%s",
					\system\classes\Configuration::$BASE_URL, 'duckietown', $tile); ?>"
					draggable="true" ondragstart="drag(event)" />
			<?php endforeach; ?>
	</div>

	<!-- <div id="images">
		<div id="drag4" draggable="true" ondragstart="drag(event)"></div>
		<div id="drag5" draggable="true" ondragstart="drag(event)"></div>
		<div id="drag6" draggable="true" ondragstart="drag(event)"></div>
		<div id="drag7" draggable="true" ondragstart="drag(event)"></div>
		<div id="drag8" draggable="true" ondragstart="drag(event)"></div>
		<div id="drag9" draggable="true" ondragstart="drag(event)"></div>
		<div id="drag10" draggable="true" ondragstart="drag(event)"></div>
		<div id="drag11" draggable="true" ondragstart="drag(event)"></div>
		<div id="drag12" draggable="true" ondragstart="drag(event)"></div>
	</div> -->

	<div id="text">
		<div id="div1" ondrop="drop(event)" ondragover="allowDrop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)"></div>
		<div id="div2" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div3" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div4" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div5" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div6" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div7" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div8" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div9" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
	</div>

</div>



<script type="text/javascript">

var baseurl = '<?php echo \system\classes\Configuration::$BASE_URL ?>';

</script>
