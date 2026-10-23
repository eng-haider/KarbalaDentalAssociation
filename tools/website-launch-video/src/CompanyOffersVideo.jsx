import React, { useEffect, useState } from 'react';
import { AbsoluteFill, Img, Sequence, continueRender, delayRender, staticFile, useCurrentFrame } from 'remotion';
import { OFFER_SCENES } from '../company-offers-timing.mjs';

const NAVY = '#061634';
const GOLD = '#d7ad4c';
const clamp = value => Math.max(0, Math.min(1, value));
const ease = value => {
  const t = clamp(value);
  return t * t * (3 - 2 * t);
};
const appear = (frame, start = 0, duration = 16) => ease((frame - start) / duration);

function Fonts() {
  const [handle] = useState(() => delayRender('Load Cairo Arabic font'));
  useEffect(() => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = staticFile('fonts/cairo.css');
    link.onload = async () => {
      await Promise.all([600, 700, 800, 900].map(weight => document.fonts.load(`${weight} 60px Cairo`, 'عروض الشركات المشاركة')));
      await document.fonts.ready;
      continueRender(handle);
    };
    link.onerror = () => { throw new Error('Cairo font stylesheet failed to load'); };
    document.head.appendChild(link);
    return () => link.remove();
  }, [handle]);
  return null;
}

function Background() {
  // The same OSIF hero gradient used on karbaladentalassociation.site/osif.
  return <AbsoluteFill style={{ background: 'radial-gradient(circle at 78% 34%, rgba(129, 39, 178, .75) 0, rgba(67, 13, 100, .42) 21%, transparent 46%), linear-gradient(128deg, #16021f 5%, #230335 52%, #47106a 100%)' }}>
    <div style={{ position: 'absolute', top: 0, width: '100%', height: 6, background: GOLD }} />
    <div style={{ position: 'absolute', left: 88, right: 88, bottom: 155, height: 1, background: 'linear-gradient(90deg, transparent, #d7ad4c77, transparent)' }} />
  </AbsoluteFill>;
}

function Emblem({ src, size = 178, ring = '#e4be60', crop = false }) {
  return <div style={{ width: size, height: size, borderRadius: '50%', overflow: 'hidden', background: '#fff', border: `2px solid ${ring}`, boxShadow: '0 18px 65px #0006' }}>
    <Img src={staticFile(src)} style={crop
      ? { width: '128%', height: '128%', maxWidth: 'none', marginLeft: '-14%', marginTop: '-14%' }
      : { width: '100%', height: '100%', objectFit: 'cover' }} />
  </div>;
}

function Intro() {
  const frame = useCurrentFrame();
  const show = appear(frame, 0, 16);
  const hide = 1 - appear(frame, 72, 18);
  return <AbsoluteFill style={{ alignItems: 'center', textAlign: 'center', opacity: hide }}>
    <div style={{ position: 'absolute', top: 248, width: '100%', display: 'flex', justifyContent: 'center', gap: 24, opacity: show, transform: `translateY(${28 * (1 - show)}px)` }}>
      <Emblem src="company-offers/logo.png" size={154} crop />
      <Emblem src="company-offers/osif-karbala-logo.jpg" size={154} ring="#d9a6ff" />
      <Emblem src="company-offers/sums-c.svg" size={154} ring="#9fc2fb" />
    </div>
    <div dir="rtl" style={{ position: 'absolute', top: 516, width: '100%', color: GOLD, fontSize: 28, fontWeight: 800, opacity: appear(frame, 5, 16) }}>OSIF KARBALA 2026</div>
    <div dir="rtl" style={{ position: 'absolute', top: 660, width: '100%', fontSize: 84, fontWeight: 900, lineHeight: 1.45, opacity: appear(frame, 10, 20), transform: `translateY(${28 * (1 - appear(frame, 10, 20))}px)` }}>عروض الشركات<br />المشاركة</div>
    <div dir="rtl" style={{ position: 'absolute', top: 965, width: '100%', fontSize: 38, fontWeight: 600, color: '#e2e9f3', opacity: appear(frame, 18, 18) }}>اختَر الشركة، أضف المنتجات، وأرسل طلبك</div>
  </AbsoluteFill>;
}

