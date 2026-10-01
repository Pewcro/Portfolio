'use client';

import { useEffect, useState } from 'react';
import { motion } from 'framer-motion';

export default function Background() {
  const [mousePosition, setMousePosition] = useState({ x: 0, y: 0 });

  useEffect(() => {
    const handleMouseMove = (e: MouseEvent) => {
      setMousePosition({
        x: e.clientX,
        y: e.clientY,
      });
    };
    window.addEventListener('mousemove', handleMouseMove);
    return () => window.removeEventListener('mousemove', handleMouseMove);
  }, []);

  return (
    <div className="fixed inset-0 z-[-1] overflow-hidden bg-[#030303] pointer-events-none">
      {/* Animated Gradients (Aurora Effect) */}
      <motion.div
        animate={{
          x: mousePosition.x * -0.02,
          y: mousePosition.y * -0.02,
        }}
        transition={{ type: 'tween', ease: 'easeOut', duration: 1 }}
        className="absolute inset-0"
      >
        {/* Deep Purple Orb */}
        <div className="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-purple-700/30 blur-[120px] rounded-full mix-blend-screen animate-blob" />
        {/* Bright Cyan Orb */}
        <div className="absolute top-[20%] right-[-10%] w-[45%] h-[45%] bg-cyan-600/20 blur-[120px] rounded-full mix-blend-screen animate-blob animation-delay-2000" />
        {/* Indigo Orb */}
        <div className="absolute bottom-[-20%] left-[20%] w-[60%] h-[60%] bg-indigo-600/20 blur-[120px] rounded-full mix-blend-screen animate-blob animation-delay-4000" />
      </motion.div>

      {/* Grid Overlay - Modern subtle dot/grid pattern */}
      <div className="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:32px_32px] [mask-image:radial-gradient(ellipse_70%_50%_at_50%_0%,#000_70%,transparent_100%)]" />

      {/* Interactive Mouse Glow / Spotlight */}
      <motion.div
        className="absolute rounded-full pointer-events-none blur-[100px]"
        style={{
          width: '500px',
          height: '500px',
          background: 'radial-gradient(circle, rgba(99,102,241,0.12) 0%, rgba(0,0,0,0) 70%)',
          left: 0,
          top: 0,
        }}
        animate={{
          x: mousePosition.x - 250,
          y: mousePosition.y - 250,
        }}
        transition={{ type: 'spring', damping: 40, stiffness: 200, mass: 0.5 }}
      />

      {/* Premium Noise Texture */}
      <div 
        className="absolute inset-0 opacity-[0.035] mix-blend-overlay" 
        style={{ 
          backgroundImage: 'url("data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E")' 
        }}
      />
    </div>
  );
}
