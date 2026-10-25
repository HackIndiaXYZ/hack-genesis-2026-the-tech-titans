const SRC=document.currentScript.src,B=SRC.slice(0,SRC.lastIndexOf('assets/')),$=id=>document.getElementById(id);
['https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Barlow:wght@400;500;600&display=swap','https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css'].forEach(h=>{const l=document.createElement('link');l.rel='stylesheet';l.href=h;document.head.appendChild(l)});
async function api(a,d={}){let f=d;if(!(d instanceof FormData)){f=new FormData();for(const k in d)f.append(k,d[k])}try{return await(await fetch(B+'api.php?a='+a,{method:'POST',body:f})).json()}catch(e){return{ok:0,err:'Server se connect nahi ho paya. Apache/MySQL chalu hai?'}}}
function esc(s){return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
function toast(m){const t=document.createElement('div');t.className='toast';t.textContent=m;document.body.appendChild(t);setTimeout(()=>t.remove(),3500)}
function mkMap(id,lat=22.5,lng=79,z=5){const m=L.map(id).setView([lat,lng],z);L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'© OpenStreetMap'}).addTo(m);return m}
const ico=e=>L.divIcon({html:'<div class="pin">'+e+'</div>',className:'',iconSize:[36,36],iconAnchor:[18,18]});
function km(a,b,c,d){const R=6371,r=Math.PI/180,x=(c-a)*r,y=(d-b)*r,h=Math.sin(x/2)**2+Math.cos(a*r)*Math.cos(c*r)*Math.sin(y/2)**2;return 2*R*Math.asin(Math.sqrt(h))}
function chat(box,room){const ms=box.querySelector('.msgs'),f=box.querySelector('form');let last=0;
 async function pull(){const r=await api('chat_fetch',{room,after:last});if(r.ok&&r.rows.length){r.rows.forEach(m=>{last=m.id;const d=document.createElement('div');d.className='m'+(m.mine?' me':'');d.innerHTML='<small>'+esc(m.name)+'</small>'+esc(m.body);ms.appendChild(d)});ms.scrollTop=ms.scrollHeight}}
 f.onsubmit=async e=>{e.preventDefault();const i=f.body;if(!i.value.trim())return;const v=i.value;i.value='';await api('chat_send',{room,body:v});pull()};pull();return setInterval(pull,2500)}
/* ---- panel shell (sidebar + topbar) and auth guard ---- */
const NAV={customer:[['index.html','🏠 Dashboard'],['support.html','💬 Support chat'],['profile.html','👤 My profile']],provider:[['index.html','🚚 Jobs'],['support.html','💬 Support chat'],['profile.html','👤 My profile']],admin:[['index.html','📊 Overview'],['users.html','👥 Users'],['services.html','🛠️ Services'],['support.html','💬 Support inbox'],['profile.html','👤 My profile']]};
const av=u=>u.avatar?`<img class="av" src="${B}uploads/${esc(u.avatar)}" alt="">`:`<span class="av">${esc((u.name||'?')[0].toUpperCase())}</span>`;
async function boot(role,title,init){
 const u=(await api('me')).u;
 if(!u||u.role!=role||u.status=='blocked'){location.href=B+'login.html?role='+role;return}
 const cur=location.pathname.split('/').pop()||'index.html',home=B+role+'/',s=document.createElement('div');
 s.innerHTML=`<aside class="side"><a class="logoMy" href="${B}index.html">My<b>Highway</b></a><div class="sign">${role[0].toUpperCase()+role.slice(1)} panel</div><nav>${NAV[role].map(n=>`<a href="${home+n[0]}" class="${cur==n[0]?'on':''}">${n[1]}</a>`).join('')}</nav><a class="out" href="#" id="lo">Logout</a></aside><main class="main"><header class="top"><h1>${title}</h1><a class="who" href="${home}profile.html">${av(u)}<span>${esc(u.name)}</span></a></header><section class="wrap" id="wrap"></section></main>`;
 document.title=title+' - MyHighway';document.body.prepend(s);$('wrap').appendChild($('page'));
 $('lo').onclick=async e=>{e.preventDefault();await api('logout');location.href=B+'login.html'};
 await init(u)}
