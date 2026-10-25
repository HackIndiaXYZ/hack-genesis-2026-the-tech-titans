<?php require 'config.php'; header('Content-Type: application/json');
$a=$_GET['a']??''; $u=me(); $P=$_POST;
function out($x){echo json_encode($x);exit;} function fail($m){out(['ok'=>0,'err'=>$m]);}
function room_ok($u,$room){
 if($u['role']=='admin')return true; if($room=='sup_'.$u['id'])return true;
 if(preg_match('/^req_(\d+)$/',$room,$m)){$r=q("SELECT customer_id,provider_id FROM requests WHERE id=?",'i',$m[1]);return $r&&($r[0]['customer_id']==$u['id']||$r[0]['provider_id']==$u['id']);}
 return false;}
function ai_local($m){
 $R=[['/accident|takkar|crash|injur|ghayal|ambulance/i',7,"⚠️ Sabse pehle safety: gaadi ko side me lagayein, hazard lights on karein aur road se door khade hon. Koi ghayal ho to turant 112 ya 108 par call karein. Uske baad 'Accident Recovery' request bhejein, hum recovery aur towing arrange karenge."],
 ['/tow|crane|khich|stuck|fas gayi|band ho gayi/i',1,"Gaadi chalne layak nahi hai to Towing best rahegi. Gaadi neutral me chhodein, hazard lights on rakhein aur safe jagah par rukein. Provider aapke GPS location par aayega."],
 ['/tyre|tire|puncture|punchar|panchar|flat/i',2,"Lagta hai tyre puncture hai. Highway par khud tyre na badlein agar shoulder safe nahi hai. 'Tyre Repair' request bhejein, provider spare ya puncture repair kar dega."],
 ['/fuel|petrol|diesel|tank|empty|khatam/i',3,"Fuel khatam hone par 'Fuel Delivery' choose karein. Provider 5 litre fuel le kar aayega, taaki aap agle pump tak pahunch sakein."],
 ['/battery|jump|self|start nahi|start nhi|click/i',4,"Start na hona aksar battery ki wajah se hota hai. 'Battery Jump-Start' se mostly gaadi turant start ho jati hai. Lights ya AC on ho to band kar dein."],
 ['/engine|overheat|garam|smoke|dhuan|dhua|brake|clutch|mechanic|awaaz|noise/i',5,"Engine garam ho ya dhuan aa raha ho to gaadi turant band kar dein aur bonnet tab tak na kholein jab tak thanda na ho jaye. 'Mechanic On-Spot' request karein."],
 ['/lock|key|chabi|chaabi/i',6,"Chaabi andar reh gayi hai to 'Lockout Help' choose karein. Provider bina damage ke gaadi khol dega."]];
 foreach($R as $r)if(preg_match($r[0],$m))return['reply'=>$r[2],'service'=>$r[1]];
 if(preg_match('/price|cost|charge|kitna|rate|paisa|fees/i',$m)){$l=array_map(fn($s)=>"{$s['icon']} {$s['name']}: from ₹{$s['base_price']}",q("SELECT * FROM services"));return['reply'=>"Starting prices:\n".implode("\n",$l)."\nFinal amount kaam ke baad provider batata hai, distance aur kaam ke hisaab se.",'service'=>0];}
 if(preg_match('/eta|kitni der|kab tak|time|wait/i',$m))return['reply'=>"Provider accept karte hi aap map par live dekh sakte hain, aur ETA distance ke hisaab se auto update hota hai (avg 40 km/h).",'service'=>0];
 if(preg_match('/hi|hello|namaste|hey/i',$m))return['reply'=>"Namaste! Batayein gaadi ko kya hua hai: tyre, fuel, battery, engine, ya accident?",'service'=>0];
 return['reply'=>"Main samajh nahi paya. Kripya problem thodi detail me likhein, jaise 'tyre puncture', 'petrol khatam' ya 'engine garam'. Emergency ho to 112 par call karein.",'service'=>0];}
function ai_reply($m){
 $loc=ai_local($m);
 if(AI_API_KEY&&function_exists('curl_init')){
  $sv=implode(', ',array_map(fn($s)=>"{$s['id']}={$s['name']} (from Rs {$s['base_price']})",q("SELECT * FROM services")));
  $ch=curl_init('https://api.anthropic.com/v1/messages');
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>1,CURLOPT_POST=>1,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['content-type: application/json','x-api-key: '.AI_API_KEY,'anthropic-version: 2023-06-01'],
   CURLOPT_POSTFIELDS=>json_encode(['model'=>AI_MODEL,'max_tokens'=>400,'system'=>"You are HighwayGo's roadside assistant for Indian highways. Reply in the user's language (Hinglish is fine), short, practical, safety first. Services: $sv. If danger or injury, tell them to call 112.",'messages'=>[['role'=>'user','content'=>$m]]])]);
  $r=json_decode(curl_exec($ch),true); if(!empty($r['content'][0]['text']))$loc['reply']=$r['content'][0]['text'];}
 return $loc;}
