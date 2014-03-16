<?php
include 'libraries/integrity.md5.class.php';

$integrity = new integrity('./');

if($integrity->getMd5Hashes() > 0){
    echo "MD5 File Hashes Generated!";
} else {
    echo "Error!";
}
?>
