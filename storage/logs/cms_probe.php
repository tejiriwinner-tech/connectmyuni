<?php
require __DIR__ . "/backend/bootstrap.php";
use ConnectMyUni\Services\HeroSlideService;
use ConnectMyUni\Services\ServiceService;
use ConnectMyUni\Services\UniversityService;
use ConnectMyUni\Services\CountryService;
use ConnectMyUni\Services\EventService;
use ConnectMyUni\Services\TestimonialService;
use ConnectMyUni\Services\GalleryService;
use ConnectMyUni\Helpers\MediaResolver;

$tests = [
  "BASE_URL" => CONNECTMYUNI_BASE_URL,
  "hero" => fn() => (new HeroSlideService())->getActive(5),
  "services" => fn() => (new ServiceService())->getAllActive(),
  "universities_featured" => fn() => (new UniversityService())->getFeatured(),
  "countries_featured" => fn() => (new CountryService())->getFeatured(),
  "countries_all" => fn() => (new CountryService())->getAllWithUniversityCounts(),
  "events" => fn() => (new EventService())->getAllForPublic(),
  "testimonials" => fn() => (new TestimonialService())->getForPublic(6),
  "gallery" => fn() => (new GalleryService())->getForPublic(12),
];
foreach ($tests as $name => $fn) {
  if ($name === "BASE_URL") { echo "BASE_URL=".($fn)."  "; continue; }
  try { $r = $fn(); echo "$name: ".(is_array($r)?count($r)." rows":"scalar")."  "; }
  catch (\Throwable $e) { echo "$name ERR: ".$e->getMessage()."  "; }
}
echo "\n--- sample hero row ---\n";
try { $h = (new HeroSlideService())->getActive(5); if ($h) { var_export(array_keys($h[0])); echo "\n"; var_export($h[0]); } else { echo "empty\n"; } }
catch (\Throwable $e) { echo "hero ERR: ".$e->getMessage()."\n"; }
echo "\n--- sample service row keys ---\n";
try { $s = (new ServiceService())->getAllActive(); if ($s) var_export(array_keys($s[0])); else echo "empty\n"; }
catch (\Throwable $e) { echo "svc ERR: ".$e->getMessage()."\n"; }
echo "\n--- sample university row ---\n";
try { $u = (new UniversityService())->getFeatured(); if ($u){ var_export(array_keys($u[0])); echo "\n"; } else echo "empty\n"; }
catch (\Throwable $e) { echo "uni ERR: ".$e->getMessage()."\n"; }
echo "\n--- sample event row ---\n";
try { $e2 = (new EventService())->getAllForPublic(); if ($e2){ var_export(array_keys($e2[0])); echo "\n"; } else echo "empty\n"; }
catch (\Throwable $e) { echo "evt ERR: ".$e->getMessage()."\n"; }
echo "\n--- gallery row ---\n";
try { $g = (new GalleryService())->getForPublic(12); if ($g){ var_export(array_keys($g[0])); echo "\n"; } else echo "empty\n"; }
catch (\Throwable $e) { echo "gal ERR: ".$e->getMessage()."\n"; }

