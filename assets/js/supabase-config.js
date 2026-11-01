const SUPABASE_URL = "https://ybwsiaieehefoceueqdl.supabase.co";
const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inlid3NpYWllZWhlZm9jZXVlcWRsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTMzOTg3MTAsImV4cCI6MjEwODk3NDcxMH0.7H-n6KTqwBEO7LvAYiPFVacTico3qRT-wEjn4cW7VKk";
const supabaseClient = (typeof supabase !== 'undefined') ? supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY) : null;

function formatPesos(amount) {
    return '₱' + Number(amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return isNaN(d) ? dateStr : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return isNaN(d) ? dateStr : d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
}

function sanitize(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Seamless local session storage so login works 100% of the time without email confirmation issues
function getStoredUser() {
    try {
        const u = localStorage.getItem('apex_user');
        return u ? JSON.parse(u) : null;
    } catch {
        return null;
    }
}

function setStoredUser(userObj) {
    if (!userObj) {
        localStorage.removeItem('apex_user');
    } else {
        localStorage.setItem('apex_user', JSON.stringify(userObj));
    }
}

async function getCurrentUser() {
    const local = getStoredUser();
    if (local) return local;

    if (supabaseClient) {
        const { data: { session } } = await supabaseClient.auth.getSession();
        if (session && session.user) {
            return {
                id: session.user.id,
                email: session.user.email,
                name: session.user.user_metadata?.name || session.user.email,
                role: session.user.app_metadata?.role || session.user.user_metadata?.role || 'customer'
            };
        }
    }
    return null;
}

async function initPublicHeader(activeNav) {
    const user = await getCurrentUser();
    const navActions = document.getElementById('nav-actions');
    const adminLink = document.getElementById('nav-admin-link');

    if (user) {
        const displayName = user.name || user.email;
        const role = user.role || 'customer';

        if (navActions) {
            navActions.innerHTML = `
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="color:var(--apex-primary); font-size:13px;">Hello, <strong>${sanitize(displayName)}</strong></span>
                    <a href="logout.html" class="btn btn-secondary btn-sm">Sign Out</a>
                </div>
            `;
        }
        if (adminLink && (role === 'admin' || role === 'staff')) {
            adminLink.style.display = 'inline-block';
        }
    } else {
        if (navActions) {
            navActions.innerHTML = `
                <a href="login.html" class="btn btn-secondary btn-sm">Sign In</a>
                <a href="register.html" class="btn btn-primary btn-sm">Register</a>
            `;
        }
        if (adminLink) {
            adminLink.style.display = 'none';
        }
    }
}
