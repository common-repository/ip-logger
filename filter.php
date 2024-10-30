<?php

if ($_REQUEST["act"] == "delete") {
	$wpdb->get_var("delete from ".$_REQUEST["table_name"]."_block where id=" . $_REQUEST["id"]);
}

if ($_REQUEST["act"] == "add") {
	$wpdb->get_var("insert into ".$_REQUEST["table_name"]."_block (".$_REQUEST["target"].") values ('".$_REQUEST["filter"]."')");
}

?>
<script>
document.location.href = "/wp-admin/options-general.php?page=yhc-ip-logger/index.php&updated=true";
</script>
