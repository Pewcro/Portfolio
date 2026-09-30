'use client';

import { useEffect, useState } from 'react';
import { supabase } from '@/lib/supabase';

type Tab = 'profile' | 'projects' | 'skills' | 'socials';

export default function AdminDashboard() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<Tab>('profile');

  // Login state
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loginError, setLoginError] = useState('');
  const [loginLoading, setLoginLoading] = useState(false);

  // Profile state
  const [profile, setProfile] = useState<any>({});
  const [profileFile, setProfileFile] = useState<File | null>(null);
  const [profileMsg, setProfileMsg] = useState({ text: '', color: '' });

  // Projects state
  const [projects, setProjects] = useState<any[]>([]);
  const [newProj, setNewProj] = useState({ title: '', desc: '', tags: '', github: '' });
  const [projFile, setProjFile] = useState<File | null>(null);
  const [projMsg, setProjMsg] = useState({ text: '', color: '' });

  // Skills state
  const [skills, setSkills] = useState<any[]>([]);
  const [newSkill, setNewSkill] = useState('');
  const [skillMsg, setSkillMsg] = useState({ text: '', color: '' });

  // Socials state
  const [socials, setSocials] = useState<any[]>([]);
  const [socialMsg, setSocialMsg] = useState({ text: '', color: '' });

  // Check auth on mount
  useEffect(() => {
    supabase.auth.getSession().then(({ data }) => {
      if (data.session) {
        setIsLoggedIn(true);
        loadAllData();
      }
      setLoading(false);
    });
  }, []);

  // Upload helper
  async function uploadFile(file: File): Promise<string> {
    const fileExt = file.name.split('.').pop();
    const fileName = `${Date.now()}_${Math.random().toString(36).substring(7)}.${fileExt}`;
    const { error } = await supabase.storage.from('image').upload(fileName, file);
    if (error) throw error;
    const { data } = supabase.storage.from('image').getPublicUrl(fileName);
    return data.publicUrl;
  }

  // Login
  async function handleLogin() {
    if (!email || !password) { setLoginError('Isi semua field!'); return; }
    setLoginLoading(true);
    setLoginError('');
    const { error } = await supabase.auth.signInWithPassword({ email, password });
    if (error) {
      setLoginError(error.message);
      setLoginLoading(false);
    } else {
      setIsLoggedIn(true);
      loadAllData();
    }
  }

  async function handleLogout() {
    await supabase.auth.signOut();
    setIsLoggedIn(false);
  }

  // Load all data
  function loadAllData() {
    loadProfile();
    loadProjects();
    loadSkills();
    loadSocials();
  }

  // --- PROFILE ---
  async function loadProfile() {
    const { data } = await supabase.from('profile').select('*').single();
    if (data) setProfile(data);
  }

  async function saveProfile() {
    setProfileMsg({ text: 'Menyimpan...', color: 'text-yellow-400' });
    try {
      let imgUrl = profile.profile_image || '';
      if (profileFile) imgUrl = await uploadFile(profileFile);

      const { error } = await supabase.from('profile').update({
        full_name: profile.full_name,
        short_name: profile.short_name,
        roles: profile.roles,
        hero_desc: profile.hero_desc,
        about_text: profile.about_text,
        email: profile.email,
        profile_image: imgUrl,
      }).eq('id', 1);

      if (error) throw error;
      setProfileMsg({ text: 'Berhasil disimpan!', color: 'text-green-400' });
      loadProfile();
    } catch (err: any) {
      setProfileMsg({ text: 'Gagal: ' + err.message, color: 'text-red-400' });
    }
    setTimeout(() => setProfileMsg({ text: '', color: '' }), 3000);
  }

  // --- PROJECTS ---
  async function loadProjects() {
    const { data } = await supabase.from('projects').select('*').order('id', { ascending: true });
    setProjects(data || []);
  }

  async function addProject() {
    if (!newProj.title) { setProjMsg({ text: 'Judul wajib diisi!', color: 'text-red-400' }); return; }
    setProjMsg({ text: 'Mengupload...', color: 'text-yellow-400' });
    try {
      let imgUrl = 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?q=80&w=800&auto=format&fit=crop';
      if (projFile) imgUrl = await uploadFile(projFile);

      const { error } = await supabase.from('projects').insert([{
        title: newProj.title, description: newProj.desc, tags: newProj.tags,
        github_link: newProj.github, image: imgUrl,
      }]);
      if (error) throw error;
      setProjMsg({ text: 'Project ditambahkan!', color: 'text-green-400' });
      setNewProj({ title: '', desc: '', tags: '', github: '' });
      setProjFile(null);
      loadProjects();
    } catch (err: any) {
      setProjMsg({ text: 'Gagal: ' + err.message, color: 'text-red-400' });
    }
    setTimeout(() => setProjMsg({ text: '', color: '' }), 3000);
  }

  async function deleteProject(id: number) {
    if (!confirm('Hapus project ini?')) return;
    await supabase.from('projects').delete().eq('id', id);
    loadProjects();
  }

  // --- SKILLS ---
  async function loadSkills() {
    const { data } = await supabase.from('skills').select('*').order('id');
    setSkills(data || []);
  }

  async function addSkill() {
    if (!newSkill) return;
    const { error } = await supabase.from('skills').insert([{ skill_name: newSkill }]);
    if (error) setSkillMsg({ text: error.message, color: 'text-red-400' });
    else {
      setNewSkill('');
      setSkillMsg({ text: 'Skill ditambahkan!', color: 'text-green-400' });
      loadSkills();
    }
    setTimeout(() => setSkillMsg({ text: '', color: '' }), 3000);
  }

  async function deleteSkill(id: number) {
    await supabase.from('skills').delete().eq('id', id);
    loadSkills();
  }

  // --- SOCIALS ---
  async function loadSocials() {
    const { data } = await supabase.from('social_links').select('*').order('id');
    setSocials(data || []);
  }

  async function saveSocials() {
    setSocialMsg({ text: 'Menyimpan...', color: 'text-yellow-400' });
    try {
      for (const s of socials) {
        await supabase.from('social_links').update({ url: s.url }).eq('id', s.id);
      }
      setSocialMsg({ text: 'Social Links disimpan!', color: 'text-green-400' });
    } catch (err: any) {
      setSocialMsg({ text: 'Gagal: ' + err.message, color: 'text-red-400' });
    }
    setTimeout(() => setSocialMsg({ text: '', color: '' }), 3000);
  }

  function updateSocialUrl(id: number, url: string) {
    setSocials(prev => prev.map(s => s.id === id ? { ...s, url } : s));
  }

  // Show loading
  if (loading) return <div className="min-h-screen flex items-center justify-center text-zinc-500 relative z-10">Loading...</div>;

  // --- LOGIN SCREEN ---
  if (!isLoggedIn) {
    return (
      <div className="min-h-screen flex items-center justify-center relative z-10 px-4">
        <div className="glass p-10 rounded-3xl w-full max-w-md relative overflow-hidden">
          <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-sky-500"></div>
          <h2 className="text-3xl font-light mb-2 text-white">Welcome Back</h2>
          <p className="text-zinc-400 mb-8 text-sm">Sign in to manage your portfolio</p>

          <label>Email</label>
          <input type="email" placeholder="Email address" value={email}
            onChange={e => setEmail(e.target.value)} className="mb-4" />
          <label>Password</label>
          <input type="password" placeholder="Password" value={password}
            onChange={e => setPassword(e.target.value)} className="mb-8"
            onKeyDown={e => e.key === 'Enter' && handleLogin()} />

          <button onClick={handleLogin} disabled={loginLoading}
            className="w-full bg-white text-black py-3 rounded-xl font-bold uppercase tracking-widest hover:bg-gray-200 cursor-pointer transition-colors disabled:opacity-50">
            {loginLoading ? 'Loading...' : 'Login to Dashboard'}
          </button>
          {loginError && <p className="text-red-400 text-sm mt-4 text-center">{loginError}</p>}
        </div>
      </div>
    );
  }

  // --- DASHBOARD ---
  const tabs: { key: Tab; label: string }[] = [
    { key: 'profile', label: 'Profil' },
    { key: 'projects', label: 'Projects' },
    { key: 'skills', label: 'Skills' },
    { key: 'socials', label: 'Social Links' },
  ];

  return (
    <div className="max-w-5xl mx-auto py-12 px-6 relative z-10">
      {/* Header */}
      <header className="glass rounded-2xl p-6 flex flex-col md:flex-row justify-between items-center mb-8">
        <h2 className="text-2xl font-light text-white tracking-widest uppercase">
          Admin <span className="font-bold grad-text">Panel</span>
        </h2>
        <div className="flex gap-4 mt-4 md:mt-0">
          <a href="/" className="btn-ghost text-xs">Lihat Website</a>
          <button onClick={handleLogout}
            className="btn text-xs !bg-red-500/10 !text-red-400 !border-red-500/30 hover:!bg-red-500 hover:!text-white">
            Logout
          </button>
        </div>
      </header>

      {/* Tabs */}
      <div className="flex overflow-x-auto gap-2 p-2 glass rounded-xl mb-8 w-max max-w-full">
        {tabs.map(t => (
          <button key={t.key} onClick={() => setActiveTab(t.key)}
            className={`tab-btn ${activeTab === t.key ? 'active' : ''}`}>
            {t.label}
          </button>
        ))}
      </div>

      {/* TAB: PROFIL */}
      {activeTab === 'profile' && (
        <div className="glass p-8 rounded-3xl animate-fadeIn">
          <h3 className="text-xl font-light mb-6 text-white">Edit Profil Utama</h3>
          <div className="grid md:grid-cols-2 gap-6">
            <div><label>Nama Lengkap</label><input value={profile.full_name || ''} onChange={e => setProfile({ ...profile, full_name: e.target.value })} /></div>
            <div><label>Nama Singkat (Logo)</label><input value={profile.short_name || ''} onChange={e => setProfile({ ...profile, short_name: e.target.value })} /></div>
            <div className="md:col-span-2"><label>Roles (Pisahkan koma)</label><input value={profile.roles || ''} onChange={e => setProfile({ ...profile, roles: e.target.value })} /></div>
            <div className="md:col-span-2"><label>Hero Description</label><textarea rows={2} value={profile.hero_desc || ''} onChange={e => setProfile({ ...profile, hero_desc: e.target.value })} /></div>
            <div className="md:col-span-2"><label>About Text (| = paragraf baru)</label><textarea rows={4} value={profile.about_text || ''} onChange={e => setProfile({ ...profile, about_text: e.target.value })} /></div>
            <div><label>Email Kontak</label><input type="email" value={profile.email || ''} onChange={e => setProfile({ ...profile, email: e.target.value })} /></div>
            <div>
              <label>Foto Profil</label>
              <input type="file" accept="image/*" onChange={e => setProfileFile(e.target.files?.[0] || null)} className="text-sm" />
              {profile.profile_image && <img src={profile.profile_image} className="mt-4 h-24 w-24 object-cover rounded-xl border border-white/10" />}
            </div>
            <div className="md:col-span-2 mt-4">
              <button onClick={saveProfile} className="btn w-full !bg-indigo-600 !border-indigo-500 hover:!bg-indigo-500">Simpan Profil</button>
              {profileMsg.text && <p className={`${profileMsg.color} text-sm text-center mt-3`}>{profileMsg.text}</p>}
            </div>
          </div>
        </div>
      )}

      {/* TAB: PROJECTS */}
      {activeTab === 'projects' && (
        <div className="glass p-8 rounded-3xl animate-fadeIn">
          <h3 className="text-xl font-light mb-6 text-white">Manajemen Projects</h3>

          <div className="mb-10 p-6 rounded-2xl bg-black/20 border border-white/5">
            <h4 className="text-sm font-bold text-indigo-400 uppercase tracking-widest mb-4">Tambah Project Baru</h4>
            <div className="grid md:grid-cols-2 gap-4">
              <div><label>Judul</label><input value={newProj.title} onChange={e => setNewProj({ ...newProj, title: e.target.value })} /></div>
              <div><label>Link GitHub</label><input value={newProj.github} onChange={e => setNewProj({ ...newProj, github: e.target.value })} /></div>
              <div className="md:col-span-2"><label>Tags (koma)</label><input value={newProj.tags} onChange={e => setNewProj({ ...newProj, tags: e.target.value })} /></div>
              <div className="md:col-span-2"><label>Deskripsi</label><textarea rows={2} value={newProj.desc} onChange={e => setNewProj({ ...newProj, desc: e.target.value })} /></div>
              <div className="md:col-span-2"><label>Gambar</label><input type="file" accept="image/*" onChange={e => setProjFile(e.target.files?.[0] || null)} className="text-sm" /></div>
              <div className="md:col-span-2 mt-4">
                <button onClick={addProject} className="btn w-full !bg-green-600/20 !text-green-400 !border-green-500/30 hover:!bg-green-600 hover:!text-white">Tambah Project</button>
                {projMsg.text && <p className={`${projMsg.color} text-sm mt-3 text-center`}>{projMsg.text}</p>}
              </div>
            </div>
          </div>

          <h4 className="text-sm font-bold text-zinc-400 uppercase tracking-widest mb-4">Daftar Project</h4>
          <div className="grid gap-4">
            {projects.length === 0 && <div className="text-zinc-500 text-sm">Belum ada project.</div>}
            {projects.map(p => (
              <div key={p.id} className="flex flex-col sm:flex-row justify-between sm:items-center bg-white/5 border border-white/10 p-5 rounded-2xl gap-4">
                <div className="flex gap-5 items-center">
                  <img src={p.image} className="w-20 h-20 object-cover rounded-xl shadow-lg border border-white/10" />
                  <div>
                    <h5 className="font-bold text-white text-lg">{p.title}</h5>
                    <p className="text-xs text-indigo-300 uppercase tracking-widest mt-1">{p.tags}</p>
                    <a href={p.github_link} target="_blank" className="text-xs text-zinc-400 hover:text-white mt-2 inline-block">🔗 GitHub</a>
                  </div>
                </div>
                <button onClick={() => deleteProject(p.id)} className="btn !bg-red-500/10 !text-red-400 !border-red-500/30 hover:!bg-red-500 hover:!text-white whitespace-nowrap">Hapus</button>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* TAB: SKILLS */}
      {activeTab === 'skills' && (
        <div className="glass p-8 rounded-3xl animate-fadeIn">
          <h3 className="text-xl font-light mb-6 text-white">Manajemen Skills</h3>
          <div className="flex flex-col md:flex-row gap-4 mb-8 items-end">
            <div className="flex-grow w-full"><label>Nama Skill</label><input value={newSkill} onChange={e => setNewSkill(e.target.value)} placeholder="misal: React JS" /></div>
            <button onClick={addSkill} className="btn h-fit !py-3 whitespace-nowrap !bg-green-600/20 !text-green-400 !border-green-500/30 hover:!bg-green-600 hover:!text-white">Tambah Skill</button>
          </div>
          {skillMsg.text && <p className={`${skillMsg.color} text-sm mb-6 text-center`}>{skillMsg.text}</p>}
          <ul className="grid md:grid-cols-2 gap-3">
            {skills.map(s => (
              <li key={s.id} className="flex justify-between items-center bg-white/5 border border-white/10 px-5 py-3 rounded-xl">
                <span className="text-zinc-200 font-medium">{s.skill_name}</span>
                <button onClick={() => deleteSkill(s.id)} className="text-xs text-red-400 hover:text-red-300 uppercase tracking-widest font-bold">Hapus</button>
              </li>
            ))}
          </ul>
        </div>
      )}

      {/* TAB: SOCIALS */}
      {activeTab === 'socials' && (
        <div className="glass p-8 rounded-3xl animate-fadeIn">
          <h3 className="text-xl font-light mb-6 text-white">Manajemen Social Links</h3>
          <div className="space-y-4 mb-8">
            {socials.map(s => (
              <div key={s.id}>
                <label>{s.platform}</label>
                <input value={s.url} onChange={e => updateSocialUrl(s.id, e.target.value)} />
              </div>
            ))}
          </div>
          <button onClick={saveSocials} className="btn w-full !bg-indigo-600 !border-indigo-500 hover:!bg-indigo-500">Simpan Social Links</button>
          {socialMsg.text && <p className={`${socialMsg.color} text-sm text-center mt-4`}>{socialMsg.text}</p>}
        </div>
      )}
    </div>
  );
}
