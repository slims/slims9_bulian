<?php
$html_str = '
<!DOCTYPE html>
<html>
<head>
    <title>'.$sysconf['library_name'].' '.$page_title.'</title>
    <link href="'.SWB.'css/bootstrap.min.css'.'" rel="stylesheet" type="text/css" />
    <link href="'.SWB.'css/printed.css?v='.date('this').'" rel="stylesheet" type="text/css" />
</head>
<body>
    <h5>'.$sysconf['library_name'].' - '.$page_title.'</h5>
    '.$table->printTable().'
    <script type="text/javascript">self.print();</script>
</body></html>';
