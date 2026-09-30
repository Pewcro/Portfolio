import { createClient } from '@supabase/supabase-js';

const supabaseUrl = 'https://pohngtzslyytrimksoof.supabase.co';
const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBvaG5ndHpzbHl5dHJpbWtzb29mIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTA3NDQ4MDEsImV4cCI6MjEwNjMyMDgwMX0.BHp-wF4qsj0qBXIQ2VwpEKK4XwJzbT4_LUKZTAgzUGo';

export const supabase = createClient(supabaseUrl, supabaseKey);
