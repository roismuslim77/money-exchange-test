<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;
use Illuminate\Support\Facades\Storage;

class ScrapController extends Controller
{
    public function refresh(Request $request){
        $files = glob(storage_path('rates').'/*');

        // Deleting all the files in the list
        foreach($files as $file) {
            if(is_file($file))
                // Delete the given file
                unlink($file);
        }

        return redirect()->back()->with('success', 'Berhasil Hapus File.');
    }

    public function scrapMoney(Request $request)
    {

        $all_rates = [];
        $client = new Client();

        $website = $client->request('GET', 'https://kursdollar.org/');

        //scrap for title
        $ret = [];
        $table = $website->filter('.row.space30')->each(function($node, $i) use($ret){
            if($i == 1){
                $title = $node->filter('.title_table')->eq(1);
                return [
                    'date' => date('d-m-Y'),
                    'day' => date('l'),
                    'indonesia' => $title->children()->eq(1)->text(),
                    'word' => $title->children()->eq(2)->text()
                ];
            }
        });

        //scrap for rates
        $first_rates = $website->filter('tr[style="text-align: right; cursor: pointer; background-color: #CAFDB5;"]');
        $first_rates = [
            "currency" => preg_replace("/[^a-zA-Z0-9]+/", "", $first_rates->filter('strong')->text()),
            "buy" => (float)explode(" ",$first_rates->children()->eq(1)->text())[0],
            "sell" => (float)explode(" ",$first_rates->children()->eq(2)->text())[0],
            "average" => (float)explode(" ",$first_rates->children()->eq(3)->text())[0],
            "word_rate" => (float)$first_rates->children()->eq(4)->text()
        ];

        $rates = $website->filter('tr[style="text-align: right; cursor: pointer;"]')->each(function($node){
            $kurs_name = $node->filter('strong')->text();
            $buy = $node->children()->eq(1)->text();
            $sell = $node->children()->eq(2)->text();
            $avg = $node->children()->eq(3)->text();
            $world = $node->children()->eq(4)->text();

            return [
                "currency" => preg_replace("/[^a-zA-Z0-9]+/", "", $kurs_name),
                "buy" => (float)explode(" ",$buy)[0],
                "sell" => (float)explode(" ",$sell)[0],
                "average" => (float)explode(" ",$avg)[0],
                "word_rate" => (float)$world
            ];
        });

        $all_rates = array_merge([$first_rates], $rates);
        $ret = [
            'meta' => $table[1],
            'rates' => $all_rates
        ];

        Storage::disk('local')->put('rate-'.date('d-m-y').'--'.date('H-i-s').'.json', json_encode($ret));
        return 'updated';
    }
}
