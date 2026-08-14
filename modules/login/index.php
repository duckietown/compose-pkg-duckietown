<?php
use \system\classes\Core;
use \system\classes\Configuration;
use \system\packages\duckietown\Duckietown;

$icon_url = Core::getImageURL('logo_h60.png', 'duckietown');
?>

<!-- https://github.com/45678/Base58 -->
<script type="text/javascript" src="<?php echo Core::getJSscriptURL('base58.js', 'duckietown') ?>" charset="utf-8"></script>

<style type="text/css">
  .dt-login-inline {
    max-width: 420px;
    margin: 0 auto;
    text-align: left;
  }
  .dt-login-inline .input-group {
    margin-bottom: 12px;
  }
  .dt-login-inline .dt-login-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
  }
</style>

<div class="dt-login-inline">
  <div class="text-center" style="margin-bottom: 16px;">
    <img src="<?php echo $icon_url ?>" alt="Duckietown" style="height: 48px;"/>
  </div>

  <div class="input-group">
    <span class="input-group-addon" id="dt-token">Your Token</span>
    <input type="text" name="username" class="form-control" style="display: none" value="Duckietown Token" autocomplete="username">
    <input type="password" name="dt-token" class="form-control" id="dt-token-input"
           placeholder="Paste your personal token here" aria-describedby="dt-token" style="height:50px"
           autocomplete="current-password">
  </div>

  <div class="dt-login-actions">
    <a href="https://hub.duckietown.com/profile/" target="_blank" style="font-size: 14px;">
      <span class="glyphicon glyphicon-link" aria-hidden="true"></span>
      Get your token / Sign up
    </a>
    <button type="button" id="dt-login-confirm" class="btn btn-primary">Sign in</button>
  </div>
</div>

<?php
/*
 * Previous modal-based "Sign in with Duckietown" UI kept for reference.
 *
<button type="button" class="login-button">
  <span class="login-button-icon">
    <img src="<?php echo $icon_url ?>"/>
  </span>
  <span class="login-button-text" style="background-color: #ffc60f; color: #545454" data-toggle="modal" data-target="#dt-login-modal">
    Sign in with Duckietown
  </span>
</button>
...
*/
?>

<script type="text/javascript">

function base58_decode( text ){
  var bytes = Base58.decode(text);
  var str = '';
  for (var i = 0; i < bytes.length; i++) {
    str += String.fromCharCode(bytes[i]);
  }
  return str;
}//base58_decode

function dt_login_submit(){
  let token = $('#dt-token-input').val();
  // split the token in three parts
  let parts = token.split('-');
  if( parts.length !== 3 ){
    openAlert( 'danger', '[Error DT-1]: The token is not valid' );
    return;
  }
  // get parts
  let payload_58 = parts[1];
  // decode payload and signature
  let payload = base58_decode(payload_58);
  // make sure that the payload is complete
  try {
    payload = JSON.parse(payload);
  } catch (e) {
    openAlert( 'danger', '[Error DT-2]: Invalid token format; Invalid payload.' );
    return;
  }
  if( payload.uid === undefined || payload.exp === undefined ){
    // not valid
    openAlert( 'danger', '[Error DT-3]: Invalid token format; Missing fields from payload.' );
    return;
  }
  showPleaseWait();

  function on_login_success_fcn(){
    $(window).trigger('COMPOSE_LOGGED_IN');
  }//on_login_success_fcn

  // call API
  smartAPI('duckietoken', 'login_with_duckietoken', {
      'arguments': {
          'duckietoken': token,
      },
      'block': true,
      'confirm': true,
      'on_success': on_login_success_fcn
  });
}

$('#dt-login-confirm').on('click', dt_login_submit);
$('#dt-token-input').on('keydown', function(e){
  if (e.key === 'Enter' || e.keyCode === 13) {
    e.preventDefault();
    dt_login_submit();
  }
});

</script>
