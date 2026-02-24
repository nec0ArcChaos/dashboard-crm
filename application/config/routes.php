<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING — CRM Dashboard
| -------------------------------------------------------------------------
*/

$route['default_controller'] = 'dashboard';
$route['404_override']        = '';
$route['translate_uri_dashes'] = FALSE;

// Dashboard routes
$route['dashboard']                = 'dashboard/index';
$route['dashboard/modal_detail']   = 'dashboard/modal_detail';