function BrowserFrame({ src, label, step, zoom = 1, fade = 1 }) {
  const frame = useCurrentFrame();
  const inProgress = appear(frame, 0, 18);
  const gentleZoom = zoom + 0.018 * ease(frame / 120);
  return <AbsoluteFill style={{ opacity: fade * inProgress }}>
    <div dir="rtl" style={{ position: 'absolute', top: 135, width: '100%', textAlign: 'center', color: GOLD, fontSize: 63, lineHeight: 1.35, fontWeight: 900, opacity: inProgress, transform: `translateY(${20 * (1 - inProgress)}px)` }}>{label}</div>
    <div style={{ position: 'absolute', top: 290, left: 500, width: 80, height: 4, background: GOLD, opacity: inProgress }} />
    <div style={{ position: 'absolute', top: 350, left: 75, width: 930, height: 1290, overflow: 'hidden', borderRadius: 28, background: '#fff', border: '1px solid #ffffff70', boxShadow: '0 30px 90px #0008', opacity: inProgress, transform: `translateY(${18 * (1 - inProgress)}px)` }}>
      <Img src={staticFile(`company-offers/${src}`)} style={{ width: '100%', height: '100%', objectFit: 'contain', display: 'block', transform: `scale(${gentleZoom})`, transformOrigin: 'center top' }} />
    </div>
    <div dir="rtl" style={{ position: 'absolute', top: 1690, width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 15, color: '#e9eff8', fontSize: 30, fontWeight: 700, opacity: appear(frame, 10, 16) }}>
      <span style={{ display: 'inline-flex', width: 44, height: 44, borderRadius: 22, alignItems: 'center', justifyContent: 'center', background: GOLD, color: NAVY, fontWeight: 900 }}>{step}</span>
      <span>{label}</span>
    </div>
  </AbsoluteFill>;
}

function SceneContent({ duration, children }) {
  const frame = useCurrentFrame();
  const fadeOut = 1 - appear(frame, duration - 16, 16);
  return <AbsoluteFill style={{ opacity: fadeOut }}>{children}</AbsoluteFill>;
}

function Scene({ from, duration, children }) {
  return <Sequence from={from} durationInFrames={duration}><SceneContent duration={duration}>{children}</SceneContent></Sequence>;
}

function Final() {
  const frame = useCurrentFrame();
  const show = appear(frame, 0, 15);
  return <AbsoluteFill style={{ opacity: show }}>
    <div dir="rtl" style={{ position: 'absolute', top: 220, width: '100%', textAlign: 'center', color: GOLD, fontSize: 70, lineHeight: 1.3, fontWeight: 900 }}>طلبك جاهز في واتساب</div>
    <div style={{ position: 'absolute', top: 405, left: 150, width: 780, height: 830, overflow: 'hidden', borderRadius: 28, background: '#fff', boxShadow: '0 30px 90px #0008', transform: `scale(${0.98 + 0.02 * show})` }}>
      <Img src={staticFile('company-offers/whatsapp-ready.png')} style={{ width: '100%', height: '100%', objectFit: 'contain', display: 'block' }} />
    </div>
    <div dir="rtl" style={{ position: 'absolute', top: 1370, width: '100%', padding: '0 100px', textAlign: 'center', fontSize: 46, lineHeight: 1.65, fontWeight: 700, color: '#eef3fa', opacity: appear(frame, 10, 16) }}>اضغط «إرسال» داخل واتساب لتصل رسالة الطلب إلى الشركة</div>
    <div style={{ position: 'absolute', top: 1655, width: '100%', textAlign: 'center', color: '#f0d89b', fontFamily: 'Arial, sans-serif', fontSize: 29 }}>karbaladentalassociation.site/discounts</div>
  </AbsoluteFill>;
}

export function CompanyOffersVideo() {
  return <AbsoluteFill style={{ fontFamily: 'Cairo, sans-serif', color: '#fff', overflow: 'hidden' }}>
    <Fonts /><Background />
    <Sequence from={0} durationInFrames={90}><Intro /></Sequence>
    <Scene from={66} duration={120}><BrowserFrame src="discounts.png" step={OFFER_SCENES[0].step} label={OFFER_SCENES[0].title} zoom={0.995} /></Scene>
    <Scene from={156} duration={150}><BrowserFrame src="participating-companies.png" step={OFFER_SCENES[1].step} label={OFFER_SCENES[1].title} zoom={0.99} /></Scene>
    <Scene from={276} duration={150}><BrowserFrame src="product-added.png" step={OFFER_SCENES[2].step} label={OFFER_SCENES[2].title} zoom={1.01} /></Scene>
    <Scene from={396} duration={150}><BrowserFrame src="cart.png" step={OFFER_SCENES[3].step} label={OFFER_SCENES[3].title} zoom={1.01} /></Scene>
    <Sequence from={528} durationInFrames={72}><Final /></Sequence>
  </AbsoluteFill>;
}
