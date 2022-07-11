<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


require_once $GLOBALS['__SYSTEM__DIR__'] . 'templates/forms/SmartForm.php';
require_once $GLOBALS['__SYSTEM__DIR__'] . 'classes/RESTfulAPI.php';


use \system\classes\Core;
use \system\classes\Database;
use system\classes\Configuration;
use system\classes\RESTfulAPI;
use system\packages\duckietown_duckiebot\Duckiebot;


// constants
$step_no = $_COMPOSE_SETUP_STEP_NO;

if (
    (
        (isset($_GET['step']) && $_GET['step'] == $step_no) ||
        (isset($_GET['force_step']) && $_GET['force_step'] == $step_no)
    ) &&
    (
        isset($_GET['confirm']) && $_GET['confirm'] == '1'
    )
) {
    _compose_first_setup_step_in_progress();
    // confirm step
    $first_setup_db = new Database('core', 'first_setup');
    $first_setup_db->write('step' . $step_no, null);
    
    // redirect to setup page
    Core::redirectTo('setup');
}
?>

<div style="margin: 20px 60px">
    <br/>
    <h3>Terms and Conditions</h3>
    <p>
        By proceeding you agree to the following terms and conditions:
    </p>
    <ul>
        <li>
            <strong>Duckietown Terms and Conditions</strong>: <a href="https://www.duckietown.org/about/terms-and-conditions">https://www.duckietown.org/about/terms-and-conditions</a>
        </li>
        <li>
            <strong>Duckietown Software License</strong>: <a href="https://www.duckietown.org/about/sw-license">https://www.duckietown.org/about/sw-license</a>
        </li>
        <li>
            <strong>Duckietown Privacy Policy</strong>: <a href="https://www.duckietown.org/about/privacy">https://www.duckietown.org/about/privacy</a>
        </li>
    </ul>
    <p>
        If you disagree, you can quit now.
    </p>
    <br/>
</div>

<button type="button" class="btn btn-success" id="confirm-step-button" style="float:right">
    <span class="fa fa-arrow-down" aria-hidden="true"></span>&nbsp; Agree
</button>

<script type="text/javascript">
    $('#confirm-step-button').on('click', function(){
        location.href = 'setup?step=<?php echo $step_no ?>&confirm=1';
    });
</script>
