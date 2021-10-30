<?php
session_start();
header('Content-type: application/x-javascript');
if(isset($_SESSION['cjax_cache'])) print $_SESSION['cjax_cache'];
elseif(isset($_COOKIE['cjax_cache']))print $_COOKIE['cjax_cache'];