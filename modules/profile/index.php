<?php
use \system\packages\duckietown\Duckietown;

$token = Duckietown::getUserToken();
if(is_null($token))
  return;
?>

<style>
.click-to-reveal-anchor {
  width: 100%;
  color: #444eb8;
  text-decoration: none;
}

.click-to-reveal-anchor:active,
.click-to-reveal-anchor:focus,
.click-to-reveal-anchor:visited {
  color: #444eb8;
  text-decoration: none;
}

.click-to-reveal-anchor:hover {
  color: #5260ff;
  text-decoration: none;
}
</style>

<h4>Duckietown Token</h4>
<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
  <div class="container-fluid" style="padding-left:0; padding-right:0; padding-top:12px;">

    <!-- Click to toggle the token -->
    <a
      data-toggle="collapse"
      class="click-to-reveal-anchor"
      href="#duckietown-dashboard-profile-token"
    >
      <center>
        Click to reveal the token
      </center>
    </a>
    <div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">
      <p class="text-center collapse" id="duckietown-dashboard-profile-token" style="margin: 20px 0;">
        <?php echo $token ?>
      </p>

    </div>
  </div>
</nav>