async function pubNav(){const u=(await api('me')).u,n=$('nav');n.className='nav';
 n.innerHTML=`<a class="logo" href="${B}index.html">My<b id="hlogo">Highway</b></a><nav><a href="${B}index.html#services">Services</a><a href="${B}index.html#how">How it works</a><a href="${B}index.html#portals">Portals</a></nav><div>${u?`<a class="btn p sm" href="${B+u.role}/index.html">Open dashboard</a>`:`<a class="btn sm" href="${B}login.html">Login</a> <a class="btn p sm" href="${B}register.html">Sign up</a>`}</div>`;return u}
function profileUI(u){$('page').innerHTML=`<div class="card" style="max-width:720px"><div id="pmsg"></div><form id="pf"><div class="row" style="align-items:center;gap:16px">${av(u)}<div style="flex:1"><label class="f">Profile photo</label><input type="file" name="avatar" accept="image/*"></div></div>
<div class="grid-f"><div><label class="f">Full name</label><input name="name" value="${esc(u.name)}" required></div><div><label class="f">Email (login)</label><input value="${esc(u.email)}" disabled></div><div><label class="f">Phone</label><input name="phone" value="${esc(u.phone)}"></div><div><label class="f">Vehicle</label><input name="vehicle" value="${esc(u.vehicle)}"></div></div>
<label class="f">Address</label><input name="address" value="${esc(u.address)}">${u.role=='provider'?`<label class="f">Skills / services you offer</label><input name="skills" value="${esc(u.skills)}" placeholder="Towing, tyre, battery">`:''}
<h3 style="margin-top:22px">Change password</h3><div class="grid-f"><div><label class="f">Current password</label><input type="password" name="old_password"></div><div><label class="f">New password</label><input type="password" name="new_password" minlength="6"></div></div>
<button class="btn p" style="margin-top:18px">Save changes</button></form></div>`;
 $('pf').onsubmit=async e=>{e.preventDefault();const r=await api('profile_save',new FormData(e.target));$('pmsg').innerHTML=`<div class="note ${r.ok?'':'bad'}">${esc(r.ok?'Profile save ho gaya.':r.err)}</div>`;if(r.ok)setTimeout(()=>location.reload(),800)}}
/* ---- floating AI assistant ---- */
document.addEventListener('DOMContentLoaded',function(){const w=document.createElement('div');
 w.innerHTML='<button id="aiBtn" title="AI assistant">✨ Ask AI</button><div id="aiBox" hidden><div class="aih"><b>MyHighway AI</b><span id="aiX">✕</span></div><div class="msgs" id="aiM"><div class="m">Namaste! Gaadi ki problem batayein. Main sahi service, kharcha aur safety tips bataunga.</div></div><div class="chips"><a>Tyre puncture ho gaya</a><a>Petrol khatam ho gaya</a><a>Service ka price kya hai?</a></div><form id="aiF"><input placeholder="Yahan likhein..." autocomplete="off"><button class="btn p sm">Send</button></form></div>';
 document.body.appendChild(w);const box=$('aiBox'),M=$('aiM');
 $('aiBtn').onclick=()=>{box.hidden=!box.hidden};$('aiX').onclick=()=>{box.hidden=true};
 async function ask(t){if(!t.trim())return;M.insertAdjacentHTML('beforeend','<div class="m me">'+esc(t)+'</div>');const r=await api('ai',{msg:t});M.insertAdjacentHTML('beforeend','<div class="m">'+esc(r.ok?r.reply:'AI abhi available nahi hai.').replace(/\n/g,'<br>')+'</div>');M.scrollTop=M.scrollHeight}
 $('aiF').onsubmit=e=>{e.preventDefault();const i=e.target.querySelector('input');ask(i.value);i.value=''};
 box.querySelectorAll('.chips a').forEach(a=>a.onclick=()=>ask(a.textContent))});