if($a=='ai'){$m=trim($P['msg']??'');if($m=='')fail('empty');$r=ai_reply(mb_substr($m,0,500));out(['ok'=>1,'reply'=>$r['reply'],'service'=>$r['service']]);}
function pub($x){if($x)unset($x['password']);return $x;}
if($a=='public')out(['ok'=>1,'services'=>q("SELECT * FROM services ORDER BY id"),'st'=>q("SELECT (SELECT COUNT(*) FROM users WHERE role='provider' AND online=1 AND status='active') pon,(SELECT COUNT(*) FROM requests WHERE status='completed') done,(SELECT COUNT(*) FROM users WHERE role='customer') cus")[0]]);
if($a=='me')out(['ok'=>1,'u'=>pub($u)]);
if($a=='logout'){$_SESSION=[];session_destroy();out(['ok'=>1]);}
if($a=='login'){
 $x=q("SELECT * FROM users WHERE email=?",'s',trim($P['email']??''))[0]??null;
 if(!$x||!password_verify($P['password']??'',$x['password']))fail('Email ya password galat hai.');
 if($x['status']=='blocked')fail('Aapka account blocked hai. Support se sampark karein.');
 session_regenerate_id(true);$_SESSION['uid']=$x['id'];out(['ok'=>1,'role'=>$x['role']]);}
if($a=='register'){
 $g=fn($k)=>trim($P[$k]??'');$role=$g('role')=='provider'?'provider':'customer';
 if($g('name')==''||!filter_var($g('email'),FILTER_VALIDATE_EMAIL))fail('Sahi naam aur email daalein.');
 if(strlen($P['password']??'')<6)fail('Password kam se kam 6 character ka ho.');
 if(q("SELECT id FROM users WHERE email=?",'s',$g('email')))fail('Ye email pehle se registered hai.');
 $id=q("INSERT INTO users(name,email,phone,password,role,status,vehicle,skills) VALUES(?,?,?,?,?,?,?,?)",'ssssssss',$g('name'),$g('email'),$g('phone'),password_hash($P['password'],PASSWORD_DEFAULT),$role,$role=='provider'?'pending':'active',$g('vehicle'),$g('skills'));
 session_regenerate_id(true);$_SESSION['uid']=$id;out(['ok'=>1,'role'=>$role]);}
