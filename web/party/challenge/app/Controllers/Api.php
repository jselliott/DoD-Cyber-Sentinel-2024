<?php

namespace App\Controllers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Api extends BaseController
{
    public function save()
    {
        $request = \Config\Services::request();
        $output = array();

        if($data = $request->getJSON())
        {
            $db = db_connect();

            try {
                
                $uuid = uniqid('', true);

                $character = array("uuid"=>$uuid,
                                    "name"=>$data->charName,
                                    "bio"=>$data->charBio,
                                    "character_id"=>$data->charID,
                                    "str"=>$data->charStr,
                                    "con"=>$data->charCon,
                                    "wis"=>$data->charWis,
                                    "chr"=>$data->charChr,
                                    "intl"=>$data->charInt,
                                    "dex"=>$data->charDex);

                $db->table("saved_characters")->insert($character);

                $options = new QROptions(
                    [
                    'eccLevel' => QRCode::ECC_L,
                    'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                    'version' => 5,
                    ]
                );
                
                $qrcode = (new QRCode($options))->render(base64_encode(json_encode(array("cyberquest_character"=>$uuid))));
                
                $output = array("qr"=>$qrcode);

            } catch (Exception $e) {
                $output = array("error"=>"An error occurred while saving your character.");
            }
        } else {
            $output = array("error"=>"An error occurred while saving your character.");
        }
        
        return $this->response->setJSON($output);
    }

    // Not implemented
    public function load()
    {
        $request = \Config\Services::request();
        $output = array();

        if($data = $request->getJSON())
        {
            $db = db_connect();
            $session = session();

            try {
                $builder = $db->table("saved_characters")
                               ->where("uuid = '".$data->cyberquest_character."'")
                               ->limit(1);

                $debug = $builder->getCompiledSelect(false);
                $result = $builder->get();

                if($character = $result->getRowArray()){
                    // TODO: Parse character data and output for mobile app
                    $output = array("error"=>"This function is not currently implemented.");
                } else {
                    $output = array("error"=>"Invalid character code. Please try again.");
                }

                if(property_exists($data,"debug") && $data->debug == "true"){
                    $output["debug"] = $debug;
                } else {
                    $output["debug"] = "false";
                }

            } catch (Exception $e) {
                $output = array("error"=>$e->getMessage(),"debug"=>"false");
            }
        } else {
            $output = array("error"=>"Invalid character code. Please try again.","debug"=>"false");
        }

        return $this->response->setJSON($output);
    }

    public function promo()
    {
        $request = \Config\Services::request();
        $output = array();

        if($data = $request->getJSON())
        {
            $db = db_connect();

            try {

                $result = $db->table("promo_codes")
                ->where("code",$data->promo_code)
                ->limit(1)
                ->get();

                if($code = $result->getRow()){
                        
                        $query = $db->query('SELECT * FROM characters WHERE hidden=1 LIMIT 1');
                        $unlocked = $query->getRowArray();
                        $output = array("unlocked"=>$unlocked,"flag"=>file_get_contents("/flag.txt"));
                        
                } else {
                    $output = array("error"=>"Invalid promo code. Please try again.");
                }

            } catch (Exception $e) {
                $output = array("error"=>$e->getMessage());
            }
        } else {
            $output = array("error"=>"Invalid promo code. Please try again.");
        }
        
        return $this->response->setJSON($output);
    }
}
