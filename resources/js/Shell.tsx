import { useState,type ReactNode } from 'react';
import { router,usePage,Link } from '@inertiajs/react';
export default function Shell({children}:{children:ReactNode}) {
 const [dark,setDark]=useState(()=>localStorage.getItem('theme')==='dark');
 document.documentElement.dataset.theme=dark?'dark':'light';
 const {auth,flash}=usePage<{auth:{name:string}|null;flash:string|null}>().props;
 return <><header><Link className="brand" href="/"><b>02</b>DEBUG / TESTING</Link><div className="row">{auth&&<><small>{auth.name}</small><button onClick={()=>router.post('/logout')}>Keluar</button></>}<button aria-label="Ganti tema" onClick={()=>{localStorage.setItem('theme',dark?'light':'dark');setDark(!dark);}}>{dark?'Terang':'Gelap'}</button></div></header><main><div className="intro"><div><div className="eyebrow">Debugging & Testing · 02 / 05</div><h1>Webhook Inspector</h1><p>Terima request webhook, periksa payload yang telah diredaksi, dan uji pengiriman ulang ke penerima yang diizinkan.</p></div><span className="tag">BUKTI SEBELUM KESIMPULAN.</span></div>{flash&&<div className="status" role="status">{flash}</div>}{children}<footer className="note"><span>Webhook Inspector / AhmadNurHidayat-23</span><span>Ruang kerja developer.</span></footer></main></>;
}