if(!$u||$u['status']=='blocked')fail('login');
$ACT="'pending','accepted','on_the_way','working'";
switch($a){
case 'loc': q("UPDATE users SET lat=?,lng=?,last_seen=NOW() WHERE id=?",'ddi',(float)$P['lat'],(float)$P['lng'],$u['id']); out(['ok'=>1]);
case 'online': if($u['role']!='provider'||$u['status']!='active')fail('not allowed'); q("UPDATE users SET online=? WHERE id=?",'ii',(int)$P['on'],$u['id']); out(['ok'=>1]);
case 'req_create':
 if($u['role']!='customer')fail('customer only');
 if(q("SELECT id FROM requests WHERE customer_id=? AND status IN($ACT)",'i',$u['id']))fail('Aapki ek request pehle se active hai.');
 if(!q("SELECT id FROM services WHERE id=?",'i',(int)$P['service_id']))fail('Service select karein.');
 if(empty($P['lat'])||empty($P['lng']))fail('Map par location select karein.');
 $id=q("INSERT INTO requests(customer_id,service_id,vehicle,note,lat,lng,address) VALUES(?,?,?,?,?,?,?)",'iissdds',$u['id'],(int)$P['service_id'],mb_substr($P['vehicle'],0,120),mb_substr($P['note'],0,255),(float)$P['lat'],(float)$P['lng'],mb_substr($P['address'],0,255)); out(['ok'=>1,'id'=>$id]);
case 'active':
 $r=q("SELECT r.*,s.name sname,s.icon,p.name pname,p.phone pphone,p.vehicle pveh,p.lat plat,p.lng plng FROM requests r JOIN services s ON s.id=r.service_id LEFT JOIN users p ON p.id=r.provider_id WHERE r.customer_id=? AND r.status IN($ACT) ORDER BY r.id DESC LIMIT 1",'i',$u['id']); out(['ok'=>1,'r'=>$r[0]??null]);
case 'cancel': q("UPDATE requests SET status='cancelled' WHERE id=? AND customer_id=? AND status IN('pending','accepted')",'ii',(int)$P['id'],$u['id']); out(['ok'=>1]);
case 'rate': q("UPDATE requests SET rating=? WHERE id=? AND customer_id=? AND status='completed' AND rating IS NULL",'iii',max(1,min(5,(int)$P['rating'])),(int)$P['id'],$u['id']); out(['ok'=>1]);
case 'open':
 if($u['role']!='provider')fail('provider only'); $lat=(float)$u['lat'];$lng=(float)$u['lng'];
 $job=q("SELECT r.*,s.name sname,s.icon,s.base_price,c.name cname,c.phone cphone FROM requests r JOIN services s ON s.id=r.service_id JOIN users c ON c.id=r.customer_id WHERE r.provider_id=? AND r.status IN('accepted','on_the_way','working') LIMIT 1",'i',$u['id']);
 $rows=[]; if(!$job&&$u['status']=='active'&&$u['online'])$rows=q("SELECT r.id,r.address,r.note,r.vehicle,r.lat,r.lng,s.name sname,s.icon,s.base_price,c.name cname,ROUND(6371*ACOS(LEAST(1,COS(RADIANS(?))*COS(RADIANS(r.lat))*COS(RADIANS(r.lng)-RADIANS(?))+SIN(RADIANS(?))*SIN(RADIANS(r.lat)))),1) dist FROM requests r JOIN services s ON s.id=r.service_id JOIN users c ON c.id=r.customer_id WHERE r.status='pending' ORDER BY dist LIMIT 20",'ddd',$lat,$lng,$lat);
 out(['ok'=>1,'rows'=>$rows,'job'=>$job[0]??null]);
case 'accept':
 if($u['role']!='provider'||$u['status']!='active'||!$u['online'])fail('Pehle online ho jayein (approved provider).');
 if(q("SELECT id FROM requests WHERE provider_id=? AND status IN('accepted','on_the_way','working')",'i',$u['id']))fail('Pehle current job complete karein.');
 q("UPDATE requests SET provider_id=?,status='accepted' WHERE id=? AND status='pending'",'ii',$u['id'],(int)$P['id']); if($db->affected_rows<1)fail('Ye request kisi aur ne le li.'); out(['ok'=>1]);
case 'status':
 $st=$P['status']??''; if(!in_array($st,['on_the_way','working','completed']))fail('bad status');
 $pr=$st=='completed'?max(0,(int)$P['price']):0;
 q("UPDATE requests SET status=?,price=IF(?='completed',?,price) WHERE id=? AND provider_id=?",'ssiii',$st,$st,$pr,(int)$P['id'],$u['id']); out(['ok'=>1]);
case 'live':
 if($u['role']!='admin')fail('admin only');
 out(['ok'=>1,'providers'=>q("SELECT id,name,lat,lng,online,last_seen FROM users WHERE role='provider' AND lat IS NOT NULL"),'reqs'=>q("SELECT r.id,r.lat,r.lng,r.status,s.name sname FROM requests r JOIN services s ON s.id=r.service_id WHERE r.status IN($ACT)")]);
case 'chat_send': $room=$P['room']??''; $b=trim($P['body']??''); if(!room_ok($u,$room)||$b=='')fail('no'); q("INSERT INTO messages(room,sender_id,body) VALUES(?,?,?)",'sis',$room,$u['id'],mb_substr($b,0,1000)); out(['ok'=>1]);
case 'chat_fetch':
 $room=$P['room']??''; if(!room_ok($u,$room))fail('no');
 $rows=q("SELECT m.id,m.body,m.created,m.sender_id,x.name FROM messages m JOIN users x ON x.id=m.sender_id WHERE m.room=? AND m.id>? ORDER BY m.id LIMIT 100",'si',$room,(int)$P['after']);
 foreach($rows as &$r){$r['mine']=$r['sender_id']==$u['id'];} out(['ok'=>1,'rows'=>$rows]);
case 'profile_save':
 $g=fn($k)=>trim($P[$k]??''); if($g('name')=='')fail('Naam likhna zaruri hai.');
 $av=$u['avatar'];
 if(!empty($_FILES['avatar']['tmp_name'])){
  if($_FILES['avatar']['size']>2097152)fail('Photo 2 MB se choti honi chahiye.');
  $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][mime_content_type($_FILES['avatar']['tmp_name'])]??null;
  if(!$ext)fail('Sirf JPG/PNG/WEBP photo allowed hai.');
  $av='u'.$u['id'].'_'.time().'.'.$ext; move_uploaded_file($_FILES['avatar']['tmp_name'],__DIR__.'/uploads/'.$av);}
 if($g('new_password')!==''){
  if(!password_verify($P['old_password']??'',$u['password']))fail('Purana password galat hai.');
  if(strlen($P['new_password'])<6)fail('Naya password kam se kam 6 character ka ho.');
  q("UPDATE users SET password=? WHERE id=?",'si',password_hash($P['new_password'],PASSWORD_DEFAULT),$u['id']);}
 q("UPDATE users SET name=?,phone=?,address=?,vehicle=?,skills=?,avatar=? WHERE id=?",'ssssssi',$g('name'),$g('phone'),$g('address'),$g('vehicle'),isset($P['skills'])?$g('skills'):$u['skills'],$av,$u['id']); out(['ok'=>1]);
