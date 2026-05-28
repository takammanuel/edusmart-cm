import React, { useState } from 'react';
import { Link, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { LayoutDashboard, Users, GraduationCap, School, LogOut, Menu, X, Bell, User as UserIcon, ChevronRight } from 'lucide-react';

export default function AdminLayout() {
  const { user, logout } = useAuth();
  const location = useLocation();
  const [isMobileSidebarOpen, setIsMobileSidebarOpen] = useState(false);

  const menuItems = [
    { path: '/admin/dashboard', name: 'Tableau de bord', icon: LayoutDashboard },
    { path: '/admin/students', name: 'Gestion des Élèves', icon: GraduationCap },
    { path: '/admin/teachers', name: 'Gestion des Enseignants', icon: Users },
    { path: '/admin/classrooms', name: 'Gestion des Classes', icon: School }
  ];

  return (
    <div className="min-h-screen bg-slate-50 flex">
      
      {/* DESKTOP SIDEBAR */}
      <aside className="hidden lg:flex flex-col w-72 bg-slate-950 text-slate-400 border-r border-slate-800 fixed h-full z-30">
        <div className="p-6 border-b border-slate-800 flex items-center gap-3">
          <div className="bg-brand text-white p-2 rounded-xl">
            <School className="h-5 w-5" />
          </div>
          <div>
            <h1 className="text-white font-bold text-lg tracking-tight">EduSmart CM</h1>
            <span className="text-xs text-brand font-medium tracking-wider uppercase">Super Admin</span>
          </div>
        </div>

        <nav className="flex-1 p-4 space-y-1.5 pt-6">
          {menuItems.map((item) => {
            const Icon = item.icon;
            const isActive = location.pathname === item.path;
            return (
              <Link
                key={item.path}
                to={item.path}
                className={`flex items-center justify-between px-4 py-3.5 rounded-xl font-medium transition-all group ${
                  isActive ? 'bg-brand text-white shadow-lg shadow-brand/15' : 'hover:bg-slate-900 hover:text-slate-200'
                }`}
              >
                <div className="flex items-center gap-3.5">
                  <Icon className={`h-5 w-5 shrink-0 ${isActive ? 'text-white' : 'text-slate-400 group-hover:text-slate-200'}`} />
                  <span className="text-sm">{item.name}</span>
                </div>
                <ChevronRight className={`h-4 w-4 opacity-0 transition-all ${isActive ? 'opacity-100' : 'group-hover:opacity-40 group-hover:translate-x-0.5'}`} />
              </Link>
            );
          })}
        </nav>

        <div className="p-4 border-t border-slate-800 bg-slate-950/50">
          <button onClick={logout} className="w-full flex items-center gap-3 px-4 py-3.5 rounded-xl font-medium text-sm text-rose-400 hover:bg-rose-500/10 transition-colors">
            <LogOut className="h-5 w-5 shrink-0" />
            Déconnexion
          </button>
        </div>
      </aside>

      {/* MOBILE SIDEBAR */}
      <div className={`fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity lg:hidden ${isMobileSidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'}`} onClick={() => setIsMobileSidebarOpen(false)} />
      <aside className={`fixed top-0 bottom-0 left-0 w-72 bg-slate-950 z-50 flex flex-col transition-transform duration-300 transform lg:hidden ${isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        <div className="p-6 border-b border-slate-800 flex justify-between items-center">
          <div className="flex items-center gap-3">
            <div className="bg-brand text-white p-2 rounded-xl"><School className="h-5 w-5" /></div>
            <span className="text-white font-bold text-base">EduSmart</span>
          </div>
          <button onClick={() => setIsMobileSidebarOpen(false)} className="text-slate-400 hover:text-white"><X className="h-5 w-5" /></button>
        </div>
        <nav className="flex-1 p-4 space-y-1 pt-6">
          {menuItems.map((item) => {
            const Icon = item.icon;
            const isActive = location.pathname === item.path;
            return (
              <Link key={item.path} to={item.path} onClick={() => setIsMobileSidebarOpen(false)} className={`flex items-center gap-3.5 px-4 py-3.5 rounded-xl text-sm font-medium transition-all ${isActive ? 'bg-brand text-white' : 'text-slate-400 hover:bg-slate-900'}`}>
                <Icon className="h-5 w-5" />{item.name}
              </Link>
            );
          })}
        </nav>
        <div className="p-4 border-t border-slate-800">
          <button onClick={logout} className="w-full flex items-center gap-3 px-4 py-3.5 rounded-xl text-sm font-medium text-rose-400 hover:bg-rose-500/10"><LogOut className="h-5 w-5" /> Déconnexion</button>
        </div>
      </aside>

      {/* MAIN CONTAINER */}
      <div className="flex-1 lg:pl-72 flex flex-col min-w-0">
        <header className="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-20 shadow-sm">
          <div className="flex items-center gap-4">
            <button onClick={() => setIsMobileSidebarOpen(true)} className="lg:hidden text-slate-600 p-2 hover:bg-slate-100 rounded-lg">
              <Menu className="h-6 w-6" />
            </button>
            <div>
              <span className="text-xs font-semibold tracking-wider uppercase text-slate-400 block">Espace de Pilotage</span>
              <h2 className="text-sm font-bold text-slate-800">Console Centrale Nationale</h2>
            </div>
          </div>

          <div className="flex items-center gap-4">
            <button className="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-xl relative transition-colors">
              <Bell className="h-5 w-5" />
              <span className="absolute top-2 right-2 w-2 h-2 bg-brand rounded-full" />
            </button>
            <div className="h-8 w-px bg-slate-200" />
            <div className="flex items-center gap-3">
              <div className="hidden md:block text-right">
                <span className="text-sm font-semibold text-slate-800 block leading-tight">{user?.name}</span>
                <span className="text-xs text-brand font-medium capitalize">{user?.role}</span>
              </div>
              <div className="h-10 w-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600">
                <UserIcon className="h-5 w-5" />
              </div>
            </div>
          </div>
        </header>

        <main className="flex-1 p-6 md:p-8 overflow-y-auto">
          <Outlet />
        </main>
      </div>

    </div>
  );
}