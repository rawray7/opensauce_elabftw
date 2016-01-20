<?php
/**
 * index.php
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see http://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */

/**
 * Index page
 *
 */
use \Elabftw\Elabftw\Tools as Tools;

require_once 'inc/common.php';
//$page_title = _('Home');
$selected_menu = null;

// Check if already logged in
if (isset($_SESSION['auth']) && $_SESSION['auth'] === 1) {


}


require_once 'inc/head.php';

$formKey = new \Elabftw\Elabftw\FormKey();


// if we are not in https, die saying we work only in https
if (!Tools::usingSsl()) {
    // get the url to display a link to click (without the port)
    $url = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
    $message = "eLabFTW works only in HTTPS. Please enable HTTPS on your server. Or click this link : <a href='$url'>$url</a>";
    display_message('error', $message);
    require_once 'inc/footer.php';
    exit;
}
?>


<section class='center'>
	<h2><?php echo _('OpenSauce = Open Source Chemistry'); ?></h2>
    <br>
    <h3> A powerful platform for chemical collaboration. Explore, adapt, document and share actionable recipes with the world. Open Sauce is built on the eLabFTW electronic lab notebook software, so you can quickly record, analyze and timestamp your experiments.</h3>
    <br>
    <h4> OpenSauce.us is currently in a public beta. We would greatly appreciate your feedback via our <a href='https://github.com/rawray7/opensauce_elabftw/issues'> GitHub issue tracker </a>. </h4>
	
    <!-- Login form -->
    <form method="post" action="app/login-exec.php" autocomplete="off">
        <h3><?php echo _('Sign in to your account'); ?></h3>
        <p>
        <label class='block' for="username"><?php echo _('Username'); ?></label>
        <input name="username" type="text" value='<?php
            // put the username in the field if we just registered
            if (isset($_SESSION['username'])) {
                echo $_SESSION['username'];
            }
            ?>' required /><br>
            <label class='block' for="password"><?php echo _('Password'); ?></label>
            <input name="password" type="password" required /><br>
            <!-- form key -->
            <?php echo $formKey->getFormkey(); ?>
        <br>
        <label for='rememberme'><?php echo _('Remember me'); ?></label>
        <input type='checkbox' name='rememberme' id='rememberme' />
        
        <div id='loginButtonDiv'>
        <button type="submit" class='button' name="Submit"><?php echo _('Login'); ?></button>
        </div>
    </form>
    <?php printf(_("Don't have an account? %sRegister%s now!<br>Lost your password? %sReset%s it!"), "<a href='register.php'>", "</a>", "<a href='#' class='trigger'>", "</a>"); ?></p>
    <div class='toggle_container'>
    <form name='resetPass' method='post' action='app/reset.php'>
    <input placeholder='<?php echo _('Enter your email address'); ?>' name='email' type='email' required />
    <button class='button' type="submit" name="Submit"><?php echo _('Send new password'); ?></button>
    </form>
    </div>

</section>

<script>
// Check for cookies
function checkCookiesEnabled() {
    var cookieEnabled = (navigator.cookieEnabled) ? true : false;
    if (typeof navigator.cookieEnabled == "undefined" && !cookieEnabled) {
        document.cookie="testcookie";
        cookieEnabled = (document.cookie.indexOf("testcookie") != -1) ? true : false;
    }
return (cookieEnabled);
}
if (!checkCookiesEnabled()) {
    var cookie_alert = "<div class='errorbox messagebox<p><?php echo _('Please enable cookies in your navigator to continue.'); ?></p></div>";
    document.write(cookie_alert);
}
</script>





<?php require_once 'inc/footer.php';