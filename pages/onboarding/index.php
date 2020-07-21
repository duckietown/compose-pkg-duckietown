<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;
use \system\packages\duckietown\Duckietown;

$res = Duckietown::getUserToken();
if (!is_null($res)) {
    Core::redirectTo('');
}
?>

<!-- https://github.com/45678/Base58 -->
<script type="text/javascript" src="<?php echo Core::getJSscriptURL('base58.js', 'duckietown') ?>"
        charset="utf-8"></script>


<h2 class="page-title"></h2>

<p>
    Welcome to the Duckietown <strong>onboarding</strong> page!
    <br/>
    Fill in the form below to complete your onboarding.
</p>
<br/>

Your personal token can be found at
<a href="https://www.duckietown.org/site/your-token" target="_blank">
    https://www.duckietown.org/site/your-token
</a>.

<div class="input-group" style="margin:10px 0 40px 0">
    <span class="input-group-addon" id="dt-token">Your Token</span>
    <input type="text" class="form-control" id="dt-token-input"
           placeholder="Paste your personal token here" aria-describedby="dt-token"
           style="height:50px">
</div>

<button type="button" class="btn btn-success" id="dt-confirm" style="float:right">Confirm</button>



<script type="text/javascript">

    function base58_decode(text) {
        let bytes = Base58.decode(text);
        let str = '';
        for (let i = 0; i < bytes.length; i++) {
            str += String.fromCharCode(bytes[i]);
        }
        return str;
    }//base58_decode

    $('#dt-confirm').on('click', function () {
        let token = $('#dt-token-input').val();
        // split the token in three parts
        let parts = token.split('-');
        if (parts.length !== 3) {
            openAlert('danger', 'The token is not valid');
            return;
        }
        // get parts
        let version = parts[0];
        let payload_58 = parts[1];
        let signature_58 = parts[2];
        // decode payload and signature
        let payload = base58_decode(payload_58);
        let signature = base58_decode(signature_58);
        // make sure that the payload is complete
        try {
            payload = JSON.parse(payload);
        } catch (e) {
            openAlert('danger', 'Invalid token format; Invalid payload.');
            return;
        }
        if (payload.uid === undefined || payload.exp === undefined) {
            // not valid
            openAlert('danger', 'Invalid token format; Missing fields from payload.');
            return;
        }
        showPleaseWait();
        // send token to the server
        url = "<?php echo sprintf(
            '%sweb-api/%s/duckietoken/set/json?duckietoken={0}&token=%s',
            Configuration::$BASE,
            Configuration::$WEBAPI_VERSION,
            $_SESSION['TOKEN']) ?>".format(token);
        callAPI(url, true, true);
    });

</script>
