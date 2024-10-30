<?php

// Add new counters for several totals
update_option("ip_logger_totalcounter_hits_saved", get_option("ip_logger_blck_archived"));
delete_option("ip_logger_blck_archived");

?>
