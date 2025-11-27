<?php
require __DIR__ . '/vendor/autoload.php';
use Carbon\Carbon;
$now = Carbon::now();
$s = $now->copy()->addDays(90)->toDateString();
$new = Carbon::parse($s);
echo "now: " . $now->toDateTimeString() . PHP_EOL;
echo "new due (string): $s" . PHP_EOL;
echo "new due parsed: " . $new->toDateTimeString() . PHP_EOL;
echo "diffInDays: " . $new->diffInDays($now) . PHP_EOL;
echo "diffInDaysSigned: " . $new->diffInDays($now, false) . PHP_EOL;
echo "new class: " . get_class($new) . PHP_EOL;
echo "now ts: {$now->timestamp}, new ts: {$new->timestamp}" . PHP_EOL;
// also try Carbon::now()->diffInDays
echo "now->diffInDays(new): " . $now->diffInDays($new) . PHP_EOL;
// print object debug
var_dump($new);
