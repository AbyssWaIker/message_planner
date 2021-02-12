<?php


require_once  'mail.php';
require_once  'data_reciever.php';


$isCLI = ( php_sapi_name() == 'cli' );
if(!$isCLI)
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';

$data = new data_reviever();;
$mail = new sender('kirnevartem30@gmail.com',  $data->title, $data->text);
$mail->send();

?>