case 'history': out(['ok'=>1,'rows'=>q("SELECT r.*,s.name sname,s.icon,p.name pname FROM requests r JOIN services s ON s.id=r.service_id LEFT JOIN users p ON p.id=r.provider_id WHERE r.customer_id=? ORDER BY r.id DESC LIMIT 8",'i',$u['id'])]);
case 'pstats':
 out(['ok'=>1,'all'=>q("SELECT COUNT(*) n,COALESCE(SUM(price),0) t,ROUND(AVG(rating),1) a FROM requests WHERE provider_id=? AND status='completed'",'i',$u['id'])[0],'today'=>q("SELECT COUNT(*) n,COALESCE(SUM(price),0) t FROM requests WHERE provider_id=? AND status='completed' AND DATE(updated)=CURDATE()",'i',$u['id'])[0]]);
case 'adm_stats':
 if($u['role']!='admin')fail('admin only');
 out(['ok'=>1,'s'=>q("SELECT (SELECT COUNT(*) FROM users WHERE role='customer') cu,(SELECT COUNT(*) FROM users WHERE role='provider') pr,(SELECT COUNT(*) FROM users WHERE role='provider' AND status='pending') pend,(SELECT COUNT(*) FROM users WHERE role='provider' AND online=1) onl,(SELECT COUNT(*) FROM requests) rq,(SELECT COUNT(*) FROM requests WHERE status IN($ACT)) act,(SELECT COALESCE(SUM(price),0) FROM requests WHERE status='completed') rev")[0],
 'rows'=>q("SELECT r.id,r.status,r.price,r.created,s.name sname,c.name cname,p.name pname FROM requests r JOIN services s ON s.id=r.service_id JOIN users c ON c.id=r.customer_id LEFT JOIN users p ON p.id=r.provider_id ORDER BY r.id DESC LIMIT 12")]);
case 'adm_users':
 if($u['role']!='admin')fail('admin only'); $f=$P['role']??'';
 out(['ok'=>1,'me'=>$u['id'],'rows'=>in_array($f,['customer','provider','admin'])?q("SELECT id,name,email,phone,role,status,created FROM users WHERE role=? ORDER BY id DESC",'s',$f):q("SELECT id,name,email,phone,role,status,created FROM users ORDER BY id DESC")]);
case 'adm_user_act':
 if($u['role']!='admin')fail('admin only'); $id=(int)$P['id']; $act=$P['act']??''; if($id==$u['id'])fail('Apne account par ye action nahi ho sakta.');
 if($act=='approve'||$act=='unblock')q("UPDATE users SET status='active' WHERE id=?",'i',$id);
 elseif($act=='block')q("UPDATE users SET status='blocked',online=0 WHERE id=?",'i',$id);
 elseif($act=='delete')q("DELETE FROM users WHERE id=? AND role<>'admin'",'i',$id);
 out(['ok'=>1]);
case 'svc_add': case 'svc_save': case 'svc_del':
 if($u['role']!='admin')fail('admin only'); $g=fn($k)=>trim($P[$k]??'');
 if($a=='svc_add'){if($g('name')=='')fail('Service ka naam likhein.');q("INSERT INTO services(name,icon,descr,base_price) VALUES(?,?,?,?)",'sssi',$g('name'),$g('icon')?:'🛠️',$g('descr'),(int)$g('base_price'));}
 if($a=='svc_save')q("UPDATE services SET name=?,icon=?,descr=?,base_price=? WHERE id=?",'sssii',$g('name'),$g('icon'),$g('descr'),(int)$g('base_price'),(int)$g('id'));
 if($a=='svc_del')q("DELETE FROM services WHERE id=?",'i',(int)$g('id'));
 out(['ok'=>1]);
case 'sup_rooms':
 if($u['role']!='admin')fail('admin only');
 out(['ok'=>1,'rows'=>q("SELECT u.id,u.name,u.role,(SELECT body FROM messages WHERE room=CONCAT('sup_',u.id) ORDER BY id DESC LIMIT 1) last FROM users u WHERE EXISTS(SELECT 1 FROM messages WHERE room=CONCAT('sup_',u.id)) ORDER BY (SELECT MAX(id) FROM messages WHERE room=CONCAT('sup_',u.id)) DESC")]);
}
fail('unknown');
