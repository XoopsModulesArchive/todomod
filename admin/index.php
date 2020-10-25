<?php

require dirname(__DIR__, 3) . '/include/cp_header.php';

xoops_cp_header();

echo "<script language=\"javascript\">\n";
echo "  location.href='" . XOOPS_URL . '/modules/system/admin.php?fct=preferences&op=showmod&mod=' . $xoopsModule->getVar('mid') . "';";
echo "</script>\n";
exit();

xoops_cp_footer();
