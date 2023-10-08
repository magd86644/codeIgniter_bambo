
<?php

if (!function_exists('get_visitor_agent_h')) {
    function get_visitor_agent_h($request)
    {
        $agent = $request->getUserAgent();
        if ($agent->isBrowser()) {
            $agentTxt = $agent->getBrowser() . ' ' . $agent->getVersion();
        } elseif ($agent->isRobot()) {
            $agentTxt = $agent->getRobot();
        } elseif ($agent->isMobile()) {
            $agentTxt = $agent->getMobile();
        } else {
            $agentTxt = 'Unidentified User Agent';
        }
        return $agentTxt;
    }
}

if (!function_exists('save_visitors_data_h')) {
    /** Save visitors public info, if session ip is not set.
     * request,dbmodl is protected, not accessible, so we send them as parameter
   
    */
    function save_visitors_data_h($request,$dbModel)
    {
        if (session()->has('session_ip')) {
            return;
        }
       
        // new visitor
        $agent =  $request->getUserAgent();
        $agentTxt = get_visitor_agent_h($request);
        $ip = $request->getIPAddress();
        session()->set('session_ip', $ip);
        $data = [
            'agent' => $agentTxt,
            'ip' => $ip,
            "date" => date("Y-m-d"),
            "full_date" => date("Y-m-d h:i:s"),
            "os" =>  $agent->getPlatform(),
            "browser" => $agent->getBrowser(),
            "version" => $agent->getVersion(),
            "is_robot" => $agent->isRobot(),
            "is_mobile" => $agent->isMobile()
        ];
        $dbModel->save_data('db_visitors',  $data);
    }
}
