'use client';

import { useEffect, useRef, useState } from 'react';
import Typed from 'typed.js';
import VanillaTilt from 'vanilla-tilt';

export default function ClientHome({ profile, projects, skills, socials }: any) {
  const [isScrolled, setIsScrolled] = useState(false);
  const typedRef = useRef(null);
  const tiltRefs = useRef([]);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    if (profile?.roles && typedRef.current) {
      const typed = new Typed(typedRef.current, {
        strings: profile.roles.split(',').map((r: string) => r.trim()),
        typeSpeed: 65,
        backSpeed: 35,
        backDelay: 2200,
        loop: true,
      });
      return () => typed.destroy();
    }
  }, [profile]);

  useEffect(() => {
    const mouseGlow = document.getElementById('mouse-glow');
    const moveGlow = (e: MouseEvent) => {
      if (mouseGlow) {
        mouseGlow.style.opacity = '1';
        mouseGlow.style.left = e.clientX + 'px';
        mouseGlow.style.top = e.clientY + 'px';
      }
    };
    const hideGlow = () => {
      if (mouseGlow) mouseGlow.style.opacity = '0';
    };

    window.addEventListener('mousemove', moveGlow);
    window.addEventListener('mouseout', hideGlow);
    return () => {
      window.removeEventListener('mousemove', moveGlow);
      window.removeEventListener('mouseout', hideGlow);
    };
  }, []);

  const getIconClass = (platform: string) => {
    const p = platform.toLowerCase();
    if (p === 'github') return 'fa-github';
    if (p === 'instagram') return 'fa-instagram';
    if (p === 'linkedin') return 'fa-linkedin';
    return 'fa-link';
  };

  const githubLink = socials.find((s: any) => s.platform.toLowerCase() === 'github')?.url;

  return (
    <>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
      <div id="mouse-glow" className="mouse-glow"></div>

      {/* Navigation */}
      <nav id="nav" className={`fixed w-full z-50 transition-all duration-500 flex justify-between items-center px-6 md:px-14 ${isScrolled ? 'scrolled py-4' : 'py-6'}`}>
        <a href="#home" id="nav-logo" className="text-xl md:text-2xl font-bold tracking-widest uppercase text-white hover:opacity-70 transition-opacity">
          {profile?.short_name}.
        </a>
        <div className="flex gap-4 md:gap-8 text-xs uppercase tracking-[0.2em] font-medium hidden md:flex">
          <a href="#about" className="hover:text-white transition-colors">About</a>
          <a href="#projects" className="hover:text-white transition-colors">Work</a>
          <a href="#contact" className="hover:text-white transition-colors">Contact</a>
        </div>
        <div className="flex gap-4">
            <a href="/admin" className="btn-ghost !text-[10px] !px-4 !py-2">Admin</a>
            {githubLink && <a href={githubLink} target="_blank" className="hover:text-white transition-colors text-lg"><i className="fab fa-github"></i></a>}
        </div>
      </nav>

      {/* Hero */}
      <section id="home" className="min-h-screen flex items-center justify-center px-6 pt-20">
        <div className="max-w-4xl w-full text-center relative z-10 flex flex-col items-center">
          <p className="text-xs text-zinc-500 tracking-[.3em] uppercase mb-4">Hello, I am</p>
          <h1 id="hero-name" className="text-5xl md:text-7xl font-bold tracking-tight mb-4 text-white">
            {profile?.full_name}
          </h1>
          <div className="h-10 text-xl md:text-2xl font-light grad-text tracking-wide mb-6">
            <span ref={typedRef}></span>
          </div>
          <p id="hero-desc" className="text-sm md:text-base text-zinc-400 max-w-lg mb-10 leading-relaxed font-light">
            {profile?.hero_desc}
          </p>
          <div className="flex gap-4">
            <a href="#projects" className="btn">View My Work</a>
            <a href="#about" className="btn-ghost">About Me</a>
          </div>
        </div>
      </section>

      {/* About */}
      <section id="about" className="py-32 px-6 relative z-10 border-t border-white/5">
        <div className="max-w-6xl mx-auto">
          <div className="flex items-center gap-5 mb-14">
            <span className="text-xs text-zinc-500 tracking-[.2em] uppercase">01</span>
            <h2 className="text-2xl font-light tracking-wide text-white uppercase">About Me</h2>
            <div className="h-px bg-white/10 flex-grow max-w-xs"></div>
          </div>
          <div className="grid md:grid-cols-12 gap-12 items-center">
            <div className="md:col-span-7 space-y-6 text-sm text-zinc-400 leading-relaxed font-light">
              <div id="about-text-container" dangerouslySetInnerHTML={{__html: profile?.about_text ? profile.about_text.split('|').map((p: string) => `<p>${p}</p>`).join('') : ''}}></div>
              <div className="pt-6">
                <p className="text-white text-xs uppercase tracking-[.15em] mb-4">Technologies I frequently use:</p>
                <ul id="skills-container" className="grid grid-cols-2 md:grid-cols-3 gap-y-3 text-xs tracking-wider">
                  {skills?.map((s: any) => (
                    <li key={s.id} className="flex items-center gap-3">
                      <div className="w-1 h-1 bg-white rounded-full flex-shrink-0"></div>
                      {s.skill_name}
                    </li>
                  ))}
                </ul>
              </div>
            </div>
            <div className="md:col-span-5">
              <div className="glass rounded-2xl p-2">
                {profile?.profile_image && (
                  <img id="about-img" src={profile.profile_image} alt="Profile" className="w-full rounded-xl object-cover aspect-[4/5] filter grayscale-[30%] contrast-110" />
                )}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Projects */}
      <section id="projects" className="py-32 px-6 relative z-10">
        <div className="max-w-6xl mx-auto">
          <div className="flex items-center gap-5 mb-14">
            <span className="text-xs text-zinc-500 tracking-[.2em] uppercase">02</span>
            <h2 className="text-2xl font-light tracking-wide text-white uppercase">Selected Work</h2>
            <div className="h-px bg-white/10 flex-grow max-w-xs"></div>
          </div>
          <div id="projects-container" className="grid md:grid-cols-2 gap-10">
            {projects?.map((p: any) => (
              <div key={p.id} className="proj-card glass rounded-2xl overflow-hidden flex flex-col">
                <div className="h-56 overflow-hidden relative">
                  <img src={p.image} className="proj-img w-full h-full object-cover absolute inset-0" />
                  <div className="absolute inset-0 z-20 flex items-center justify-center gap-6 bg-black/80 opacity-0 hover:opacity-100 transition-opacity backdrop-blur-sm">
                    <a href={p.github_link} target="_blank" className="text-white/70 hover:text-white text-2xl transition-all hover:-translate-y-1">
                      <i className="fab fa-github"></i>
                    </a>
                  </div>
                </div>
                <div className="p-7 flex flex-col flex-grow">
                  <h3 className="text-lg font-medium text-white mb-2 tracking-wide">{p.title}</h3>
                  <p className="text-zinc-400 text-sm flex-grow leading-relaxed mb-6">{p.description}</p>
                  <div className="flex flex-wrap gap-3 text-xs text-zinc-500 uppercase tracking-widest">
                    {p.tags.split(',').map((t: string, i: number) => <span key={i}>{t.trim()}</span>)}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Contact */}
      <section id="contact" className="py-32 px-6 text-center relative z-10">
        <div className="max-w-2xl mx-auto glass p-14 rounded-3xl">
          <p className="text-zinc-500 text-xs tracking-[.2em] uppercase mb-5">03. Contact</p>
          <h2 className="text-4xl md:text-5xl font-light text-white mb-7 tracking-wide">Let&apos;s Connect</h2>
          <p className="text-zinc-400 text-sm leading-relaxed mb-10 max-w-md mx-auto">
            Whether it&apos;s a collab, freelance gig, or just saying hi — hit me up, I&apos;ll reply!
          </p>
          <a id="contact-email" href={`mailto:${profile?.email}`} className="btn">Send an Email</a>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-10 border-t border-white/5 text-center text-zinc-600 relative z-10">
        <div id="social-footer" className="flex justify-center gap-7 mb-6">
          {socials?.map((s: any) => s.url && s.url !== '#' && (
            <a key={s.id} href={s.url} target="_blank" className="hover:text-white transition-colors text-lg">
              <i className={`fab ${getIconClass(s.platform)}`}></i>
            </a>
          ))}
        </div>
        <p id="footer-name" className="text-[10px] tracking-[.2em] uppercase">Built by {profile?.full_name}</p>
      </footer>
    </>
  );
}
