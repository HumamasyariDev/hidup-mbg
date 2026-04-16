import { useAuth } from '../../hooks/useAuth';

export default function DashboardPage() {
  const { admin, logout } = useAuth();

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
          <h1 className="text-xl font-bold text-gray-800">MBG Dashboard</h1>
          <div className="flex items-center gap-4">
            <span className="text-sm text-gray-600">
              {admin?.name} ({admin?.role.replace('_', ' ')})
            </span>
            <button
              onClick={logout}
              className="text-sm text-red-600 hover:text-red-800"
            >
              Logout
            </button>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 py-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <DashCard title="SPPG Providers" href="/sppg-providers" icon="🏭" />
          <DashCard title="Sekolah" href="/schools" icon="🏫" />
          <DashCard title="Menu MBG" href="/menus" icon="🍽️" />
          <DashCard title="Laporan" href="/reports" icon="📊" />
        </div>
      </main>
    </div>
  );
}

function DashCard({ title, href, icon }: { title: string; href: string; icon: string }) {
  return (
    <a
      href={href}
      className="bg-white rounded-lg shadow-sm border p-6 hover:shadow-md transition-shadow"
    >
      <div className="text-3xl mb-2">{icon}</div>
      <h2 className="text-lg font-semibold text-gray-800">{title}</h2>
    </a>
  );
}
