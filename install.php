<?php
define('INSTALLING',1); require 'config.php';
$c=new mysqli(DB_HOST,DB_USER,DB_PASS); if($c->connect_errno)die('MySQL connect fail: '.e($c->connect_error));
$c->query("CREATE DATABASE IF NOT EXISTS `".DB_NAME."` CHARACTER SET utf8mb4"); $c->select_db(DB_NAME); $c->set_charset('utf8mb4');
if($c->multi_query(file_get_contents(__DIR__.'/database.sql'))){do{if($r=$c->store_result())$r->free();}while($c->more_results()&&$c->next_result());}
if($c->query("SELECT COUNT(*) n FROM services")->fetch_assoc()['n']==0){
 foreach([['Towing','🚛','Crane / flatbed towing to nearest garage',1200],['Tyre Repair','🛞','Puncture repair or spare tyre fitting',250],['Fuel Delivery','⛽','Petrol / diesel delivered to you (5 L)',400],['Battery Jump-Start','🔋','Jump-start or battery replacement',350],['Mechanic On-Spot','🔧','Minor engine, brake, clutch repair',500],['Lockout Help','🔑','Keys locked inside the car',300],['Accident Recovery','🚨','Recovery and towing after an accident',2000]] as $s)
  $c->query("INSERT INTO services(name,icon,descr,base_price) VALUES('".$c->real_escape_string($s[0])."','$s[1]','".$c->real_escape_string($s[2])."',$s[3])");}
if($c->query("SELECT COUNT(*) n FROM users")->fetch_assoc()['n']==0){
 foreach([['Admin','admin@highway.com','admin123','admin','active',0,null,null],['Ravi Provider','provider@highway.com','provider123','provider','active',1,28.9845,77.7064],['Demo Customer','customer@highway.com','customer123','customer','active',0,null,null]] as $u){
  $h=password_hash($u[2],PASSWORD_DEFAULT);$lat=$u[6]??'NULL';$lng=$u[7]??'NULL';
  $c->query("INSERT INTO users(name,email,password,role,status,online,lat,lng,phone,vehicle) VALUES('$u[0]','$u[1]','$h','$u[3]','$u[4]',$u[5],$lat,$lng,'9999999999','Tow Truck / Car')");}}
echo '<body style="font:16px system-ui;background:#14171c;color:#eee;padding:40px"><h2>✅ Install complete</h2><p>Admin: admin@highway.com / admin123<br>Provider: provider@highway.com / provider123<br>Customer: customer@highway.com / customer123</p><p><b>Security:</b> ab install.php delete kar dein aur passwords badal dein.</p><p><a style="color:#f5c400" href="index.html">Website kholein →</a></p></body>';
