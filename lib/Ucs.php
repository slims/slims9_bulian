<?php
// Ucs
namespace SLiMS;

class Ucs
{
    public static function auto_delete ($itemIDs)
    {
        global $sysconf;
        $data = array(
            'operation' => 'delete',
            'biblio' => $itemIDs
        );
        // encode array to json format
        $to_sent['node_info'] = $sysconf['ucs'];
        $to_sent['node_data'] = $data;
        // create HTTP request
        $http_request = new \GuzzleHttp\Client();
        $http_param = ['body' => json_encode($to_sent), 'http_errors' => false];
        if (isset($sysconf['ucs']['serverversion']) && $sysconf['ucs']['serverversion'] < 3) {
            $request = $http_request->request('POST', $sysconf['ucs']['serveraddr'].'/uc-ops.php', $http_param);
        } else {
            $request = $http_request->request('POST', $sysconf['ucs']['serveraddr'].'/ucs.php', $http_param);
        }
    }
}