<?php
if (!defined('PHPBT_VERSION')) {
    die('not in phpbt');
}
?><html>
    <head>
        <META HTTP-EQUIV="Expires" CONTENT="-1">
        <title>phpBugTracker - <?php echo $page_title ?></title>
        <link rel="StyleSheet" href="styles/<?php echo STYLE ?>.css" type="text/css">
        <META http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET ?>">
    </head>
    <body topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td class="maincontent">
                    <?php
                    if (substr($content_template, -4) == '.tpl') {
                        $this->smarty->display($content_template);
                    } else {
                        include($content_template);
                    }
                    ?>
                </td>
            </tr>
        </table>
    </body>
</html>
