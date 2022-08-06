<?php
namespace App\Http\Controllers;
use App\Models\School;
use Illuminate\Http\Request;
class MyController extends Controller{  
    public static $mapApiKey = 'AIzaSyBDzEs3UGiGRu0GuNR2HpEj8umyx2YKA4c';
    public static $remoteUrl = "http://geodata.gov.gr/geoserver/wfs/?service=WFS&version=1.0.0&request=GetFeature&typeName=geodata.gov.gr:11009351-71ec-47f1-9da5-282f04b72a80&outputFormat=application/json&srsName=epsg:4326";
    public function home(Request $request){
        return view('home', [ 'mapApiKey' => self::$mapApiKey]);
    }
    public function get(Request $request){
        $data = $this->remote();
        if ( !empty($data['features']) ){
            foreach($data['features'] as $d){
                if ( !empty($d['properties']['sc_id']) ){
                    $school = School::where('remote_id', $d['properties']['sc_id'])->first();
                    if ( empty($school) ){
                        $school = new School;
                        $school->remote_id = $d['properties']['sc_id'];
                        $school->lat = $d['geometry']['coordinates'][0][1] ?? null;
                        $school->lng = $d['geometry']['coordinates'][0][0] ?? null;
                        $school->address = $d['properties']['sc_address'];
                        $school->descr = $d['properties']['sc_descr'];
                        $school->category = $d['properties']['sc_categor'];
                        $school->info = $this->geocode($d['geometry']['coordinates'][0][1], $d['geometry']['coordinates'][0][0]);
                        $school->save();
                    }
                }
            }
        }
        $schools = School::all();
        $categories = [];
        if ( !empty($schools) ){
            foreach($schools as $school){
                $categories[] = $school['category'];
            }
            $categories = array_unique($categories);
        }
        return response()->json(['schools' => $schools, 'categories' => $categories]);
    }
    public function geocode($lat, $lng){
        if ( $lat && $lng ){
            $info = [];
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lng.'&sensor=false&key='.self::$mapApiKey;
            $json = @file_get_contents($url);
            if ( $json ){
                $data = json_decode($json, true);
                $status = $data['status'];
                if ( $status == 'OK' && $data['results'][0]['address_components'] ){
                    foreach($data['results'][0]['address_components'] as $c){
                        $info[] = $c['short_name'];
                        $info[] = $c['long_name'];
                    }
                }
                return implode("\r", $info);
            }
        }
        return null;
    }
    public function remote(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$remoteUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $output = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);
        if ( !empty($error) ){
            return $error;
        } else if ( $output ){
            $data = json_decode($output, true);
            return $data;
        } else{
            return 'Error';
        }
    }
    public function search(Request $request){
        $search = $request->input('search');
        $category = $request->input('category');
        $query = School::select("*");
        if ( !empty($search) ){
            $query->where(function($query) use($search){
                $query->orWhere('category', 'LIKE', $search.'%')
                    ->orWhere('descr', 'LIKE', '%'.$search.'%')
                    ->orWhere('address', 'LIKE','%'.$search.'%')
                    ->orWhere('info', 'LIKE', '%'.$search.'%');
            });
        }
        if ( !empty($category) ){
            $query->where('category', $category);
        }
        $schools = $query->get()->toArray();
        return response()->json($schools);
    }
}