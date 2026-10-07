<?php
require __DIR__ . "/backend/bootstrap.php";
use ConnectMyUni\Services\HeroSlideService;
use ConnectMyUni\Services\ServiceService;
use ConnectMyUni\Services\UniversityService;
use ConnectMyUni\Services\CountryService;
use ConnectMyUni\Services\EventService;
use ConnectMyUni\Services\TestimonialService;
use ConnectMyUni\Services\GalleryService;

echo "BASE_URL=".CONNECTMYUNI_BASE_URL."\n";
$tests = [
  "hero" => [new HeroSlideService(), "getActive", 5],
  "services" => [new ServiceService(), "getAllActive", 0],
  "uni_featured" => [new UniversityService(), "getFeatured", 0],
  "country_featured" => [new CountryService(), "getFeatured", 0],
  "country_all" => [new CountryService(), "getAllWithUniversityCounts", 0],
  "events" => [new EventService(), "getAllForPublic", 0],
  "testimonials" => [new TestimonialService(), "getForPublic", 6],
  "gallery" => [new GalleryService(), "getForPublic", 12],
];
foreach ($tests as $name => $spec) {
  [$svc,$method,$arg] = $spec;
  try { $r = $arg ? $svc->$method($arg) : $svc->$method(); echo "$name: ".(is_array($r)?count($r)." rows":"?".gettype($r))."\n"; }
  catch (\Throwable $e) { echo "$name ERR: ".$e->getMessage()."\n"; }
}
function dump($name,$arr){ echo "--- $name keys ---\n"; if(!$arr){echo "empty\n";return;} echo implode(",",array_keys($arr[0]))."\n"; var_export($arr[0]); echo "\n"; }
try { dump("hero",(new HeroSlideService())->getActive(5)); }catch(\Throwable $e){echo "hero ERR: ".$e->getMessage()."\n";}
try { dump("service",(new ServiceService())->getAllActive()); }catch(\Throwable $e){echo "svc ERR: ".$e->getMessage()."\n";}
try { dump("uni",(new UniversityService())->getFeatured()); }catch(\Throwable $e){echo "uni ERR: ".$e->getMessage()."\n";}
try { dump("event",(new EventService())->getAllForPublic()); }catch(\Throwable $e){echo "evt ERR: ".$e->getMessage()."\n";}
try { dump("gallery",(new GalleryService())->getForPublic(12)); }catch(\Throwable $e){echo "gal ERR: ".$e->getMessage()."\n";}
try { dump("country",(new CountryService())->getAllWithUniversityCounts()); }catch(\Throwable $e){echo "ctry ERR: ".$e->getMessage()."\n";}
try { dump("testi",(new TestimonialService())->getForPublic(6)); }catch(\Throwable $e){echo "tst ERR: ".$e->getMessage()."\n";}

