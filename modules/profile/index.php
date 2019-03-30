<?php
use \system\packages\duckietown\Duckietown;

$token = Duckietown::getUserToken();
if(is_null($token))
  return;
?>

<h4>Duckietown Token</h4>
<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
  <div class="container-fluid" style="padding-left:0; padding-right:0">

    <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

      <p class="text-center" style="margin: 20px 0;">
        <?php echo $token ?>
      </p>

    </div>
  </div>
</nav>
