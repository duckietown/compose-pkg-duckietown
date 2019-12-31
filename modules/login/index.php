<?php
use \system\classes\Core;
use \system\classes\Configuration;
use \system\packages\duckietown\Duckietown;

$icon_url = Core::getImageURL('logo_h60.png', 'duckietown');
?>

<!-- https://github.com/45678/Base58 -->
<script type="text/javascript" src="<?php echo Core::getJSscriptURL('base58.js', 'duckietown') ?>" charset="utf-8"></script>


<button type="button" class="login-button">
  <span class="login-button-icon">
    <img src="<?php echo $icon_url ?>"/>
  </span>
  <span class="login-button-text" style="background-color: #ffc60f; color: #545454" data-toggle="modal" data-target="#dt-login-modal">
    Sign in with Duckietown
  </span>
</button>

<div class="modal fade modal-vertical-centered" id="dt-login-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius: 8px;">
      <div class="modal-header" style="background-color: #ffc60f; border-radius: 8px 8px 0 0">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Sign in with Duckietown</h4>
      </div>
      <div class="modal-body">

        <div class="input-group">
          <span class="input-group-addon" id="dt-token">Your Token</span>
          <input type="text" name="username" class="form-control" style="display: none" value="Duckietown Token">
          <input type="password" name="dt-token" class="form-control" id="dt-token-input" placeholder="Paste your personal token here" aria-describedby="dt-token" style="height:50px">
        </div>

      </div>
      <div class="modal-footer" style="background-color: #ffc60f; border-radius: 0 0 8px 8px">
        <a href="https://www.duckietown.org/site/your-token" target="_blank" style="float: left; font-size: 14px; margin-top: 6px">
          <span class="glyphicon glyphicon-link" aria-hidden="true"></span>
          Get your token
        </a>

        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" id="dt-login-confirm" class="btn btn-primary">Login</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script type="text/javascript">

function base58_decode( text ){
  var bytes = Base58.decode(text);
  var str = '';
  for (var i = 0; i < bytes.length; i++) {
    str += String.fromCharCode(bytes[i]);
  }
  return str;
}//base58_decode

$('#dt-login-confirm').on('click', function(){
  var token = $('#dt-token-input').val();
  // split the token in three parts
  parts = token.split('-');
  if( parts.length != 3 ){
    openAlert( 'danger', '[Error DT-1]: The token is not valid' );
    return;
  }
  // get parts
  version = parts[0];
  payload_58 = parts[1];
  signature_58 = parts[2];
  // decode payload and signature
  payload = base58_decode(payload_58);
  signature = base58_decode(signature_58);
  // make sure that the payload is complete
  try {
    payload = JSON.parse(payload);
  } catch (e) {
    openAlert( 'danger', '[Error DT-2]: Invalid token format; Invalid payload.' );
    return;
  }
  if( payload.uid == undefined || payload.exp == undefined ){
    // not valid
    openAlert( 'danger', '[Error DT-3]: Invalid token format; Missing fields from payload.' );
    return;
  }
  showPleaseWait();

  function on_login_success_fcn(){
    window.open("<?php echo Configuration::$BASE_URL.base64_decode($_GET['q']) ?>", "_top");
  }//on_login_success_fcn

  // call API
  url = "<?php echo sprintf(
    '%s/web-api/%s/duckietoken/login_with_duckietoken/json?duckietoken={0}&token=%s',
    Configuration::$BASE_URL,
    Configuration::$WEBAPI_VERSION,
    $_SESSION['TOKEN']) ?>".format( token );

  callAPI( url, true, false, on_login_success_fcn );
});

</script>
