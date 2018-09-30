<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use \system\classes\Core;
use \system\classes\Configuration;

$CHALLENGES_API_VERSION = 'v3';
?>

<!-- https://github.com/45678/Base58 -->
<script type="text/javascript" src="<?php echo Core::getJSscriptURL('base58.js', 'duckietown') ?>" charset="utf-8"></script>


<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>Insert your Duckietown Token</h2>
			</td>
		</tr>

	</table>

	Your personal token can be found at <a href="https://www.duckietown.org/site/your-token" target="_blank">https://www.duckietown.org/site/your-token</a>.

	<div class="input-group" style="margin:40px 0">
	  <span class="input-group-addon" id="dt-token">Your Token</span>
	  <input type="text" class="form-control" id="dt-token-input" placeholder="Paste your personal token here" aria-describedby="dt-token" style="height:50px">
	</div>

	<button type="button" class="btn btn-success" id="dt-confirm" style="float:right">Confirm</button>

</div>

<script type="text/javascript">

	function base58_decode( text ){
		var bytes = Base58.decode(text);
        var str = '';
        for (var i = 0; i < bytes.length; i++) {
            str += String.fromCharCode(bytes[i]);
        }
        return str;
	}//base58_decode

	$('#dt-confirm').on('click', function(){
		var token = $('#dt-token-input').val();
		// split the token in three parts
		parts = token.split('-');
		if( parts.length != 3 ){
			openAlert( 'danger', 'The token is not valid' );
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
			openAlert( 'danger', 'Invalid token format; Invalid payload.' );
			return;
		}
		if( payload.uid == undefined || payload.exp == undefined ){
			// not valid
			openAlert( 'danger', 'Invalid token format; Missing fields from payload.' );
			return;
		}
		showPleaseWait();
		// verify token on the server
		var url = "https://challenges.duckietown.org/<?php echo $CHALLENGES_API_VERSION ?>/info";
		// call API
		$.ajax({
			type: 'GET',
	        url: url,
			headers: {
				'X-Messaging-Token': token
			},
			success: function( result ){
				// send token to the server
				url = "<?php echo sprintf(
					'%s/web-api/%s/duckietoken/set/json?duckietoken={0}&token=%s',
					Configuration::$BASE_URL,
					Configuration::$WEBAPI_VERSION,
					$_SESSION['TOKEN'],
					$_SESSION['TOKEN']) ?>".format( token );
				callAPI( url, true, true );
		    },
			error: function( jqXHR, textStatus, errorThrown ){
		        // error
				hidePleaseWait();
				openAlert('danger', 'The token is not valid. Please check and try again.');
	    	}
		});
	});

</script>
