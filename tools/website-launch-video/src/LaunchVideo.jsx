import React, { useEffect, useState } from 'react';
import { AbsoluteFill, Img, Sequence, continueRender, delayRender, staticFile, useCurrentFrame } from 'remotion';
import { DISCOUNT_FRAMES, SECTION_TRANSITION, CLOSING_START, CLOSING_FRAMES, CTA_FRAME, SECTIONS } from '../timing.mjs';

const GOLD = '#C89A2B';
const NAVY = '#061634';
const clamp = x => Math.max(0, Math.min(1, x));
const ease = x => { x = clamp(x); return x * x * (3 - 2 * x); };
const rise = (frame, start = 0, length = 18) => ({
  opacity: ease((frame - start) / length),
  transform: `translateY(${28 * (1 - ease((frame - start) / length))}px)`,
});

function Fonts() {
  const [handle] = useState(() => delayRender('Load the real website Cairo Arabic font'));
  useEffect(() => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = staticFile('fonts/cairo.css');
    link.onload = async () => {
      await Promise.all([400, 600, 700, 800, 900].map(w => document.fonts.load(`${w} 60px Cairo`, 'تم إطلاق الموقع الإلكتروني')));
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
  return <AbsoluteFill style={{ background: 'radial-gradient(ellipse at 65% 28%, #123566 0%, #091f44 40%, #061634 76%)' }}>
    <div style={{ position: 'absolute', top: 0, width: '100%', height: 6, background: GOLD }} />
    <div style={{ position: 'absolute', left: 88, right: 88, bottom: 182, height: 1, background: 'linear-gradient(90deg, transparent, #c89a2b66, transparent)' }} />
  </AbsoluteFill>;
}

function Emblem({ size = 190 }) {
  return <div style={{ width: size, height: size, borderRadius: '50%', overflow: 'hidden', background: '#fff', boxShadow: '0 18px 65px #0004', border: '2px solid #e4be60', position: 'relative' }}>
    <Img src={staticFile('logo.png')} style={{ position: 'absolute', width: '128%', height: '128%', maxWidth: 'none', left: '-14%', top: '-14%' }} />
  </div>;
}

function Intro() {
  const f = useCurrentFrame();
  return <AbsoluteFill style={{ opacity: 1 - ease((f - 83) / 13), alignItems: 'center', textAlign: 'center' }}>
    <div style={{ position: 'absolute', top: 370, ...rise(f, 0, 16) }}><Emblem size={218} /></div>
    <div style={{ position: 'absolute', top: 670, color: GOLD, fontSize: 27, fontWeight: 700, ...rise(f, 3, 17) }}>الموقع الرسمي</div>
    <div dir="rtl" style={{ position: 'absolute', top: 754, width: 930, fontSize: 98, fontWeight: 900, lineHeight: 1.45, ...rise(f, 5, 18) }}>
      <div>تم إطلاق</div>
      <div>الموقع الإلكتروني</div>
    </div>
    <div dir="rtl" style={{ position: 'absolute', top: 1090, width: 930, lineHeight: 1.85, ...rise(f, 11, 18) }}>
      <div style={{ fontSize: 39, fontWeight: 600, color: '#e2e9f3' }}>لنقابة أطباء الأسنان العراقيين</div>
      <div style={{ fontSize: 40, fontWeight: 800, color: '#d7ad4c' }}>فرع كربلاء المقدسة</div>
    </div>
    <div style={{ position: 'absolute', top: 1360, width: 76, height: 4, background: GOLD, ...rise(f, 15, 20) }} />
  </AbsoluteFill>;
}

function SiteFrame({ children, top = 190, height = 1535, scale = 1, translateY = 0, opacity = 1 }) {
  return <div style={{ position: 'absolute', width: 980, left: 50, top, height, overflow: 'hidden', borderRadius: 26, background: '#fff', border: '1px solid #ffffff55', boxShadow: '0 30px 85px #0006', transform: `translateY(${translateY}px) scale(${scale})`, opacity, transformOrigin: 'center center' }}>{children}</div>;
}

function Homepage() {
  const f = useCurrentFrame();
  const p = ease(f / 18);
  const screenshot = String(Math.min(131, f)).padStart(4, '0');
  return <AbsoluteFill style={{ opacity: p }}>
    <SiteFrame scale={0.96 + 0.04 * ease(f / 115)} translateY={42 * (1 - p)}>
      <Img src={staticFile(`hero-frames/${screenshot}.jpg`)} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
    </SiteFrame>
  </AbsoluteFill>;
}

function SectionShot({ item, number }) {
  const f = useCurrentFrame();
  const enter = ease(f / SECTION_TRANSITION);
  const progress = ease(f / (item.hold + SECTION_TRANSITION));
  const source = item.footage
    ? `discount-frames/${String(Math.min(DISCOUNT_FRAMES - 1, f)).padStart(4, '0')}.jpg`
    : item.file;
  return <AbsoluteFill style={{ opacity: enter, background: NAVY }}>
    <Background />
    <div dir="rtl" style={{ position: 'absolute', top: 186, left: 55, width: 970, textAlign: 'center', fontSize: 78, lineHeight: 1.4, color: '#edc86c', fontWeight: 900, whiteSpace: 'nowrap', ...rise(f, 1, SECTION_TRANSITION) }}>{item.label}</div>
    <div style={{ position: 'absolute', top: 329, left: 500, width: 80, height: 4, background: GOLD, opacity: enter }} />
    <SiteFrame top={392} height={1295} scale={0.985 + 0.015 * progress} translateY={18 * (1 - enter)}>
      <Img src={staticFile(source)} style={{ width: '100%', display: 'block', transform: item.footage ? undefined : `translateY(${-22 * progress}px)` }} />
    </SiteFrame>
    <div style={{ position: 'absolute', bottom: 201, width: '100%', display: 'flex', justifyContent: 'center', gap: 12, direction: 'rtl' }}>
      {SECTIONS.map((_, i) => <div key={i} style={{ width: i === number ? 40 : 8, height: 4, borderRadius: 3, background: i === number ? GOLD : '#ffffff35' }} />)}
    </div>
  </AbsoluteFill>;
}

function Closing() {
  const f = useCurrentFrame();
  const ctaStart = CTA_FRAME - CLOSING_START;
  const cta = ease((f - ctaStart + 4) / SECTION_TRANSITION);
  const enter = ease(f / SECTION_TRANSITION);
  const message = 1 - ease((f - ctaStart + 12) / 14);
  return <AbsoluteFill style={{ opacity: enter }}>
    <SiteFrame top={190} height={1535} scale={1 + 0.025 * ease(f / 230)}>
      <Img src={staticFile('home.png')} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
    </SiteFrame>
    <AbsoluteFill style={{ background: `linear-gradient(180deg, rgba(6,22,52,${0.12 + cta * 0.6}) 0%, rgba(6,22,52,${0.2 + cta * 0.6}) 27%, rgba(6,22,52,${0.8 + cta * 0.1}) 55%, #061634 91%)` }} />
    <div style={{ position: 'absolute', top: 946, width: '100%', textAlign: 'center', opacity: message }}>
      <div dir="rtl" style={{ fontSize: 66, fontWeight: 800, ...rise(f, 15, 18) }}>خدمات النقابة…</div>
      <div dir="rtl" style={{ fontSize: 83, fontWeight: 900, color: '#edc86c', marginTop: 6, ...rise(f, 22, 20) }}>الآن أقرب إليكم</div>
    </div>
    <div style={{ position: 'absolute', inset: 0, opacity: cta, textAlign: 'center', transform: `translateY(${20 * (1 - cta)}px)` }}>
      <div style={{ position: 'absolute', top: 345, width: '100%', display: 'flex', justifyContent: 'center' }}><Emblem size={184} /></div>
      <div dir="rtl" style={{ position: 'absolute', top: 575, width: '100%', fontSize: 31, color: '#eef1f8', fontWeight: 600 }}>نقابة أطباء الأسنان العراقيين</div>
      <div dir="rtl" style={{ position: 'absolute', top: 638, width: '100%', fontSize: 32, color: '#d7ad4c', fontWeight: 800 }}>فرع كربلاء المقدسة</div>
      <div dir="rtl" style={{ position: 'absolute', top: 815, width: '100%', fontSize: 89, fontWeight: 900 }}>زوروا الموقع الآن</div>
      <div dir="ltr" style={{ position: 'absolute', top: 1015, width: '100%', fontFamily: 'Arial, sans-serif', fontSize: 35, fontWeight: 500, color: '#f0d89b', whiteSpace: 'nowrap' }}>karbaladentalassociation.smartclinic.software</div>
      <div style={{ position: 'absolute', top: 1140, width: '100%', display: 'flex', justifyContent: 'center' }}>
        <Img src={staticFile('qr.png')} style={{ width: 280, height: 280, borderRadius: 18, boxShadow: '0 12px 40px #0004' }} />
      </div>
    </div>
  </AbsoluteFill>;
}

export function LaunchVideo() {
  return <AbsoluteFill style={{ color: '#fff', fontFamily: 'Cairo, sans-serif', overflow: 'hidden' }}>
    <Fonts /><Background />
    <Sequence from={0} durationInFrames={98}><Intro /></Sequence>
    <Sequence from={84} durationInFrames={144}><Homepage /></Sequence>
    {SECTIONS.map((item, i) => <Sequence key={item.file} from={item.start} durationInFrames={item.hold + SECTION_TRANSITION}><SectionShot item={item} number={i} /></Sequence>)}
    <Sequence from={CLOSING_START} durationInFrames={CLOSING_FRAMES}><Closing /></Sequence>
  </AbsoluteFill>;
}
