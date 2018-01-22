<?php

# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Tuesday, January 9th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Saturday, January 20th 2018


require_once $GLOBALS['__PACKAGES__DIR__'].'duckietown/Duckietown.php';
use \system\packages\duckietown\Duckietown as Duckietown;

?>


<style type="text/css">

body > .container{
	/* TODO: compute this based on the size of the grid + 2*toolbox_width + some padding */
    min-width: 1000px;
}

</style>

<?php
$tiles = [
	"3way_tile_plain.svg" => ["0", "90", "180", "270"],
	"4way_tile_plain.svg" => ["0"],
	"curve_tile_plain.svg" => ["0", "90", "180", "270"],
	"grass_tile_plain.svg" => ["0"],
	"parking_lot_tile_plain.svg" => ["0"],
	"straight_2stop_tile_plain.svg" => ["0", "90"],
	"straight_stop_tile_plain.svg" => ["0", "90", "180", "270"],
	"straight_tile_plain.svg" => ["0", "90"]
];

?>

<link href="<?php echo sprintf("%scss.php?package=%s&stylesheet=%s",
	\system\classes\Configuration::$BASE_URL, 'duckietown', 'duckietown_page.css'); ?>" rel="stylesheet">
<script src="<?php echo sprintf("%sjs.php?package=%s&script=%s",
	\system\classes\Configuration::$BASE_URL, 'duckietown', 'duckietown_page.js'); ?>"></script>


<div style="width:100%; margin:auto">

	<table style="width:970px; margin:auto; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:50%">
				<h2>Duckietown</h2>
			</td>
		</tr>

	</table>



	<table style="width:100%">
		<tr>

			<td class="side_toolbox_container side_toolbox_container_left">

				<div class="tiles_toolbox_left_top">
						<?php
						foreach ($tiles as $tile => $tile_orientations):
							foreach ($tile_orientations as $orientation): ?>
								<img class="tile tile_<?php echo $orientation ?>"
									id="<?php echo $tile."_".$orientation ?>"
									src="<?php echo sprintf("%simage.php?package=%s&image=%s",
										\system\classes\Configuration::$BASE_URL, 'duckietown', $tile); ?>"
									draggable="true" ondragstart="drag(event)" />
							<?php
							endforeach;
						endforeach;
						?>
				</div>

				<div class="tiles_toolbox_left_bottom">
					<img src="<?php echo sprintf("%simage.php?package=%s&image=%s",
						\system\classes\Configuration::$BASE_URL, 'duckietown', 'trashcan.png'); ?>"
						 />
				</div>

			</td>


			<td class="text-center" style="width:100%">

				<?php
				$rows = 6;
				$columns = 5;
				?>

				<table class="town_canvas">
				<?php
				for ($i = 0; $i < $rows; $i++) {
					echo "<tr>";
					for ($j = 0; $j < $columns; $j++) {
						?>
						<td>
							<div id="slot_<?php echo $i."_".$j ?>"
								class="tile_container"
								data-row="<?php echo $i ?>"
								data-column="<?php echo $j ?>"
								ondrop="drop(event)"
								ondragover="allowDrop(event)"
								ondragenter="dragEnter(event)"
								ondragleave="dragLeave(event)">
							</div>
						</td>
						<?php
					}
					echo "</tr>";
				}
				?>
				</table>

			</td>


			<td class="side_toolbox_container side_toolbox_container_right">

				<div class="tiles_toolbox_right">

				</div>

			</td>

		</tr>
	</table>








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



	<!-- <div id="text">
		<div id="div1" ondrop="drop(event)" ondragover="allowDrop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)"></div>
		<div id="div2" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div3" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div4" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div5" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div6" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div7" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div8" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
		<div id="div9" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
	</div> -->

</div>



<script type="text/javascript">

var baseurl = '<?php echo \system\classes\Configuration::$BASE_URL ?>';

</script>
