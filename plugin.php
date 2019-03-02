<?php
/**
* Plugin Name: WordPress AWS CloudFront Cache Invalidator
* Plugin URI: https://www.jennycraig.com.au/
* Description: This plugin performs custom manipulations with HubSpot forms using HubSpot Forms JS API .
* Version: 0.1
* Author: Daynis Olman
* Author URI: https://www.jennycraig.com.au/
**/
add_action('admin_menu', 'test_button_menu');
function test_button_menu(){
  add_menu_page('Invalidate CloudFront Distribution', 'CloudFront Invalidate', 'manage_options', 'test-button-slug', 'test_button_admin_page');
}
function test_button_admin_page() {
  // This function creates the output for the admin page.
  // It also checks the value of the $_POST variable to see whether
  // there has been a form submission. 
  // The check_admin_referer is a WordPress function that does some security
  // checking and is recommended good practice.
  // General check for user permissions.
  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient pilchards to access this page.')    );
  }
  // Start building the page
  echo '<div class="wrap">';
  echo '<h2>WordPress AWS CloudFront Invalidationr</h2>';
  // Check whether the button has been pressed AND also check the nonce
  if (isset($_POST['test_button']) && check_admin_referer('test_button_clicked')) {
    // the button has been pressed AND we've passed the security check
    test_button_action();
  }
  echo '<form action="options-general.php?page=test-button-slug" method="post">';
  // this is a WordPress security feature - see: https://codex.wordpress.org/WordPress_Nonces
  wp_nonce_field('test_button_clicked');
  echo '<input type="hidden" value="true" name="test_button" />';
  submit_button('Start invalidation!');
  echo '</form>';
  echo '</div>';
}
function test_button_action()
{
    
  flush_CloudFront();
    
  echo '<div id="message" class="updated fade"><p>'
    .'AWS CloudFront Invalidation process has been initiated.</p> <p>This will will invalidate all cached assets in your CloudFront distribution.</p><p>Please wait 2-3 minutes for AWS to finalise invalidation process in the background.' . '</p></div>';
  $path = WP_TEMP_DIR . '/test-button-log.txt';
  $handle = fopen($path,"w");
  if ($handle == false) {
    echo '<p>Could not write the log file to the temporary directory: ' . $path . '</p>';
  }
  else {
    echo '<p>Log has been written to: ' . $path . '</p>';
    fwrite ($handle , "Call Function button clicked on: " . date("D j M Y H:i:s", time())); 
    fclose ($handle);
  }
}  
function flush_CloudFront() {
    
    
/**
 * Super-simple AWS CloudFront Invalidation Script
 * Modified by Steve Jenkins <steve stevejenkins com> to invalidate a single file via URL.
 * 
 * Steps:
 * 1. Set your AWS Access Key
 * 2. Set your AWS Secret Key
 * 3. Set your CloudFront Distribution ID (or pass one via the URL with &dist)
 * 4. Put cf-invalidate.php in a web accessible and password protected directory
 * 5. Run it via: http://example.com/protected_dir/cf-invalidate.php?filename=FILENAME
 *    or http://example.com/cf-invalidate.php?filename=FILENAME&dist=DISTRIBUTION_ID
 * 
 * The author disclaims copyright to this source code.
 *
 * Details on what's happening here are in the CloudFront docs:
 * http://docs.amazonwebservices.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.html
 * 
 */
//$onefile = $_GET['filename']; // You must include ?filename=FILENAME in your URL or this won't work
  $onefile = "/*"; // You must include ?filename=FILENAME in your URL or this won't work
if (!isset($_GET['dist'])) {
        $distribution = 'E2EHADLBZO3D7D'; // Your CloudFront Distribution ID, or pass one via &dist=
} else {
        $distribution = $_GET['dist'];
}
$access_key = 'AAAAAAAAAAAAAAAAAAAAAAA'; // Your AWS Access Key goes here
$secret_key = 'BBBBBBBBBBBBBBBBBBBBBBB'; // Your AWS Secret Key goes here
$epoch = date('U');
$xml = <<<EOD
<InvalidationBatch>
    <Path>{$onefile}</Path>
    <CallerReference>{$distribution}{$epoch}</CallerReference>
</InvalidationBatch>
EOD;
/**
 * You probably don't need to change anything below here.
 */
$len = strlen($xml);
$date = gmdate('D, d M Y G:i:s T');
$sig = base64_encode(
    hash_hmac('sha1', $date, $secret_key, true)
);
$msg = "POST /2010-11-01/distribution/{$distribution}/invalidation HTTP/1.0\r\n";
$msg .= "Host: cloudfront.amazonaws.com\r\n";
$msg .= "Date: {$date}\r\n";
$msg .= "Content-Type: text/xml; charset=UTF-8\r\n";
$msg .= "Authorization: AWS {$access_key}:{$sig}\r\n";
$msg .= "Content-Length: {$len}\r\n\r\n";
$msg .= $xml;
$fp = fsockopen('ssl://cloudfront.amazonaws.com', 443, 
    $errno, $errstr, 30
);
if (!$fp) {
    die("Connection failed: {$errno} {$errstr}\n");
}
fwrite($fp, $msg);
$resp = '';
while(! feof($fp)) {
    $resp .= fgets($fp, 1024);
}
fclose($fp);
print '<pre>'.$resp.'</pre>'; // Make the output more readable in your browser
    
    
}
?>
