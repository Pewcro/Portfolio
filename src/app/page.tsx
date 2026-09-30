import { supabase } from '@/lib/supabase';
import ClientHome from './ClientHome';

export const revalidate = 60; // revalidate every 60 seconds

export default async function Home() {
  const [{ data: profile }, { data: projects }, { data: skills }, { data: socials }] = await Promise.all([
    supabase.from('profile').select('*').single(),
    supabase.from('projects').select('*').order('id', { ascending: true }),
    supabase.from('skills').select('*').order('id', { ascending: true }),
    supabase.from('social_links').select('*').order('id', { ascending: true }),
  ]);

  return (
    <ClientHome 
      profile={profile} 
      projects={projects || []} 
      skills={skills || []} 
      socials={socials || []} 
    />
  );
}
