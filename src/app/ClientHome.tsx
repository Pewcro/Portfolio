'use client';

import { useEffect, useRef, useState } from 'react';
import Typed from 'typed.js';
import { motion } from 'framer-motion';

export default function ClientHome({ profile, projects, skills, socials }: any) {
  const [isScrolled, setIsScrolled] = useState(false);
  const typedRef = useRef(null);

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
        <motion.div 
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, ease: [0.23, 1, 0.32, 1] }}
          className="max-w-4xl w-full text-center relative z-10 flex flex-col items-center"
        >
          <p className="text-xs text-zinc-500 tracking-[.4em] uppercase mb-6 font-medium">Hello, I am</p>
          <h1 id="hero-name" className="text-5xl md:text-7xl lg:text-8xl font-bold tracking-tighter mb-4 text-white">
            {profile?.full_name}
          </h1>
          <div className="h-10 text-xl md:text-2xl font-light grad-text tracking-widest mb-8">
            <span ref={typedRef}></span>
          </div>
          <p id="hero-desc" className="text-sm md:text-base text-zinc-400 max-w-lg mb-12 leading-loose font-light">
            {profile?.hero_desc}
          </p>
          <div className="flex gap-6">
            <a href="#projects" className="btn">View My Work</a>
            <a href="#about" className="btn-ghost">About Me</a>
          </div>
        </motion.div>
      </section>

      {/* About */}
      <section id="about" className="py-32 px-6 relative z-10 border-t border-white/5">
        <motion.div 
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.8 }}
          className="max-w-6xl mx-auto"
        >
          <div className="flex items-center gap-5 mb-16">
            <span className="text-xs text-zinc-500 tracking-[.3em] uppercase">01</span>
            <h2 className="text-3xl font-light tracking-wide text-white uppercase">About Me</h2>
            <div className="h-px bg-white/10 flex-grow max-w-xs"></div>
          </div>
          <div className="grid md:grid-cols-12 gap-16 items-center">
            <div className="md:col-span-7 space-y-6 text-sm text-zinc-400 leading-loose font-light">
              <div id="about-text-container" dangerouslySetInnerHTML={{__html: profile?.about_text ? profile.about_text.split('|').map((p: string) => `<p>${p}</p>`).join('') : ''}}></div>
              <div className="pt-8">
                <p className="text-white text-xs uppercase tracking-[.2em] mb-6 font-medium">Technologies I frequently use:</p>
                <ul id="skills-container" className="grid grid-cols-2 md:grid-cols-3 gap-y-4 text-xs tracking-widest">
                  {skills?.map((s: any) => (
                    <li key={s.id} className="flex items-center gap-4">
                      <div className="w-1.5 h-1.5 bg-indigo-500 rounded-full flex-shrink-0 shadow-[0_0_10px_rgba(99,102,241,0.8)]"></div>
                      <span className="text-zinc-300">{s.skill_name}</span>
                    </li>
                  ))}
                </ul>
              </div>
            </div>
            <div className="md:col-span-5">
              <div className="glass rounded-3xl p-3 transform transition-transform duration-700 hover:scale-[1.02]">
                {profile?.profile_image && (
                  <img id="about-img" src={profile.profile_image} alt="Profile" className="w-full rounded-2xl object-cover aspect-[4/5] filter grayscale-[20%] contrast-110" />
                )}
              </div>
            </div>
          </div>
        </motion.div>
      </section>

      {/* Projects */}
      <section id="projects" className="py-32 px-6 relative z-10">
        <div className="max-w-6xl mx-auto">
          <motion.div 
            initial={{ opacity: 0, y: 40 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.8 }}
            className="flex items-center gap-5 mb-16"
          >
            <span className="text-xs text-zinc-500 tracking-[.3em] uppercase">02</span>
            <h2 className="text-3xl font-light tracking-wide text-white uppercase">Selected Work</h2>
            <div className="h-px bg-white/10 flex-grow max-w-xs"></div>
          </motion.div>
          <div id="projects-container" className="grid md:grid-cols-2 gap-12">
            {projects?.map((p: any, index: number) => (
              <motion.div 
                key={p.id} 
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, margin: "-100px" }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                whileHover={{ y: -10, scale: 1.02 }}
                className="proj-card glass rounded-3xl overflow-hidden flex flex-col group cursor-pointer"
              >
                <div className="h-72 overflow-hidden relative">
                  <div className="absolute inset-0 bg-gradient-to-t from-[#030303] via-transparent to-transparent z-10 opacity-80"></div>
                  <img src={p.image} alt={p.title} className="proj-img w-full h-full object-cover absolute inset-0 transition-transform duration-1000 group-hover:scale-110 group-hover:rotate-1" />
                  <div className="absolute inset-0 z-20 flex items-center justify-center gap-6 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-sm">
                    <a href={p.github_link} target="_blank" className="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center text-white/90 hover:text-white hover:bg-indigo-600 transition-all hover:scale-110 border border-white/20 shadow-xl">
                      <i className="fab fa-github text-2xl"></i>
                    </a>
                  </div>
                </div>
                <div className="p-8 flex flex-col flex-grow relative z-20 bg-gradient-to-b from-transparent to-black/60">
                  <h3 className="text-2xl font-light text-white mb-4 tracking-wide group-hover:text-indigo-400 transition-colors">{p.title}</h3>
                  <p className="text-zinc-400 text-sm flex-grow leading-loose mb-8 font-light">{p.description}</p>
                  <div className="flex flex-wrap gap-2 text-[10px] text-zinc-300 uppercase tracking-[0.2em] font-semibold">
                    {p.tags.split(',').map((t: string, i: number) => (
                      <span key={i} className="px-3 py-1.5 rounded-full bg-white/5 border border-white/10 shadow-sm">{t.trim()}</span>
                    ))}
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Contact */}
      <section id="contact" className="py-32 px-6 text-center relative z-10">
        <motion.div 
          initial={{ opacity: 0, scale: 0.95, y: 30 }}
          whileInView={{ opacity: 1, scale: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.8, ease: "easeOut" }}
          className="max-w-3xl mx-auto glass p-16 rounded-[2.5rem] relative overflow-hidden group"
        >
          <div className="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-cyan-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
          <p className="text-zinc-500 text-xs tracking-[.4em] uppercase mb-6 font-medium relative z-10">03. Contact</p>
          <h2 className="text-5xl md:text-6xl font-light text-white mb-8 tracking-wide relative z-10">Let&apos;s Connect</h2>
          <p className="text-zinc-400 text-base leading-loose mb-12 max-w-md mx-auto font-light relative z-10">
            Whether it&apos;s a collab, freelance gig, or just saying hi — hit me up, I&apos;ll reply!
          </p>
          <a id="contact-email" href={`mailto:${profile?.email}`} className="btn relative z-10">Say Hello</a>
        </motion.div>
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
