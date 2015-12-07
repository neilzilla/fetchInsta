<?php
    function fetchInsta($accessToken, $user, $amount = 3) {
        // Get root
        $store = kirby()->roots()->index() . "/site/plugins/instagram/cache$amount.json";
        
        // Check for cache
        if(file_exists($store)) $cached = json_decode(file_get_contents($store), true);
        if(isset($cached)){
            if($cached['timestamp'] > time() - 86400) return $cached['images'];
        }
        
        // Set url and do initial get of latest
        $url = "https://api.instagram.com/v1/users/$user/media/recent/?access_token=$accessToken";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        curl_close($ch);
                
        // Decode to array
        $images =  json_decode($result, true);
        $output = $images['data'];
        $nexturl = $images['pagination']['next_url'];
        // Check if amount is more than 20
        if($amount > 1){
            $i = 1;
            while($i < $amount){
                $url = $nexturl;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                $result = curl_exec($ch);
                curl_close($ch);
                $result =  json_decode($result, true);
                $count = count($output);
                $nexturl = $result['pagination']['next_url'];
                foreach($result['data'] as $img){
                    $output[$count] = $img;
                    $count++;
                }
                $i++;
            }
        }
        $data['timestamp'] = time();
        $data['images'] = $output;
        file_put_contents($store, json_encode($data));
        return $output;
    }

?>